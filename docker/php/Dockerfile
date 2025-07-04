# STAGE 1: Composer
FROM composer:latest AS composer

# STAGE 2: Main PHP App
FROM php:8.3-apache

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install PHP extensions and system dependencies
RUN apt-get update && apt-get install -y \
   supervisor git unzip zip curl wget software-properties-common \
    libzip-dev libicu-dev libxml2-dev libcurl4-openssl-dev \
    libpng-dev libjpeg-dev libfreetype6-dev libgettextpo-dev \
    zlib1g-dev libwebp-dev libbz2-dev lsb-release apt-transport-https \
    libjpeg62-turbo-dev \
 && apt-get clean && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-configure intl \
 && docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
 && docker-php-ext-install -j$(nproc) \
    mysqli intl gettext pdo pdo_mysql zip curl xml opcache calendar exif gd \
 && pecl install redis \
 && docker-php-ext-enable redis


# Apache rewrite
RUN a2enmod rewrite

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Port & document root adjustments
RUN sed -i 's/80/8181/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Supervisor config
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/supervisor/conf.d/ /etc/supervisor/conf.d/
RUN mkdir -p /var/log/supervisor

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Working directory and entrypoint
WORKDIR /var/www/html

# Entrypoint script will run Supervisor (which in turn runs Apache)
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 8181
