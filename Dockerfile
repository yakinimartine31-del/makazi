FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zip \
    supervisor \
    --no-install-recommends

# Install PHP extensions required by Laravel
# Note: ctype, json, fileinfo are built-in to the official PHP Docker image
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath opcache && \
    docker-php-ext-configure gd --with-jpeg --with-freetype && \
    docker-php-ext-install gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Bound individual media uploads and allow up to ten 2 MB files per request.
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/99-uploads.ini

# Set working directory
WORKDIR /var/www/html

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
