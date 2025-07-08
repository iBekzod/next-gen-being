FROM php:8.4-fpm-alpine

ARG UID=1000
ARG GID=1000

# Install system dependencies
RUN apk add --no-cache \
    curl \
    zip \
    unzip \
    git \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    icu-dev \
    supervisor \
    nodejs \
    npm \
    bash \
    imagemagick \
    imagemagick-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        zip \
        exif \
        pcntl \
        intl \
        bcmath \
        opcache \
        gd

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick \
    && apk del .build-deps

# Create user
RUN addgroup -g ${GID} appgroup && \
    adduser -u ${UID} -G appgroup -s /bin/sh -D appuser

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock* package.json package-lock.json* ./

# Install PHP dependencies without running scripts or generating autoloader yet
RUN COMPOSER_PROCESS_TIMEOUT=600 composer install \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --no-progress

# Install Node.js dependencies (including dev dependencies for build)
RUN npm install

# Copy application code
COPY . .

# Regenerate optimized autoloader now that all files are present
RUN composer dump-autoload --optimize

# Set up Tailwind CSS (moved from entrypoint)
RUN npx tailwindcss init -p || echo "⚠️ Tailwind init failed - using existing config"

# Build frontend assets
RUN npm run build

# Copy configuration files
COPY ./docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY . .
# Set permissions
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R appuser:appgroup /var/www/html && \
    mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    chown -R appuser:appgroup storage bootstrap/cache

# Clean up npm dev dependencies to reduce image size
RUN npm prune --production

# Switch to non-root user
# USER appuser

EXPOSE 9000

# Use entrypoint script to handle Laravel setup
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
