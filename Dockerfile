FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql zip intl gd

# Install Composer 2.8.5
COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get install -y symfony-cli

# Set working directory
WORKDIR /var/www/html

# Copy composer.json and composer.lock
COPY composer.* ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader --no-interaction

# Copy the rest of the application
COPY . .

# Generate autoload files
RUN composer dump-autoload --optimize


EXPOSE 8000

# Set permissions
RUN mkdir -p var && chmod -R 777 var/