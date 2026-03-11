# Stage 1: Composer dependencies
FROM composer:2.8 AS composer-stage
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# Stage 2: Node.js assets
FROM node:20-alpine AS node-stage
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --omit=dev 2>/dev/null || npm install
COPY . .
COPY --from=composer-stage /app/vendor ./vendor
RUN npm run build

# Stage 3: Production PHP-FPM image
FROM php:8.4-fpm AS production

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP-FPM & OPcache configuration
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.memory_consumption=192'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
    echo 'upload_max_filesize=100M'; \
    echo 'post_max_size=100M'; \
    echo 'max_execution_time=300'; \
    echo 'memory_limit=256M'; \
} > /usr/local/etc/php/conf.d/laravel.ini

WORKDIR /var/www/html

# Copy application code
COPY --chown=www-data:www-data . .
COPY --from=composer-stage --chown=www-data:www-data /app/vendor ./vendor
COPY --from=node-stage --chown=www-data:www-data /app/public/build ./public/build

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Run as www-data
USER www-data

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s \
    CMD php-fpm -t || exit 1
