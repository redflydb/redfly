# https://hub.docker.com/_/php
FROM php:8.0-apache

# Install the dependencies for the PHP extensions.
RUN apt-get update -y && \
    apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libmcrypt-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libxml2-dev \
        libyaml-dev \
        unzip \
        wget && \
    docker-php-ext-install \
        bcmath \
        gd \
        mysqli \
        pdo_mysql \
        pgsql && \
    pecl install \
        mcrypt-1.0.6 \
        yaml-2.2.3 \
        xdebug-3.2.2 && \
    docker-php-ext-enable \
        mcrypt \
        yaml

# Copy the configuration files to their respective places.
COPY ./assets/config.ini /usr/local/etc/php/conf.d/
COPY ./assets/redfly-site.conf /etc/apache2/conf-available/

# disable apache2 header to hide apache version.
RUN sed -i 's/^ServerTokens OS/ServerTokens Prod/g' /etc/apache2/conf-enabled/security.conf && \
    sed -i 's/^ServerSignature On/#ServerSignature On/g' /etc/apache2/conf-enabled/security.conf && \
    sed -i 's/^#ServerSignature Off/ServerSignature Off/g' /etc/apache2/conf-enabled/security.conf && \
# hide php version.
    cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    echo 'expose_php = Off' >> /usr/local/etc/php/php.ini && \
# Enable mod_rewrite and the site configuration for the Apache HTTP server.
    a2enmod rewrite && \
    a2enconf redfly-site

# Expose a mount point for mounting and working with the web application files.
VOLUME ["/var/www"]
WORKDIR /var/www
