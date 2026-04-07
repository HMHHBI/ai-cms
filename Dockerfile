FROM php:8.2-apache

# 🔥 HARD FIX: remove all MPM conflicts
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true

# Ensure only prefork is enabled
RUN a2enmod mpm_prefork

# Remove conflicting configs (IMPORTANT)
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf
RUN rm -f /etc/apache2/mods-enabled/mpm_worker.load
RUN rm -f /etc/apache2/mods-enabled/mpm_worker.conf

# Laravel requirements
RUN docker-php-ext-install pdo pdo_mysql

# Enable rewrite
RUN a2enmod rewrite

# Set document root to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy project
WORKDIR /var/www/html
COPY . .

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80