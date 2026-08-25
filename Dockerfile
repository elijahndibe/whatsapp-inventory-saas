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

RUN apk add --no-cache \
        libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd zip bcmath intl opcache \
    && apk del libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev icu-dev oniguruma-dev

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache-custom.ini

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

RUN addgroup -g 1000 www && adduser -G www -g www -s /bin/sh -D www \
    && chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache

USER www

EXPOSE 9000
CMD ["php-fpm"]
