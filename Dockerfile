FROM php:8.3-cli

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install system dependencies + all required PHP extensions
RUN apt-get update && apt-get install -y \
    git curl zip unzip dos2unix \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring tokenizer xml ctype bcmath \
        gd zip intl exif pcntl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Verify gd is installed
RUN php -m | grep gd

# Install Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies (ignore platform reqs since extensions are verified above)
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --ignore-platform-req=ext-gd \
    --ignore-platform-req=php-64bit

# Set storage permissions and fix line endings
RUN chmod -R 775 storage bootstrap/cache \
    && dos2unix start.sh \
    && chmod +x start.sh

EXPOSE 8080

CMD ["/bin/bash", "start.sh"]
