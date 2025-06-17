FROM php:8.3-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev libpq-dev default-mysql-client libicu-dev postgresql-client

# PHP extensions
RUN docker-php-ext-install intl pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy Laravel project files
COPY . .

# Kopiraj production .env fajl
COPY .env.production .env

# Install dependencies and prepare Laravel + Filament
RUN composer install --optimize-autoloader --no-dev \
 && php artisan config:clear \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan storage:link \
 && php artisan migrate --force \
 && php artisan db:seed --force \
 && php artisan filament:assets --no-interaction \
 && mkdir -p public/vendor \
 && cp -r public/vendor /var/www/public/vendor

# Start Laravel dev server
CMD php artisan serve --host=0.0.0.0 --port=8080
