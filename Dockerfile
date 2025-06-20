# Use official PHP image with Apache
FROM php:8.1-apache

# Install system dependencies required by cURL extension
RUN apt-get update && \
    apt-get install -y libcurl4-openssl-dev pkg-config && \
    docker-php-ext-install curl

# Enable Apache mod_rewrite (good for frameworks, optional)
RUN a2enmod rewrite

# Copy all project files to Apache's public directory
COPY . /var/www/html/

# Set correct permissions (optional but helps prevent 403 errors)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (default for Apache)
EXPOSE 80
