#!/bin/bash
# sync-skills.sh — Detects new TASKS.md entries and suggests skill updates
# Run: bash .opencode/skills/sync-skills.sh

TASKS="TASKS.md"
SKILLS_DIR=".opencode/skills"
LAST_SYNC_FILE=".opencode/skills/.last_sync"

# Get last synced line number
if [ -f "$LAST_SYNC_FILE" ]; then
    LAST_LINE=$(cat "$LAST_SYNC_FILE")
else
    LAST_LINE=0
fi

# Get current total lines
CURRENT_LINES=$(wc -l < "$TASKS")

if [ "$CURRENT_LINES" -le "$LAST_LINE" ]; then
    echo "No new tasks since last sync."
    exit 0
fi

echo "=== New tasks since last sync (lines $((LAST_LINE+1)) to $CURRENT_LINES) ==="
echo ""

# Extract new task titles
NEW_TASKS=$(sed -n "$((LAST_LINE+1)),${CURRENT_LINES}p" "$TASKS" | grep "^## [0-9]")

if [ -z "$NEW_TASKS" ]; then
    echo "No new task headers found."
else
    echo "$NEW_TASKS"
    echo ""
    echo "---"
    echo "Update relevant skills in $SKILLS_DIR/"
    echo "Then run: echo $CURRENT_LINES > $LAST_SYNC_FILE"
fi
