ARG PHP_VERSION=8.3-cli

FROM php:${PHP_VERSION}-alpine AS base

# System libs required by the MongoDB PHP extension, intl, and the build tools
# Laravel `optimize` scripts rely on these while `composer install` runs.
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    curl \
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
    || true

# Install PHP extensions explicitly via PECL so the final image contains
# everything the app needs, independent of what Alpine preinstalls.
RUN pecl install \
    mongodb \
    redis \
    xdebug \
    && docker-php-ext-enable mongodb redis xdebug \
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
        zip \
    && docker-zenv -o /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
        zend_extension=xdebug.so \
        xdebug.mode=off \
        xdebug.start_with_request=noop

# --- Composer ---
FROM composer:latest AS composer

WORKDIR /srv/app

# Install the exact locked dependency tree in one step so the composer.lock
# hash drives the image cache. `composer install` errors out if lock is missing.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --optimize-autoloader

# --- Build the app ---
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

# Dependencies (production only) + the rest of the repo.
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
        vendor/bin

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

# Runtime-only deps (no build tooling, no dev packages).
COPY --from=composer /srv/app/vendor /srv/app/vendor

# Bring in the compiled assets from the build stage.
COPY --from=build /srv/app/public/build /srv/app/public/build
COPY --from=build /srv/app/public/mix-manifest.json /srv/app/public/mix-manifest.json
COPY --from=build /srv/app/storage /srv/app/storage

COPY --from=build --chown=www-data:www-data /srv/app/public/.well-known /srv/app/public/.well-known
COPY --from=build --chown=www-data:www-data /srv/app/public/index.php /srv/app/public/index.php
COPY --from=build --chown=www-data:www-data /srv/app/public/robots.txt /srv/app/public/robots.txt

USER www-data

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost/ || exit 1

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
