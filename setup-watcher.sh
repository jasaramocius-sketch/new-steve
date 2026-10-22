#!/bin/bash
DIR="$(cd "$(dirname "$0")" && pwd)"
SERVICE_FILE="$DIR/file-watcher.service"
TARGET="/etc/systemd/system/file-watcher.service"

if [ "$(id -u)" -ne 0 ]; then
  echo "Run with sudo: sudo ./setup-watcher.sh"
  exit 1
fi

# Stop manual watcher if running
"$DIR/file-watcher.sh" stop 2>/dev/null

# Copy service file
cp "$SERVICE_FILE" "$TARGET"

# Reload, enable, start
systemctl daemon-reload
systemctl enable file-watcher
systemctl start file-watcher

echo "Done. Status:"
systemctl status file-watcher --no-pager
