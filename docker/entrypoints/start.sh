#!/usr/bin/env sh
set -e

# Ensure storage link exists (best-effort)
if [ -f /var/www/html/artisan ]; then
  php /var/www/html/artisan storage:link || true
fi

# Ensure permissions for storage and cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Start supervisord to run web, worker and other processes as configured
exec supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
