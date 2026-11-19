#!/bin/sh
set -e

# Przy starcie serwera instalujemy brakujące zależności (dev z podmontowanym
# kodem) i dociągamy schemat bazy do najnowszej migracji.
if [ "$1" = 'frankenphp' ]; then
    mkdir -p var/data
    if [ ! -f vendor/autoload.php ]; then
        composer install --prefer-dist --no-progress --no-interaction
    fi
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
fi

exec "$@"
