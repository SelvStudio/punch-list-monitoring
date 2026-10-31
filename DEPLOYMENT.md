# Panduan Deployment Aplikasi Punch List Monitoring System

Dokumen ini menjelaskan tata cara deployment aplikasi web **Punch List Monitoring System** berbasis Laravel, PHP 8.2+, SQLite, dan AdminLTE v3 ke lingkungan produksi atau server daring.

---

## 1. Karakteristik Arsitektur & Persyaratan Server

Aplikasi ini menggunakan database **SQLite** untuk kesederhanaan, portabilitas, dan performa tinggi tanpa perlu server database terpisah. Namun, saat melakukan deployment publik, wajib diperhatikan:

- **Filesystem Persisten**: Database SQLite disimpan sebagai file fisik (`database/database.sqlite`). Jangan gunakan platform cloud dengan filesystem ephemeral/sementara (seperti Heroku tier standar) tanpa persistent storage/volume disk.
- **Direktori Root Web**: Document root server web (Nginx/Apache/Caddy) **harus** diarahkan ke folder `/public`, bukan ke root direktori proyek. Hal ini penting untuk mencegah file `database.sqlite` atau file `.env` dapat diunduh publik.
- **Ekstensi PHP Wajib**: `php-sqlite3`, `php-pdo_sqlite`, `php-mbstring`, `php-xml`, `php-bcmath`, `php-curl`, `php-fileinfo`, `php-zip`.

---

## 2. Rekomendasi Layanan Hosting yang Mendukung SQLite Persisten

| Layanan | Jenis | Dukungan SQLite Persisten | Rekomendasi Penggunaan |
| :--- | :--- | :--- | :--- |
| **VPS (DigitalOcean / Linode / IDCloudHost / Biznet)** | Virtual Private Server | 100% Persisten | **Sangat Direkomendasikan** untuk kontrol penuh dan biaya hemat. |
| **Render.com** | Web Service (Docker) | Memerlukan *Persistent Disk* | Cocok untuk deployment cloud modern (Mount persistent disk ke `/var/www/database` & `/var/www/storage`). |
| **Fly.io** | Container Platform | Memerlukan *Fly Volume* | Sangat baik untuk deployment global menggunakan volume persisten. |
| **cPanel Shared Hosting** | Shared Hosting | Persisten pada home directory | Pastikan Document Root cPanel dapat diarahkan ke direktori `public_html/public`. |

---

## 3. Langkah-Langkah Deployment ke Server VPS (Ubuntu 22.04 / 24.04 LTS)

### Langkah 3.1 — Persiapan Server & PHP

```bash
# Update repository sistem
sudo apt update && sudo apt upgrade -y

# Install Nginx, Git, Composer, dan PHP 8.2 beserta modulnya
sudo apt install -y nginx git composer php8.2-fpm php8.2-cli php8.2-sqlite3 \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl
```

### Langkah 3.2 — Clone Repository & Konfigurasi Izin Direktori

```bash
# Masuk ke direktori web
cd /var/www

# Clone repository dari GitHub
git clone https://github.com/USERNAME_ANDA/punch-list-monitoring.git punch-list
cd punch-list

# Install dependency produksi (tanpa dev package)
composer install --no-dev --optimize-autoloader

# Atur kepemilikan direktori ke user web server (www-data)
sudo chown -R www-data:www-data /var/www/punch-list
sudo chmod -R 775 /var/www/punch-list/storage /var/www/punch-list/bootstrap/cache /var/www/punch-list/database
```

### Langkah 3.3 — Konfigurasi Environment (`.env`)

```bash
# Salin file konfigurasi
cp .env.example .env

# Buat application key
php artisan key:generate
```

Buka file `.env` dengan editor (`nano .env`) dan sesuaikan parameter produksi:

```ini
APP_NAME="Punch List Monitoring System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://punchlist.domainanda.com
APP_TIMEZONE="Asia/Jakarta"

DB_CONNECTION=sqlite
# DB_DATABASE otomatis mengarah ke database/database.sqlite
```

### Langkah 3.4 — Migrasi Database, Seeder, dan Storage Link

```bash
# Buat file database jika belum ada
touch database/database.sqlite
sudo chown www-data:www-data database/database.sqlite
sudo chmod 664 database/database.sqlite

# Jalankan migrasi dan masukkan data capstone
php artisan migrate --force --seed

# Buat symbolic link untuk file upload publik
php artisan storage:link

# Optimasi cache Laravel untuk performa produksi maksimal
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Langkah 3.5 — Konfigurasi Nginx Web Server

Buat file konfigurasi Nginx: `sudo nano /etc/nginx/sites-available/punch-list`

```nginx
server {
    listen 80;
    server_name punchlist.domainanda.com;
    root /var/www/punch-list/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Blokir akses ke file tersembunyi (.env, .git, sqlite)
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan konfigurasi Nginx dan pasang SSL/HTTPS:

```bash
sudo ln -s /etc/nginx/sites-available/punch-list /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Pasang SSL gratis menggunakan Let's Encrypt Certbot
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d punchlist.domainanda.com
```

---

## 4. Konfigurasi Cron Scheduler Otomatis

Agar sistem secara otomatis memeriksa tenggat waktu dan mengirimkan pengingat 3 hari sebelum tenggat atau keterlambatan setiap hari pukul 07:00 WIB:

Buka crontab server:
```bash
sudo crontab -u www-data -e
```

Tambahkan satu baris berikut:
```cron
* * * * * cd /var/www/punch-list && php artisan schedule:run >> /dev/null 2>&1
```

*(Catatan: Anda juga dapat menjalankan perintah manual kapan saja melalui menu **Pengaturan Sistem** di dashboard atau menjalankan `php artisan punchlist:check-deadlines`).*

---

## 5. Prosedur Pencadangan (Backup) Database SQLite

Karena seluruh basis data SQLite tersimpan dalam satu file, pencadangan sangat cepat dan aman dilakukan:

```bash
# Perintah backup aman tanpa menghentikan sistem (menggunakan SQLite online backup)
sqlite3 /var/www/punch-list/database/database.sqlite ".backup '/var/backups/punchlist_$(date +\%Y\%m\%d_\%H\%M\%S).sqlite'"
```
