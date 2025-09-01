# Use the same base as your main app
FROM php:8.2-fpm-alpine

# Install system dependencies and supervisor
RUN apk add --no-cache \
    bash \
    curl \
    freetype-dev \
    git \
    icu-dev \
    jpeg-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    supervisor \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        xml \
        zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create app user
RUN addgroup -g 1000 appgroup && adduser -u 1000 -G appgroup -s /bin/sh -D appuser

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --no-scripts --no-interaction

# Copy application code
COPY . .

# Regenerate optimized autoloader
RUN composer dump-autoload --optimize

# Set proper ownership
RUN chown -R appuser:appgroup /var/www/html

# Create log directories and set permissions
RUN mkdir -p /var/log \
    && chmod 755 /var/log \
    && touch /var/log/supervisord.log \
    && touch /var/log/laravel-worker.log \
    && touch /var/log/laravel-schedule.log \
    && chown appuser:appgroup /var/log/*.log

# Copy supervisor configuration
COPY ./docker/supervisor/supervisord.conf /etc/supervisord.conf

# Expose port (not really needed for scheduler but good practice)
EXPOSE 9000

# Start supervisor as root (it will run child processes as appuser)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
