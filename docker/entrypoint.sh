#!/bin/sh
set -e

cd /var/www/html

export PORT="${PORT:-8080}"

echo "Configuring Nginx on port ${PORT}..."
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

php artisan package:discover --ansi

echo "Waiting for database and running migrations..."
attempt=0
max_attempts=30
until php artisan migrate --force --no-interaction; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge "$max_attempts" ]; then
        echo "Database migration failed after ${max_attempts} attempts."
        exit 1
    fi
    echo "Retrying migrate in 2s (${attempt}/${max_attempts})..."
    sleep 2
done

if [ "${RUN_SEED}" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force --no-interaction
fi

echo "Caching Laravel configuration..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting PHP-FPM and Nginx..."
php-fpm -D
exec nginx -g 'daemon off;'
