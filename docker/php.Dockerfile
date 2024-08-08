FROM php:8.1-apache

#installing packages
RUN apt-get update && \
    apt-get install -y libicu-dev libzip-dev zip unzip yarn && \
    docker-php-ext-install pdo pdo_mysql && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    docker-php-ext-install opcache && \
    docker-php-ext-install zip 

#installing composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer self-update

#copy the directory and install composer packages
#the composer install then goes into the vendor directory, wich is a volume in docker-composer. 
#so it is not affected when the bind volume is mounted 
WORKDIR /var/www/html
COPY . .

# Authorize the .htaccess to execute
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
RUN a2enmod rewrite

# set the entrypoint to our wrapper script, and repeat the base
# image's command
ENTRYPOINT ["bash", "./docker/entrypoint.sh"]
CMD ["apache2-foreground"]
#   ^^^^^^^^^^^^^^^^^^^^^^
#   this is the base image's CMDw
