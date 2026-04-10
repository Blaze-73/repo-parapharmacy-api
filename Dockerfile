FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# IMPORTANT: Hugging Face uses port 7860
RUN sed -i 's/Listen 80/Listen 7860/' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:7860>/' /etc/apache2/sites-available/000-default.conf

# Set DocumentRoot to public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN composer install --no-dev --optimize-autoloader

# Expose Hugging Face port
EXPOSE 7860

CMD ["apache2-foreground"]
