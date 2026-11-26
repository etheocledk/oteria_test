# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1: compile the Vue front-end with Laravel Mix (Node is not shipped in
# the final image, which keeps it smaller and reduces its attack surface).
# ---------------------------------------------------------------------------
FROM node:24-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json webpack.mix.js ./
RUN npm ci

COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2: install PHP dependencies (production only, no dev tools).
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

# ---------------------------------------------------------------------------
# Stage 3: runtime image, PHP 8.4 served by Apache.
# ---------------------------------------------------------------------------
FROM php:8.4-apache AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql opcache \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

# Production PHP settings, Apache document root on public/ and hardening.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && a2enmod rewrite headers
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-securedesk.ini"
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/security.conf /etc/apache2/conf-available/security.conf

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data --from=assets /app/public/js ./public/js
COPY --chown=www-data:www-data --from=assets /app/public/css ./public/css
COPY --chown=www-data:www-data --from=assets /app/public/mix-manifest.json ./public/mix-manifest.json

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
