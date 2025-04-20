#!/bin/bash
dockerize -wait tcp://db:3306 -timeout 20s

git config --global --add safe.directory /var/www/html

composer install

php artisan migrate

php artisan serve --host=0.0.0.0 --port=8080
