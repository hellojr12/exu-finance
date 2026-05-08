FROM php:8.3-cli

# Install system dependencies + all required PHP extensions
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring tokenizer xml ctype bcmath \
        gd zip intl exif pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set storage permissions + fix line endings on start script
RUN chmod -R 775 storage bootstrap/cache \
    && apt-get install -y dos2unix \
    && dos2unix start.sh \
    && chmod +x start.sh

EXPOSE 8080

CMD ["/bin/bash", "start.sh"]
