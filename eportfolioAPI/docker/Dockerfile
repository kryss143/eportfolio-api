# syntax=docker/dockerfile:1

# --- Base image: PHP 8.3 Alpine (runtime + build deps) ---
FROM php:8.3-cli-alpine AS base

# Install system deps required by Laravel, Composer, and the MongoDB/Redis
# PHP extensions (the ones your composer.json/lockfile expects).
RUN apk add --no-cache \
    nginx \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    icu-dev \
    postgresql-dev \
    zlib-dev \
    && docker-php-ext-install -j "$(nproc)" \
        gd \
        pdo_mysql \
        mysqli \
        pdo_pgsql \
        zip \
        exif \
        pcntl \
        bcmath \
    && docker-php-ext-enable pdo_mysql mysqli pdo_pgsql zip exif pcntl bcmath

# Build stage: install Composer.
FROM composer:2 AS build-composer

WORKDIR /app

# Copy composer config before installing so the lock file is honored.
COPY composer.json composer.lock ./

# Install dependencies with BuildKit's cache mount.
# --ignore-platform-req=ext-mongodb keeps the composer stage building while the
# mongodb extension is installed in the later base production stage.
RUN --mount=type=cache,id=composer-cache,target=/root/.cache/composer \
    composer install --no-interaction --no-progress --optimize-autoloader --no-dev --no-scripts --ignore-platform-req=ext-mongodb

# --- Build stage: compile the frontend (Vite -> public/build) ---
FROM node:20-alpine AS build

ARG APP_ENV=production
ARG APP_VERSION=1.0.0
ARG GIT_COMMIT=unknown

WORKDIR /app

ENV APP_ENV=${APP_ENV} \
    APP_DEBUG=false \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

COPY --from=build-composer /app/vendor /app/vendor
# Copy the application source code
COPY . .

# Build the frontend (Vite -> public/build)
RUN npm ci && npm run build

# --- Production runtime ---
FROM base AS production

WORKDIR /app

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    FILESYSTEM_DISK=local \
    QUEUE_CONNECTION=sync \
    CACHE_STORE=file \
    SESSION_DRIVER=file

# 1. Copy Composer dependencies
COPY --from=build-composer /app/vendor /app/vendor
# 2. Copy the compiled application and built frontend from the build stage
COPY --from=build /app /app

# Copy compiled PHP extensions from the base build layer
COPY --from=base /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=base /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Ensure permissions are correct for Laravel writing tasks
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    mkdir -p /var/www/storage/framework/views /var/www/storage/logs && \
    chown -R www-data:www-data /var/www /var/www/storage /var/www/bootstrap/cache

# 1. Copy Nginx site config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# 2. Remove the stock Nginx site that tries to serve /usr/share/nginx/html
RUN rm -f /etc/nginx/conf.d/default.conf

# 3. Make the Laravel public dir the Nginx web root
RUN ln -sf /app/public /var/www/html

# 4. Start Nginx (serves static assets + proxies PHP) and PHP built-in server
#    Nginx listens on 8080 (Render's port).
#    PHP built-in server runs on 127.0.0.1:9000 and handles /index.php.
CMD ["sh", "-c", "php artisan serve --host=127.0.0.1 --port=9000 --no-reload & \
    nginx -g 'daemon off;'"]
