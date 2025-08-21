#!/usr/bin/env bash
set -e

echo "→ Running database migrations…"
if ! /var/www/html/vendor/bin/phinx migrate; then
  echo "Database migration failed. Exiting."
fi

echo "→ Starting Supervisor…"
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf &

echo "→ Starting Apache…"
exec apache2-foreground
