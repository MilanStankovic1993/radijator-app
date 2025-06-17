FROM php:8.3-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev libpq-dev default-mysql-client

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev

RUN php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan storage:link

CMD php artisan serve --host=0.0.0.0 --port=8080
