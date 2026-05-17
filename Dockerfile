FROM php:8.5-cli-alpine AS builder
WORKDIR /app

RUN apk add --no-cache unzip libzip-dev libpng-dev nodejs npm
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --optimize

COPY package.json package-lock.json* ./
RUN npm install

RUN npm run build

RUN rm -f bootstrap/cache/services.php bootstrap/cache/packages.php bootstrap/cache/config.php

FROM builder AS tester
WORKDIR /app

FROM builder AS production-builder
WORKDIR /app
RUN composer install --no-dev --no-scripts --optimize-autoloader

FROM php:8.5-fpm-alpine AS production
WORKDIR /var/www/html

RUN apk add --no-cache \
    libpng libjpeg-turbo freetype libzip icu-libs oniguruma bash zlib

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev oniguruma-dev icu-dev zlib-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apk del .build-deps

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN printf "opcache.enable=1\nopcache.enable_cli=1\nopcache.memory_consumption=128\nopcache.interned_strings_buffer=8\nopcache.max_accelerated_files=4000\nopcache.revalidate_freq=2\nopcache.jit=tracing\nopcache.jit_buffer_size=64M\n" > $PHP_INI_DIR/conf.d/docker-php-ext-opcache.ini

COPY --from=production-builder --chown=www-data:www-data /app .

USER root
RUN chmod +x artisan

RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
