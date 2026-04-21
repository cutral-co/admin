FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip

COPY . /var/www

RUN cp .env.example .env || true

WORKDIR /var/www
RUN chown -R www-data:www-data /var/www

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --prefer-dist

RUN php artisan key:generate --force
RUN php artisan jwt:secret --force || true

EXPOSE 9000

CMD ["php-fpm"]
