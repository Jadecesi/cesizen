FROM php:8.5-rc-fpm-alpine3.21

RUN apt-get update && apt-get install -y unzip git zip libicu-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN composer install --no-dev --optimize-autoloader --no-scripts

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
