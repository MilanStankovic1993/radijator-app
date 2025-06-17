FROM php:8.3-fpm

# System dependencies including libicu-dev for intl PHP extension and PostgreSQL client libs
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev libpq-dev default-mysql-client libicu-dev postgresql-client

# PHP extensions including intl and pdo_pgsql for PostgreSQL
RUN docker-php-ext-install intl pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Kopiraj production .env fajl kao .env za Laravel aplikaciju
COPY .env.production .env

# Install dependencies and run artisan setup commands including migrations and seeders
RUN composer install --optimize-autoloader --no-dev \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan storage:link \
 && php artisan migrate --force \
 && php artisan db:seed --force

CMD php artisan serve --host=0.0.0.0 --port=8080
