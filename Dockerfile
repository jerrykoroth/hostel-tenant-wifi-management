FROM php:8.2-apache

# Enable mod_rewrite for .htaccess
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all files to the Apache directory
COPY . /var/www/html/

# Set proper permissions (optional but safe)
RUN chown -R www-data:www-data /var/www/html

# Expose Apache port
EXPOSE 80
