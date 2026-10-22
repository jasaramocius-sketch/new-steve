import type { Plugin } from "@opencode-ai/plugin"
import { join } from "node:path"
import { writeFileSync, unlinkSync } from "node:fs"

export const ChatSaverPlugin: Plugin = async ({ client, directory }) => {
  const script = join(directory, "chat-saver.php")
  const logDir = join(directory, "chat-logs")
  const tmpFile = join(directory, ".chat-saver-tmp.json")

  let lastSessionId = ""
  let lastStamp = ""

  return {
    event: async ({ event }) => {
      // Save the full chat automatically after every completed exchange.
      if (event.type !== "session.idle") return

      const sessionID = (event.properties as { sessionID?: string })?.sessionID
      if (!sessionID || sessionID === lastSessionId) return

      try {
        const messages = await client.session.messages({ path: { id: sessionID } })

        const chat = messages
          .filter(({ info }) => info.role !== "system")
          .map(({ info, parts }) => {
            let content = ""
            for (const part of parts) {
              if (part.type === "text" && "text" in part) {
                content += (content ? "\n" : "") + (part as { text: string }).text
              } else if (part.type === "tool") {
                const t = part as {
                  tool?: string
                  state?: string
                  input?: unknown
                  output?: unknown
                }
                if (t.state === "completed" || t.tool) {
                  const out = typeof t.output === "string" ? t.output : JSON.stringify(t.output ?? "")
                  content += (content ? "\n" : "") + `[tool:${t.tool ?? "unknown"}]\n${out}`
                }
              }
            }
            return { role: info.role, content }
          })

        if (chat.length === 0) return

        writeFileSync(tmpFile, JSON.stringify(chat))
        const metaFile = join(directory, ".chat-saver-meta.json")
        writeFileSync(metaFile, JSON.stringify({ tool: "opencode", session: sessionID }))

        const { stdout } = await Bun
          .$`php ${script} ${tmpFile} ${logDir} chat ${metaFile}`
          .quiet()
          .nothrow()

        const result = JSON.parse(stdout.toString() || "{}")
        if (result.stamp && result.stamp !== lastStamp) {
          lastStamp = result.stamp
          lastSessionId = sessionID
          await client.app.log({
            body: {
              service: "chat-saver",
              level: "info",
              message: "Chat auto-saved",
              extra: { session: sessionID, files: result },
            },
          })
        }

        unlinkSync(tmpFile)
        unlinkSync(metaFile)
      } catch (error) {
        await client.app.log({
          body: {
            service: "chat-saver",
            level: "error",
            message: "Failed to auto-save chat",
            extra: { session: sessionID, error: String(error) },
          },
        })
      }
    },
  }
}
