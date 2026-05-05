FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y unzip curl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project
COPY . /app
WORKDIR /app

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Start server
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "index.php"]