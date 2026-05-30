#!/bin/sh
set -e

# Wait for MySQL
echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    sleep 2
done
echo "MySQL ready."

# Run migrations
php artisan migrate --force

# Start supervisor (nginx + php-fpm)
exec supervisord -c /etc/supervisord.conf -n
