FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Clean Apache MPM configs completely
RUN rm -f /etc/apache2/mods-enabled/mpm_*

# Enable only prefork (required for PHP)
RUN a2enmod mpm_prefork

# Enable rewrite
RUN a2enmod rewrite

# Copy project
COPY . /var/www/html/

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80