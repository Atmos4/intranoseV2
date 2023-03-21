FROM php:8.1-apache

RUN apt-get update && \
    docker-php-ext-install pdo pdo_mysql
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
RUN a2enmod rewrite