FROM php:8.1-apache

RUN a2enmod rewrite

RUN docker-php-ext-install mysqli

# Copy whole project into /var/www/klink to preserve original layout
WORKDIR /var/www/klink
COPY . /var/www/klink

# Serve the public directory as Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/klink/public

RUN sed -ri \
    -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

RUN chown -R www-data:www-data /var/www/klink

EXPOSE 80