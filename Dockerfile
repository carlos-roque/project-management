# Use the original image as the base
FROM eloufirhatim/helper:latest

# Set working directory
WORKDIR /var/www

# Copy your customized files from your repository
# This assumes your repository structure matches the container's
COPY resources/views/filament/pages/board.blade.php /var/www/resources/views/filament/pages/board.blade.php

# Clear view cache to ensure changes take effect
RUN php artisan view:clear

# Fix permissions
RUN chown -R www-data:www-data /var/www/resources/views
