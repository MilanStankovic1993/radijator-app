FROM php:8.3-fpm

# Instalacija sistemskih paketa uključujući nginx i supervisor
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libpq-dev default-mysql-client libicu-dev \
    postgresql-client

# Instalacija PHP ekstenzija
RUN docker-php-ext-install intl pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl gd

# Instalacija Composera
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Radni direktorijum
WORKDIR /var/www

# Kopiranje Laravel fajlova
COPY . .

# Kopiranje .env produkcionog fajla
COPY .env.production .env

# OBRIŠI default nginx konfiguraciju (da ne preuzme control)
RUN rm /etc/nginx/conf.d/default.conf

# Kopiranje naše nginx konfiguracije
COPY nginx/default.conf /etc/nginx/conf.d/default.conf

# Omogući logove da se vide u Docker logovima
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

# Laravel setup
RUN composer install --optimize-autoloader --no-dev \
 && php artisan config:clear \
 && php artisan cache:clear \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan storage:link \
 && php artisan migrate --force \
 && php artisan db:seed --force \
 && php artisan filament:assets --no-interaction

# Kopiranje supervisor konfiguracije
COPY supervisord.conf /etc/supervisord.conf

# Izloži port 80
EXPOSE 80

# Startuj nginx i php-fpm preko supervisora
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
