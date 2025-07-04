FROM composer:latest AS composer
FROM php:8.3-apache

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip curl wget software-properties-common \
    libzip-dev libicu-dev libxml2-dev libcurl4-openssl-dev \
    libpng-dev libjpeg-dev libfreetype6-dev libgettextpo-dev \
    zlib1g-dev libwebp-dev libbz2-dev lsb-release apt-transport-https \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        mysqli intl gettext pdo pdo_mysql zip curl xml opcache calendar exif

RUN apt-get update && apt-get install -y libjpeg62-turbo-dev \
    && docker-php-ext-configure gd \
        --with-jpeg \
        --with-freetype \
        --with-webp \
    && docker-php-ext-install gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Enable Apache rewrite
RUN a2enmod rewrite

# Change permissions to make sure Apache can write to necessary directories
RUN chown -R www-data:www-data /var/www/html

# Set Apache to listen on port 8181 (to avoid host conflicts)
RUN sed -i 's/80/8181/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Set DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

# Copy custom php config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html


EXPOSE 8181