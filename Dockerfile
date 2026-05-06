# ================================================
# STAGE 1 — Build & installation des dépendances
# ================================================
FROM php:8.5-cli AS builder

LABEL maintainer="stchablintete@gmail.com"
LABEL app="laravel-app"
LABEL stage="builder"

# Variables de build
ARG COMPOSER_FLAGS="--no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction"

# Installation des dépendances système + extensions PHP pour Laravel + MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        zip \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        default-libmysqlclient-dev \
    && docker-php-ext-install \
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

# Récupérer Composer depuis son image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copier uniquement les fichiers de dépendances en premier
# (optimisation cache Docker : si composer.json ne change pas, cette couche est réutilisée)
COPY composer.json composer.lock ./

# Installer les dépendances sans les packages de développement
RUN composer install $COMPOSER_FLAGS

# Copier tout le code source
COPY . .

# Générer l'autoloader optimisé pour la production
RUN composer dump-autoload --optimize --no-dev

# Nettoyer les fichiers inutiles en production
RUN rm -rf tests/ \
           .env.example \
           .git/ \
           storage/logs/*.log \
           bootstrap/cache/*.php

# ================================================
# STAGE 2 — Image de production légère (Alpine)
# ================================================
FROM php:8.5-fpm-alpine AS production

LABEL maintainer="stchablintete@gmail.com"
LABEL app="laravel-app"
LABEL stage="production"

# Extensions PHP nécessaires en production avec MySQL
RUN apk add --no-cache \
        libzip-dev \
        libpng-dev \
        oniguruma-dev \
        libxml2-dev \
        mysql-client \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        bcmath \
        gd \
        xml \
        opcache

WORKDIR /var/www/html

# Copier uniquement ce qui est nécessaire depuis le builder
COPY --from=builder /app .

# Permissions Laravel obligatoires
RUN chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache \
    && chmod -R 775 \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache

# Configuration PHP-FPM optimisée pour la production
RUN echo "opcache.enable=1"                    >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256"   >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0"    >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0"        >> /usr/local/etc/php/conf.d/opcache.ini

# Variables d'environnement par défaut (surchargeables au docker run)
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=mysql \
    DB_HOST=mysql \
    DB_PORT=3306 \
    DB_DATABASE=biblio \
    DB_USERNAME=root \
    DB_PASSWORD=root

# Healthcheck : vérifie que PHP-FPM répond correctement
HEALTHCHECK --interval=30s --timeout=10s --start-period=15s --retries=3 \
    CMD php-fpm -t || exit 1

EXPOSE 9000

CMD ["php-fpm"]
