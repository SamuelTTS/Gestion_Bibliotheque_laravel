# ==========================================
# STAGE 1 : Builder
# ==========================================
FROM php:8.5-cli-alpine AS builder

WORKDIR /app

RUN apk add --no-cache \
    unzip \
    libzip-dev \
    libpng-dev \
    nodejs \
    npm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json* ./
RUN npm install
COPY . .
RUN npm run build

RUN composer dump-autoload --optimize --no-dev



FROM php:8.5-fpm-alpine AS production

WORKDIR /var/www

RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    icu-libs \
    oniguruma \
    bash

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath \
    && docker-php-ext-install gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install intl \
    && docker-php-ext-install opcache \
    && apk del .build-deps

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN echo "opcache.memory_consumption=128" > $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.enable_cli=1" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.jit=tracing" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.jit_buffer_size=64M" >> $PHP_INI_DIR/conf.d/opcache.ini

COPY --from=builder --chown=www-data:www-data /app /var/www

RUN mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/framework/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
