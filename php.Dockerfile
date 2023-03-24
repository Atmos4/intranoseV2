FROM php:8.1-apache

RUN apt-get update && \
    apt-get install -y libicu-dev && \
    docker-php-ext-install pdo pdo_mysql && \
    docker-php-ext-install intl && \
    docker-php-ext-install opcache

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer self-update

WORKDIR /var/www/html
COPY . .

RUN composer install

# Authorize the .htaccess to execute
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
RUN a2enmod rewrite
