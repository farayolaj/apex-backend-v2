FROM php:8.2-apache

ARG USER="nairaboom"

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y --fix-missing software-properties-common lsb-release apt-transport-https ca-certificates wget
# RUN apt-add-repository ppa:ondrej/php

RUN apt-get clean
RUN apt-get update && apt-get install -y \
libpng-dev \
zlib1g-dev \
libzip-dev \
libicu-dev \
g++ \
libjpeg62-turbo-dev \
libwebp-dev \
libfreetype6-dev \
libxml2-dev \
libbz2-dev \
libonig-dev \
git \
zip \
unzip \
libcurl4-gnutls-dev \
curl


RUN apt-get -y install exif
RUN apt-get install --fix-missing -y libpq-dev
RUN apt-get install --no-install-recommends -y libpq-dev

RUN docker-php-ext-install -j$(nproc) intl
RUN docker-php-ext-configure intl && docker-php-ext-install mysqli
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install zip
RUN docker-php-ext-install gettext

RUN docker-php-ext-install curl
RUN docker-php-ext-install xml
RUN docker-php-ext-install pdo
RUN docker-php-ext-install opcache
RUN docker-php-ext-install calendar
RUN docker-php-ext-install exif

RUN a2enmod rewrite
RUN a2enmod rewrite headers
RUN service apache2 restart

# Change permissions to make sure Apache can write to necessary directories
RUN chown -R www-data:www-data /var/www/html

# CREATE GROUP AND USER
RUN groupadd -r ${USER} && useradd -g ${USER} ${USER}

# clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN echo "ServerName localhost" | tee -a /etc/apache2/apache2.conf
  
RUN sed -i "s/Listen 80/Listen 8082/" /etc/apache2/ports.conf



EXPOSE 8082