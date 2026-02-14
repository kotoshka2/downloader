FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    curl \
    ca-certificates \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js (LTS)
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs \
    && node -v \
    && npm -v

# Install yt-dlp
RUN curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory to default Apache document root
WORKDIR /var/www/html

# Copy application source
COPY . /var/www/html/

# Set up permissions for writable directories
# Set up permissions for writable directories
RUN mkdir -p downloads api/logs \
    && chown -R www-data:www-data downloads api/logs \
    && chmod -R 755 downloads api/logs

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]

# Expose port 80
EXPOSE 80

