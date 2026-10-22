import { pathToFileURL } from "node:url";

const root = process.env.PWD || process.cwd();
await import(pathToFileURL(`${root}/public/assets/front/js/marked.min.js`).href);

const marked = globalThis.marked;
marked.setOptions({ breaks: true, gfm: true });

let input = "";
process.stdin.setEncoding("utf8");
process.stdin.on("data", (c) => (input += c));
process.stdin.on("end", () => {
  let items;
  try {
    items = JSON.parse(input);
  } catch (e) {
    process.stderr.write("Invalid JSON: " + e.message);
    process.exit(1);
  }
  if (!Array.isArray(items)) {
    process.stderr.write("Expected an array of {id, text}");
    process.exit(1);
  }
  const out = items.map((it) => {
    let raw = String(it.text || "");
    raw = raw.replace(/^[ \t]*\u2022[ \t]+/gm, "- ");
    return { id: it.id, html: marked.parse(raw) };
  });
  process.stdout.write(JSON.stringify(out));
});