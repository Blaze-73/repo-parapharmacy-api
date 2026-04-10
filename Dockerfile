# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions for PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Set the Apache DocumentRoot to the Laravel public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Copy project files to the container
COPY . /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
