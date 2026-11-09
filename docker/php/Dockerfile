# syntax=docker/dockerfile:1.7
ARG PHP_VERSION=8.5

FROM composer:2 AS composer-bin

# ---------- base ----------
FROM php:${PHP_VERSION}-fpm-alpine AS base
ARG USER_ID=1000
ARG GROUP_ID=1000
ENV TZ=Asia/Ho_Chi_Minh \
  COMPOSER_ALLOW_SUPERUSER=1

COPY --from=ghcr.io/mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache bash git unzip curl tzdata su-exec \
  && install-php-extensions pdo_mysql redis pcntl bcmath intl zip gd exif opcache \
  && addgroup -g ${GROUP_ID} app \
  && adduser -D -u ${USER_ID} -G app -s /bin/bash app

COPY docker/php/php.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zzz-app.conf

WORKDIR /var/www/html
RUN chown app:app /var/www/html
EXPOSE 9000

# ---------- dev ----------
FROM base AS dev
COPY docker/php/php-dev.ini $PHP_INI_DIR/conf.d/zzz-dev.ini
CMD ["php-fpm"]

# ---------- prod ----------
FROM base AS prod
COPY docker/php/php-prod.ini $PHP_INI_DIR/conf.d/zzz-prod.ini
COPY --chmod=755 docker/php/entrypoint.sh /usr/local/bin/entrypoint

COPY backend/composer.json backend/composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
  composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY backend/ ./
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
  && composer dump-autoload --optimize --classmap-authoritative --no-dev \
  && chown -R app:app storage bootstrap/cache

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

# ---------- web-prod ----------
FROM nginx:1.27-alpine AS web-prod
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=prod /var/www/html/public /var/www/html/public
