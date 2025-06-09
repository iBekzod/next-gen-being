FROM php:8.4-fpm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    cron \
    supervisor \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libsqlite3-dev \
    libmariadb-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        intl \
        bcmath \
        opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Create matching user
RUN groupadd -g ${GID} appgroup && \
    useradd -u ${UID} -g appgroup -m appuser && \
    chown -R appuser:appgroup /var/www/html

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY ./docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

CMD ["php-fpm"]
