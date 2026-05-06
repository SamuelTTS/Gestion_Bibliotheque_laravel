# ==========================================
# STAGE 1 : Builder (Installation des dépendances)
# ==========================================
FROM php:8.2-cli-alpine as builder

WORKDIR /app

# Installation des outils nécessaires au build
RUN apk add --no-cache unzip libzip-dev libpng-dev

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie des fichiers de dépendances en premier (optimisation du cache Docker)
COPY composer.json composer.lock ./

# Installation des dépendances sans les scripts de post-installation (on les fera après)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copie de tout le projet
COPY . .

RUN npm install && npm run build
# Finalisation de l'autoloader et des scripts Laravel
RUN composer dump-autoload --optimize --no-dev

# ==========================================
# STAGE 2 : Production (L'image finale)
# ==========================================
FROM php:8.2-fpm-alpine as production

LABEL maintainer="stchablintete@gmail.com"

WORKDIR /var/www

# Installation des dépendances système d'exécution (Runtime)
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    bash

# Configuration et installation des extensions PHP indispensables pour Laravel 12
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    intl

# Optimisation de la configuration PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# Copie du code depuis le stage builder
COPY --from=builder --chown=www-data:www-data /app /var/www

# Création des dossiers nécessaires et gestion des permissions
# Essentiel pour éviter les erreurs de "Permission Denied" sur storage/
RUN mkdir -p /var/www/storage/framework/{sessions,views,cache} \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Exposition du port PHP-FPM
EXPOSE 9000

# Passage à l'utilisateur non-root pour la sécurité
USER www-data

CMD ["php-fpm"]
