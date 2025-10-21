# =====================================================
# STAGE 1: Build Frontend (npm)
# =====================================================
FROM node:20 AS frontend-builder

WORKDIR /app

# Copy package.json dan package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy seluruh kode frontend
COPY . .

# Build assets frontend
RUN npm run build

# =====================================================
# STAGE 2: Setup PHP (Laravel Backend)
# =====================================================
FROM php:8.2-fpm AS backend

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy backend files
COPY . .

# Copy hasil build frontend dari tahap sebelumnya
COPY --from=frontend-builder /app/public/build ./public/build

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Laravel optimizations
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan storage:link || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 8000

# Jalankan Laravel menggunakan built-in server
CMD php artisan serve --host=0.0.0.0 --port=8000
