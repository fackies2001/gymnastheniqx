FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libgd-dev libzip-dev libpng-dev libjpeg-dev \
    nodejs npm curl unzip git \
    && docker-php-ext-install gd pdo pdo_mysql zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-interaction
RUN npm install && npm run build

EXPOSE 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]