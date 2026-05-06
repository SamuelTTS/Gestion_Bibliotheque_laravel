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

# Installation des dépendances de RUNTIME (bibliothèques partagées)
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    icu-libs \
    oniguruma \
    bash

# Installation des extensions PHP (Correction de l'erreur de compilation)
# On sépare les installations pour éviter les conflits de répertoires 'modules/*'
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    # Étape A : Extensions standards et rapides
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath \
    # Étape B : Extensions lourdes installées une par une (évite les erreurs de stat modules/*)
    && docker-php-ext-install gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install intl \
    && docker-php-ext-install opcache \
    && apk del .build-deps

# Configuration de PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configuration OpCache (Optimisé pour PHP 8.5 / Laravel)
RUN echo "opcache.memory_consumption=128" > $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.enable_cli=1" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.jit=tracing" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.jit_buffer_size=64M" >> $PHP_INI_DIR/conf.d/opcache.ini

# Copie des fichiers depuis le builder
COPY --from=builder --chown=www-data:www-data /app /var/www

# Gestion des permissions pour le stockage et le cache
RUN mkdir -p /var/www/storage/framework/{sessions,views,cache} \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Sécurité : On utilise l'utilisateur non-root
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
