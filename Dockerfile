FROM php:8.2-apache

# Apache fix (IMPORTANT)
RUN a2dismod mpm_event \
  && a2dismod mpm_worker \
  && a2enmod mpm_prefork

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable rewrite
RUN a2enmod rewrite

# Set working dir
WORKDIR /var/www/html

# Copy project
COPY . .

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80