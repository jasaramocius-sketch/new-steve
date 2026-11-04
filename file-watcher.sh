#!/bin/bash
DIR="$(cd "$(dirname "$0")" && pwd)"
PIDFILE="$DIR/storage/file-backups/.watcher.pid"
LOGFILE="$DIR/storage/logs/file-watcher.log"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"

case "${1:-start}" in
  start)
    if [ -f "$PIDFILE" ] && kill -0 $(cat "$PIDFILE") 2>/dev/null; then
      echo "Watcher already running (PID $(cat "$PIDFILE"))"
      exit 1
    fi
    if [ ! -f "$DIR/storage/file-backups/.state.json" ]; then
      "$PHP_BIN" "$DIR/artisan" file:audit --init || exit 1
    fi
    nohup "$PHP_BIN" "$DIR/artisan" file:audit --watch --interval=10 --max-archive-days=0 --per-file-limit=0 >> "$LOGFILE" 2>&1 &
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
    "$0" stop; "$0" start
    ;;
  *)
    echo "Usage: $0 {start|stop|status|restart}"
    ;;
esac
