# syntax=docker/dockerfile:1

# --- Base image: PHP 8.3 Alpine (runtime) ---
FROM php:8.3-cli-alpine AS base

# Build stage: compile production dependencies for the final image.
FROM composer:2 AS build-composer

WORKDIR /app

# Copy composer config before installing so the lock file is honored.
COPY composer.json composer.lock ./

RUN --mount=type=cache,id=composer-cache,target=/root/.cache/composer \
    composer install --no-interaction --no-progress --optimize-autoloader --no-dev

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

# --- Build stage ---
FROM base AS build

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

WORKDIR /app

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    FILESYSTEM_DISK=local \
    QUEUE_CONNECTION=sync \
    CACHE_STORE=file \
    SESSION_DRIVER=file

# 1. Copy dependencies
COPY --from=build-composer /app/vendor /app/vendor
# 2. Copy the actual compiled source code from the build stage
COPY --from=build /app /app

# Copy compiled PHP extensions directly from the base build layer
COPY --from=base /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=base /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Ensure permissions are correct for Laravel writing tasks
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

USER www-data

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost:8080/ || exit 1

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
