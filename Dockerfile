FROM php:8.3-fpm-alpine

# Install system build dependencies and runtime packages
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng \
    libpng-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    freetype \
    freetype-dev \
    libzip \
    libzip-dev \
    icu \
    icu-dev \
    libxml2 \
    libxml2-dev \
    curl-dev \
    oniguruma \
    oniguruma-dev \
    mysql-client \
    mariadb-client

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        mbstring \
        zip \
        gd \
        bcmath \
        xml \
        curl \
        intl \
        opcache

# Copy Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Custom PHP settings for production & upload limits
RUN echo "upload_max_filesize = 25M" > /usr/local/etc/php/conf.d/sipades-custom.ini \
    && echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/sipades-custom.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/sipades-custom.ini \
    && echo "max_execution_time = 120" >> /usr/local/etc/php/conf.d/sipades-custom.ini

# Set working directory
WORKDIR /var/www/html

# Create Laravel storage & cache directory hierarchy with proper permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/app/private/documents \
             /var/www/html/storage/app/public \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy application source
COPY --chown=www-data:www-data . /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
