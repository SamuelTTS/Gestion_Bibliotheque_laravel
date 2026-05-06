# ==========================================
# STAGE 1 : Builder (PHP & Composer)
# ==========================================
FROM php:8.5-cli-alpine AS builder

WORKDIR /app

# Installation des dépendances système pour le build
RUN apk add --no-cache \
    unzip \
    libzip-dev \
    libpng-dev \
    nodejs \
    npm

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 1. Installer les dépendances PHP (Optimisation du cache Docker)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# 2. Installer les dépendances Node et build les assets (Vite)
COPY package.json package-lock.json* ./
RUN npm install
COPY . .
RUN npm run build

# 3. Finaliser l'autoloader PHP
RUN composer dump-autoload --optimize --no-dev

# ==========================================
# STAGE 2 : Production Image
# ==========================================
FROM php:8.5-fpm-alpine AS production

LABEL maintainer="votre-email@example.com"

WORKDIR /var/www

# Installation des dépendances de RUNTIME (seulement le nécessaire)
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    icu-libs \
    oniguruma \
    bash

# Installation des extensions PHP indispensables pour Laravel
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
        intl \
    && apk del .build-deps

# Configuration de PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configuration OpCache (Crucial pour Laravel)
RUN echo "opcache.memory_consumption=128" > $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.enable_cli=1" >> $PHP_INI_DIR/conf.d/opcache.ini

# Copie des fichiers depuis le builder
COPY --from=builder --chown=www-data:www-data /app /var/www

# Gestion des permissions pour le stockage et le cache
RUN mkdir -p /var/www/storage/framework/{sessions,views,cache} \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Sécurité : On utilise l'utilisateur non-root par défaut de l'image alpine
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
