# Dockerfile
FROM php:8.2-cli

# Install necessary PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy your app code
COPY . .

# Install Symfony dependencies
RUN composer install

# Expose port if needed (optional for internal use)
EXPOSE 8000

# Default command (optional)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
