# syntax=docker/dockerfile:1

# --- Base image: PHP 8.3 Alpine (runtime) ---
FROM php:8.3-cli-alpine AS base

# Build stage: install production dependencies for the final image.
FROM composer:2 AS build-composer

WORKDIR /app

# Copy composer config before installing so the lock file is honored.
COPY composer.json composer.lock ./

# Install dependencies with BuildKit's cache mount.
# --ignore-platform-req=ext-mongodb keeps the composer stage building while the
# mongodb extension is installed in the later base production stage.
RUN --mount=type=cache,id=composer-cache,target=/root/.cache/composer \
    composer install --no-interaction --no-progress --optimize-autoloader --no-dev --no-scripts --ignore-platform-req=ext-mongodb

# --- Build stage: compile the frontend ---
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
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

USER www-data

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost:8080/ || exit 1

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
