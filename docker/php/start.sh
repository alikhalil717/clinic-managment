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

php artisan migrate --force --no-interaction

exec php artisan serve --host=0.0.0.0 --port=8000