FROM php:8.2-apache

# Install extensions
# Install extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable apache rewrite
RUN a2enmod rewrite

# Increase upload limits to match database capability
RUN echo "upload_max_filesize = 64M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini

# Copy application source
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/
