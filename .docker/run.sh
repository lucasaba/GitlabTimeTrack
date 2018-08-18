#!/bin/sh

while ! nc -z $DB_HOST $DB_PORT </dev/null; do
    echo "Attempting database connection..."
    sleep 1;
done
echo "Connected."

echo "Running Composer Scripts..."
composer run-script symfony-scripts-docker

echo "Updating database schema..."
php bin/console doctrine:schema:update --no-interaction --force

echo "Reconfiguring file permissions for $APP_USER..."
chown -R $APP_USER:$APP_USER_GROUP $ROOT_DIR

echo "Starting apache2 server..."
exec apache2-foreground
