# syntax=docker/dockerfile:1

# --- Base image: PHP 8.3 Alpine (runtime) ---
FROM php:8.3-cli-alpine AS base

# System libs required by the MongoDB PHP extension, intl, and the build tools.
# (Added here because they are needed to compile the PECL extensions at build time.)
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    zlib-dev \
    mariadb-connector-c-dev \
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure pdo_mysql --with-mariadb \
    && docker-php-ext-configure pdo_pgsql \
    && docker-php-ext-configure pdo_dblib --with-freetds \
    && docker-php-ext-install \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        pdo_pgsql \
        pdo_dblib \
        zip

# --- Composer stage ---
FROM composer:latest AS composer

WORKDIR /srv/app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --optimize-autoloader

# --- Build stage ---
FROM base AS build

ARG APP_ENV=production
ARG APP_VERSION=1.0.0
ARG GIT_COMMIT=unknown

LABEL org.opencontainers.image.title="Laravel PHP App" \
      org.opencontainers.image.version="${APP_VERSION}" \
      org.opencontainers.image.revision="${GIT_COMMIT}" \
      org.opencontainers.image.description="Docker image for deploying this Laravel/PostgreSQL app to Render"

ENV APP_ENV=${APP_ENV} \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    PHP_TELEMETRY_SILENT=1 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

COPY --from=composer /srv/app/vendor /srv/app/vendor
COPY . .

# Laravel boilerplate that `composer install` creates from the installed
# packages, and the compiled frontend assets.
RUN php artisan package:discover --ansi \
    && php artisan config:cache --ansi \
    && php artisan route:cache --ansi \
    && php artisan view:cache --ansi \
    && rm -rf \
        database/*.sqlite \
        storage/framework/cache/data \
        storage/logs/*.log \
        tests \

# --- Production runtime ---
FROM base AS production

WORKDIR /srv/app

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    FILESYSTEM_DISK=local \
    QUEUE_CONNECTION=sync \
    CACHE_STORE=file \
    SESSION_DRIVER=file \
    PHP_TELEMETRY_SILENT=1 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

COPY --from=composer /srv/app/vendor /srv/app/vendor

# Copy compiled PECL binaries straight into the extension directory.
# No pecl, no xdebug, and no Alpine dev libs are needed in this stage.
COPY --from=base /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=base /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

USER www-data

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost/ || exit 1

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
