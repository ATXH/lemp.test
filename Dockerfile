FROM php:8.2-fpm

# Install PostgreSQL dev libraries and PHP extensions
# libpq-dev is required for the pgsql extensions
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Set working directory
WORKDIR /var/www/html

# Copy project files into the container
COPY . .

# Expose PHP-FPM port
EXPOSE 9000

CMD ["php-fpm"]