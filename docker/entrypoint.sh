#!/bin/sh
set -e

# Buat file database sqlite jika belum ada
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
    chmod 664 /var/www/html/database/database.sqlite
fi

# Pastikan perizinan folder storage dan cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Jalankan migrasi dan seeder awal
php artisan migrate --force --seed

# Hubungkan symbolic link file upload
php artisan storage:link

# Optimasi cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan PHP-FPM di background dan Nginx di foreground
php-fpm -D
nginx -g "daemon off;"
