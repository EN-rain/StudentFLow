FROM node:20-bookworm AS assets
WORKDIR /app
COPY package*.json vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm install && npm run build

FROM php:8.3-apache-bookworm
WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-install intl pdo pdo_pgsql zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=assets /app/public/build ./public/build
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/render-start.sh /usr/local/bin/render-start.sh

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && chmod +x /usr/local/bin/render-start.sh \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
CMD ["render-start.sh"]
