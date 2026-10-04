# syntax=docker/dockerfile:1

# --- Base image: PHP 8.3 Alpine (runtime) ---
FROM php:8.3-cli-alpine AS base

# System libs required by the MongoDB PHP extension, intl, and the build tools.
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    zlib-dev \
    postgresql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libvpx-dev \
    libzip-dev \
    freetds-dev \
    unixodbc-dev \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-configure pdo_pgsql \
    && docker-php-ext-configure pdo_dblib \
    && docker-php-ext-install \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        pdo_pgsql \
        zip \
    && pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis \
    && apk del --no-cache $PHPIZE_DEPS

# --- Composer stage ---
# Fixed to use the Alpine variant so 'apk add' doesn't crash
FROM composer:latest-alpine AS composer

WORKDIR /eportfolioAPI/

# Re-compile dependencies for the composer container environment
RUN apk add --no-cache $PHPIZE_DEPS icu-dev libxml2-dev oniguruma-dev zlib-dev \
    && pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis \
    && apk del --no-cache $PHPIZE_DEPS

# Copy local composer configs (Adjust path to ./eportfolioAPI/ if your Dockerfile is in the root directory)
COPY ./eportfolioAPI/composer.json ./eportfolioAPI/composer.lock ./
# Fixed: Removed the trailing dash "-"
RUN composer install --no-interaction --no-progress --optimize-autoloader --no-dev

# --- Build stage ---
FROM base AS build

ARG APP_ENV=production
ARG APP_VERSION=1.0.0
ARG GIT_COMMIT=unknown

WORKDIR /eportfolioAPI/

ENV APP_ENV=${APP_ENV} \
    APP_DEBUG=false \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

COPY --from=composer /eportfolioAPI/vendor /eportfolioAPI/vendor
# Copy the application source code (Adjust path if Dockerfile is outside eportfolioAPI subfolder)
COPY ./eportfolioAPI .

RUN php artisan package:discover --ansi \
    && php artisan config:cache --ansi \
    && php artisan route:cache --ansi \
    && php artisan view:cache --ansi \
    && rm -rf \
        database/*.sqlite \
        storage/framework/cache/data \
        storage/logs/*.log \
        tests

# --- Production runtime ---
FROM base AS production

WORKDIR /eportfolioAPI/

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    FILESYSTEM_DISK=local \
    QUEUE_CONNECTION=sync \
    CACHE_STORE=file \
    SESSION_DRIVER=file

# 1. Copy dependencies
COPY --from=composer /eportfolioAPI/vendor /eportfolioAPI/vendor
# 2. Copy the actual compiled source code from the build stage (CRUCIAL FIX)
COPY --from=build /eportfolioAPI /eportfolioAPI

# Copy compiled PHP extensions directly from the base build layer
COPY --from=base /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=base /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Ensure permissions are correct for Laravel writing tasks
RUN chown -R www-data:www-data /eportfolioAPI/storage /eportfolioAPI/bootstrap/cache

USER www-data

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost:8080/ || exit 1

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
