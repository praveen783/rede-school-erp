FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev

# Install PHP extensions
RUN docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . /app

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN php artisan config:clear
RUN php artisan cache:clear

# Expose port
EXPOSE 10000

# Start Laravel
CMD php artisan serve --host=0.0.0.0 --port=10000