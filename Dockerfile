
FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip

# Enable Apache modules (including rate limiting and timeout protection)
RUN a2enmod rewrite headers expires deflate ratelimit reqtimeout

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# PHPMailer already included in libs/ directory, no need to install via Composer

# Create upload directories and set permissions
RUN mkdir -p /var/www/html/uploads/profile_pics \
    && mkdir -p /var/www/html/uploads/backgrounds \
    && mkdir -p /var/www/html/uploads/folder_pics \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

# Apache configuration (copy if exists, otherwise use default)
RUN if [ -f apache-config.conf ]; then \
        cp apache-config.conf /etc/apache2/sites-available/000-default.conf; \
    fi

# Set ServerName globally to suppress warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

