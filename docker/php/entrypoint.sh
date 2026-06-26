#!/bin/sh
set -e

# /var/www/html/public is a named volume shared with nginx (so it can serve
# static files / act as the fastcgi document root). Named volumes only
# auto-populate from the image on first creation, so resync from the
# pristine image copy on every boot to pick up new asset builds.
rm -rf /var/www/html/public/*
cp -a /var/www/html-public-src/. /var/www/html/public/

php artisan migrate --force

exec "$@"
