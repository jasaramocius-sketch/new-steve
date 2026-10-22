#!/bin/bash
DIR="$(cd "$(dirname "$0")" && pwd)"
PIDFILE="$DIR/storage/file-backups/.watcher.pid"

case "${1:-start}" in
  start)
    if [ -f "$PIDFILE" ] && kill -0 $(cat "$PIDFILE") 2>/dev/null; then
      echo "Watcher already running (PID $(cat "$PIDFILE"))"
      exit 1
    fi
    nohup node "$DIR/file-watcher.mjs" >> "$DIR/storage/logs/file-watcher.log" 2>&1 &
    echo $! > "$PIDFILE"
    echo "Watcher started (PID $!)"
    ;;
  stop)
    if [ ! -f "$PIDFILE" ]; then
      echo "Watcher not running"
      exit 1
    fi
    PID=$(cat "$PIDFILE")
    kill "$PID" 2>/dev/null && echo "Watcher stopped (PID $PID)" || echo "Failed to stop"
    rm -f "$PIDFILE"
    ;;
  status)
    if [ -f "$PIDFILE" ] && kill -0 $(cat "$PIDFILE") 2>/dev/null; then
      echo "Watcher running (PID $(cat "$PIDFILE"))"
    else
      echo "Watcher not running"
    fi
    ;;
  restart)
    "$0" stop; sleep 1; "$0" start
    ;;
  *)
    echo "Usage: $0 {start|stop|status|restart}"
    ;;
esac
