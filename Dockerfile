FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    bash \
    npm \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install \
    gd \
    pdo_mysql \
    mbstring \
    intl \
    zip \
    bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY package.json package-lock.json vite.config.js ./
RUN npm install && npm run build

COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data

