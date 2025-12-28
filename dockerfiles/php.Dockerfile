FROM php:8.4-fpm-alpine
WORKDIR /var/www/laravel

# Update package index and install system dependencies including MySQL client
# Note: If you encounter DNS errors, check Docker's DNS settings or network configuration
RUN apk update && \
    apk add --no-cache \
    mysql-client \
    bash \
    curl \
    git \
    zip \
    unzip \
    nodejs \
    npm \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql