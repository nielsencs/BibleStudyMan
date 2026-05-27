FROM php:8.3.21-apache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install pdo pdo_mysql mysqli && docker-php-ext-enable mysqli
RUN a2enmod rewrite

COPY composer.json /var/www/html/composer.json
WORKDIR /var/www/html
RUN composer install --no-interaction --prefer-dist --no-dev

COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html
