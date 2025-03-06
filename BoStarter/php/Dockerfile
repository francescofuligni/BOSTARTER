FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libpng-dev \
    libmongoc-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli gd zip
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Enable Apache rewrite module
RUN a2enmod rewrite