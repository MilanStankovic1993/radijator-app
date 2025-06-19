FROM php:8.3-fpm

# Instalacija sistemskih paketa uključujući nginx, supervisor i dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libpq-dev default-mysql-client libicu-dev \
    postgresql-client

# Instalacija Node.js 18.x za Vite/Filament build
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
 && apt-get install -y nodejs

# Instalacija PHP ekstenzija
RUN docker-php-ext-install intl pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl gd

# Instalacija Composera
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Radni direktorijum
WORKDIR /var/www

# Kopiranje Laravel fajlova
COPY . .

# Postavi vlasništvo i dozvole nad storage i cache folderima da Laravel može pisati
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
 && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Kopiranje .env produkcionog fajla
COPY .env.production .env

# OBRIŠI default nginx konfiguraciju ako postoji
RUN [ -f /etc/nginx/conf.d/default.conf ] && rm /etc/nginx/conf.d/default.conf || true

# Kopiranje naše nginx konfiguracije
COPY nginx/default.conf /etc/nginx/conf.d/default.conf
COPY nginx/nginx.conf /etc/nginx/nginx.conf

# Omogući logove da se vide u Docker logovima
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

# Laravel setup
RUN composer install --optimize-autoloader --no-dev
RUN npm install && npm run build

# Laravel cache & migracije
RUN php artisan clear-compiled && composer dump-autoload \
 && php artisan config:clear \
 && php artisan cache:clear \
 && php artisan config:cache \
 && php artisan route:clear && php artisan route:cache \
 && php artisan view:cache \
 && php artisan storage:link || true \
 && php artisan migrate:fresh --force \
 && php artisan db:seed --force \
 && php artisan livewire:publish --assets \
 && rm -rf public/build && php artisan filament:assets --no-interaction

# Kopiranje supervisor konfiguracije
COPY supervisord.conf /etc/supervisord.conf

# Izloži port 80
EXPOSE 80

# Startuj nginx i php-fpm preko supervisora
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
