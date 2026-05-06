# ================================================
# STAGE 1 — Build & installation des dépendances
# ================================================
FROM php:8.2-cli AS builder

LABEL maintainer="stchablintete@gmail.com"
LABEL app="laravel-app"
LABEL stage="builder"

# Variables de build
ARG COMPOSER_FLAGS="--no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction"

# Installation des dépendances système + extensions PHP
# Note: PHP 8.5 n'existe pas encore officiellement en version stable, utilisation de 8.2 ou 8.3 recommandée.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        zip \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        default-libmysqlclient-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        bcmath \
        gd \
        xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Récupérer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Optimisation cache Docker
COPY composer.json composer.lock ./
RUN composer install $COMPOSER_FLAGS

# Copier le code source
COPY . .

# Générer l'autoloader optimisé
RUN composer dump-autoload --optimize --no-dev

# Nettoyage des fichiers inutiles
RUN rm -rf tests/ \
           .env.example \
           .git/ \
           storage/logs/*.log \
           bootstrap/cache/*.php

# ================================================
# STAGE 2 — Image de production légère (Alpine)
# ================================================
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="stchablintete@gmail.com"
LABEL app="laravel-app"
LABEL stage="production"

# Résolution de l'erreur "cp: can't stat 'modules/*'" : 
# 1. On installe les librairies de RUNTIME (nécessaires pour exécuter PHP)
# 2. On utilise .build-deps pour les librairies de COMPILATION (supprimées après)
RUN apk add --no-cache \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        oniguruma \
        libxml2 \
        mysql-client \
    && apk add --no-cache --virtual .build-deps \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        bcmath \
        gd \
        xml \
        opcache \
    && apk del .build-deps

WORKDIR /var/www/html

# Copier l'application depuis le builder
COPY --from=builder /app .

# Permissions Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configuration OPcache
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.revalidate_freq=0'; \
    } > /usr/local/etc/php/conf.d/opcache-optimized.ini

# Variables d'environnement
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=mysql \
    DB_HOST=mysql \
    DB_PORT=3306 \
    DB_DATABASE=biblio \
    DB_USERNAME=root \
    DB_PASSWORD=root

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=15s --retries=3 \
    CMD php-fpm -t || exit 1

EXPOSE 9000

CMD ["php-fpm"]
