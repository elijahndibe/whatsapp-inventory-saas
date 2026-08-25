# syntax=docker/dockerfile:1

# --- Frontend assets ---
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY resources resources
COPY vite.config.js ./
RUN npm run build

# --- PHP dependencies ---
FROM composer:2 AS vendor
WORKDIR /app
COPY database database
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev

# --- Runtime image ---
FROM php:8.2-fpm-alpine AS app
WORKDIR /var/www/html

# Runtime shared libraries stay installed permanently — gd/intl/zip need
# them to load at all, not just to compile. Only the -dev/headers/build
# toolchain (grouped as the .build-deps virtual package) gets removed
# afterwards; deleting the -dev packages directly would also cascade-
# remove these runtime libs since apk sees them as only pulled in by the
# -dev packages, which is exactly the bug this split avoids.
RUN apk add --no-cache libpng libjpeg-turbo freetype libzip icu-libs oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd zip bcmath intl opcache \
    && apk del .build-deps

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache-custom.ini

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

RUN addgroup -g 1000 www && adduser -G www -g www -s /bin/sh -D www \
    && chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public

USER www

EXPOSE 9000
CMD ["php-fpm"]
