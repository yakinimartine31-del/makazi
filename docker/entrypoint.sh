#!/bin/bash
set -e

echo "=== Laravel Entrypoint ==="

# Set storage and bootstrap permissions
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Wait for MySQL to be ready
echo "Waiting for MySQL container..."
until bash -c "echo > /dev/tcp/mysql/3306" 2>/dev/null; do
    echo "  MySQL not ready yet, retrying in 3s..."
    sleep 3
done
echo "MySQL is ready!"

# Install Composer dependencies if vendor directory is missing
if [ ! -d /var/www/html/vendor ] || [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --working-dir=/var/www/html
else
    echo "Composer dependencies already present."
fi

# Run database migrations
echo "Running migrations..."
php /var/www/html/artisan migrate --force

echo "=== Laravel setup complete. Starting PHP-FPM... ==="
exec "$@"
