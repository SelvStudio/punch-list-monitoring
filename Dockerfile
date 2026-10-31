FROM php:8.2-fpm-alpine

# Install Nginx, Git, SQLite, dan dependensi sistem
RUN apk update && apk add --no-cache \
    nginx \
    git \
    curl \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev

# Install ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_sqlite \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Salin seluruh source code proyek
COPY . .

# Install dependencies composer untuk production
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Konfigurasi Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Setup script entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Port web yang diekspos
EXPOSE 80

# Jalankan entrypoint script
CMD ["/usr/local/bin/entrypoint.sh"]
