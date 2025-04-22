FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y libssl-dev pkg-config git unzip

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli pdo pdo_mysql

# Enable Apache rewrite module and set permissions
RUN a2enmod rewrite
