#!/usr/bin/env bash
set -e

echo "→ Starting Supervisor…"
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf &

echo "→ Starting Apache…"
exec apache2-foreground
