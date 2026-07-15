#!/usr/bin/env sh

set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if ! grep -q '^APP_KEY=[^[:space:]]' .env; then
    php artisan key:generate --force --no-interaction >/dev/null
fi

echo "Waiting for database connection..."
for i in $(seq 1 30); do
    php artisan migrate --force --no-interaction 2>/dev/null && break
    echo "Attempt $i failed. Retrying in 2 seconds..."
    sleep 2
done

exec php artisan serve --host=0.0.0.0 --port=8000