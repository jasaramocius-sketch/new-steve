# Operations & Maintenance

This section covers routine operational tasks: backups, watchers, and file uploads.

File uploads

- Uploaded images are stored under `public/assets/images/` (see HOME_PAGE_ADMIN_GUIDE.md for the `home/` subfolder).
- Ensure correct file permissions for uploads (www-data or web user). Typical permissions: 755 for directories and 644 for files.

Backups

- The repository contains a `backup/` directory and `copy_backup.py` utility. Review and adapt to your environment.
- Recommended backup routine:
  - Daily DB dump
  - Daily snapshot of `storage/` and `public/assets`
  - Rotate backups (keep last 30 days)

File watcher

- There are scripts and a systemd service to support a file-watcher (file-watcher.sh, file-watcher.service, file-watcher.mjs).
- Use with caution; review scripts before enabling.

Monitoring

- Monitor logs under storage/logs/ for errors
- Monitor queue worker health (Supervisor or systemd)
- Add uptime/health checks that hit a lightweight health endpoint or the home page

Maintenance mode

- Use Laravel maintenance mode during upgrades:

  php artisan down
  php artisan up

- For scheduled tasks, use Laravel scheduler via cron (see docs) or systemd timers.

Security

- Keep dependencies up to date (composer update, npm update) and review changelogs.
- Never commit .env with secrets.
- Use HTTPS in production and secure cookies.

Useful utilities in repo

- file-watcher.* — utilities for watching files (read before use)
- copy_backup.py — simple backup helper
- mylaravel.conf — example webserver configuration
