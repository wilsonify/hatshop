FROM php:8.2-apache AS shared_base

# Set default environment variables
ENV APACHE_SERVER=localhost
ENV DEBIAN_FRONTEND=noninteractive

WORKDIR /var/www

# Update base system, install dependencies, PHP extensions and Composer
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends \
    git \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libkrb5-dev \
    libmemcached-dev \
    libonig-dev \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install -j$(nproc) bcmath curl iconv intl mbstring pdo pdo_pgsql pgsql soap xml zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy core library first (for composer path repository)
COPY --chown=www-data:www-data core/ /var/www/core/

# Copy application files (includes images and product_images)
COPY --chown=www-data:www-data app/html/ /var/www/html/
COPY app/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Install dependencies
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-progress \
    && a2enmod rewrite \
    && a2ensite 000-default.conf

EXPOSE 80
USER www-data
CMD ["apache2-foreground"]
