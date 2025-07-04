#!/usr/bin/env bash
set -e

# Start Supervisor (background)
echo "→ Starting Supervisor..."
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

# Start Apache in foreground
echo "→ Starting Apache..."
exec apache2-foreground
