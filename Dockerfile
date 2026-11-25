FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y libpq-dev curl unzip ca-certificates \
    && docker-php-ext-install pdo_pgsql pgsql \
    && a2enmod rewrite headers \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN mkdir -p storage/logs public/assets/icons \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage

COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf
EXPOSE 10000
CMD php -S 0.0.0.0:${PORT:-10000} -t /var/www/html/public /var/www/html/public/index.php
