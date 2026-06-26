FROM node:20-alpine AS assets
WORKDIR /app
RUN corepack enable && corepack prepare pnpm@8.14.1 --activate
COPY package.json pnpm-lock.yaml* ./
RUN pnpm install --frozen-lockfile || pnpm install
COPY . .
RUN pnpm build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        bash \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        libpng-dev \
        freetype-dev \
        libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql intl zip gd bcmath opcache \
    && apk del icu-dev libzip-dev oniguruma-dev libpng-dev freetype-dev libjpeg-turbo-dev

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# nginx shares /var/www/html/public via a named volume so it can serve
# static files and act as the fastcgi document root. Named volumes only
# auto-populate from the image once, so keep a pristine copy here that
# the entrypoint resyncs from on every boot (handles redeploys with new
# asset hashes).
RUN cp -a /var/www/html/public /var/www/html-public-src

RUN chown -R www-data:www-data /var/www/html /var/www/html-public-src

USER www-data

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
