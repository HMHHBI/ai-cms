# PHP 8.4 with Apache use karein
FROM php:8.4-apache

# Zaroori Extensions install karein
RUN apt-get update && apt-get install -y \
  libpng-dev \
  libonig-dev \
  libxml2-dev \
  zip \
  unzip \
  git \
  curl

# PHP extensions install karein
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Apache rewrite module enable karein (Laravel routes ke liye)
RUN a2enmod rewrite

# Working directory set karein
WORKDIR /var/www/html

# Project files copy karein
COPY . .

# Composer install karein
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Permissions set karein
RUN chown -R www-data:www-data storage bootstrap/cache

# Apache document root ko 'public' folder par set karein
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Port 80 open karein
EXPOSE 80

# Apache start karein
CMD ["apache2-foreground"]