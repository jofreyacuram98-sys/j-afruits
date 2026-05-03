# Use the official PHP image with Apache
FROM php:8.2-apache

# Install required system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy the entire project into the container
COPY . /var/www/html/

# Set the correct permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite

# Expose the port Render expects
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
