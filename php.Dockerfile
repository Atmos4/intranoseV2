FROM php:8.1-apache

RUN apt-get update && \
    apt-get install -y libicu-dev libzip-dev zip unzip && \
    docker-php-ext-install pdo pdo_mysql && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    docker-php-ext-install opcache && \
    docker-php-ext-install zip 

RUN docker-php-ext-enable intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer self-update


WORKDIR /var/www/html
COPY . .
RUN composer install
RUN composer dump-autoload

# Authorize the .htaccess to execute
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
RUN a2enmod rewrite
