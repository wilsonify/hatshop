FROM php:8.2-apache AS shared_base

# Set default environment variables
ENV APACHE_SERVER=localhost
ENV APACHE_PORT=8080
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
COPY --chown=root:root --chmod=555 core/ /var/www/core/

# Install core dependencies
WORKDIR /var/www/core
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-progress

# Copy application files (includes images and product_images)
COPY --chown=root:root --chmod=555 app/html/ /var/www/html/
COPY app/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Install dependencies, configure Apache for non-root port, and create writable directories
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-progress \
    && a2enmod rewrite \
    && a2ensite 000-default.conf \
    && mkdir -p /var/log/hatshop \
    && mkdir -p /var/www/html/presentation/templates_c \
    && mkdir -p /var/www/html/presentation/cache \
    && chown -R www-data:www-data /var/log/hatshop \
    && chown -R www-data:www-data /var/www/html/presentation/templates_c \
    && chown -R www-data:www-data /var/www/html/presentation/cache \
    && chmod -R 755 /var/www/html/presentation/templates_c \
    && chmod -R 755 /var/www/html/presentation/cache \
    && sed -i 's/Listen 80/Listen ${APACHE_PORT}/g' /etc/apache2/ports.conf \
    && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2

EXPOSE 8080
USER www-data
CMD ["apache2-foreground"]
