FROM php:8.4-cli

# Install necessary PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    supervisor \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy your app code
COPY . .

# Install Symfony dependencies
RUN composer install

# Copy Supervisor config file into the container
COPY supervisor.conf /etc/supervisor/conf.d/symfony.conf

# Expose port 8000 for Symfony server
EXPOSE 8000

# Start Supervisor in the foreground (this will keep the container running)
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
