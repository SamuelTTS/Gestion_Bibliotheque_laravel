
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
    bash \
    zlib

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    zlib-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    # On installe les extensions une par une ou par petits groupes stables
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install intl \
    # On force l'activation de l'opcache natif au lieu de tenter de le re-compiler
    && docker-php-ext-enable opcache \
    && apk del .build-deps

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN printf "opcache.enable=1\n\
opcache.enable_cli=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=4000\n\
opcache.revalidate_freq=2\n\
opcache.jit=tracing\n\
opcache.jit_buffer_size=64M\n" > $PHP_INI_DIR/conf.d/docker-php-ext-opcache.ini

COPY --from=builder --chown=www-data:www-data /app /var/www

RUN mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/logs \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
