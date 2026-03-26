FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpq-dev

RUN docker-php-ext-install zip pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . /app

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# ✅ Clear cache FIRST
RUN php artisan config:clear
RUN php artisan route:clear
RUN php artisan view:clear
RUN php artisan cache:clear

# # ✅ Then run migrations
# RUN php artisan migrate --force

EXPOSE 10000

CMD php -S 0.0.0.0:10000 -t public