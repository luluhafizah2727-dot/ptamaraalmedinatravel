# Setup PT Amara Al Medina Travel untuk XAMPP

Panduan lengkap untuk menjalankan aplikasi di XAMPP tanpa perlu konfigurasi kompleks.

## Prasyarat

- **XAMPP** (PHP 8.3, MySQL/MariaDB, Apache)
- **Composer** (untuk install PHP dependencies)
- **Node.js 18+** dan **npm** (untuk build frontend)

Verifikasi:
```bash
php -v           # Harus PHP 8.3+
composer --version
node -v          # Harus Node 18+
npm -v
mysql --version  # Atau MariaDB
```

## Opsi 1: Setup dari Release ZIP (Recommended)

Release ZIP sudah berisi semua dependencies, jadi Anda hanya perlu setup database dan .env.

### Langkah-Langkah

1. **Download Release ZIP**
   - Buka https://github.com/luluhafizah2727-dot/ptamaraalmedinatravel/releases
   - Download file `ptamaraalmedinatravel-v1.0.0.zip` (atau versi terbaru)

2. **Extract ke XAMPP**
   ```
   C:\xampp\htdocs\ptamaraalmedinatravel
   ```

3. **Setup File `.env`**
   ```bash
   copy .env.example .env
   ```
   Edit `.env`:
   ```env
   APP_NAME="PT Amara Al Medina Travel"
   APP_ENV=local
   APP_KEY=            # Akan di-generate di step 5
   APP_DEBUG=true
   APP_URL=http://localhost/ptamaraalmedinatravel

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ptamaraalmedinatravel
   DB_USERNAME=root
   DB_PASSWORD=        # Kosong jika default XAMPP
   ```

4. **Buat Database**
   - Buka phpMyAdmin: http://localhost/phpmyadmin
   - Atau jalankan di Command Prompt:
   ```bash
   mysql -u root -p -e "CREATE DATABASE ptamaraalmedinatravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

5. **Generate App Key & Migrate Database**
   ```bash
   cd C:\xampp\htdocs\ptamaraalmedinatravel
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   ```

6. **Akses Aplikasi**
   - Website: http://localhost/ptamaraalmedinatravel
   - Admin Panel: http://localhost/ptamaraalmedinatravel/admin/login
   - Credentials: Lihat di `.env` (`ADMIN_INITIAL_EMAIL` dan `ADMIN_INITIAL_PASSWORD`)

---

## Opsi 2: Setup dari Source Code

Jika download dari source code (bukan release ZIP), ikuti langkah-langkah ini.

### Langkah-Langkah

1. **Download/Clone Repository**
   ```bash
   git clone https://github.com/luluhafizah2727-dot/ptamaraalmedinatravel.git
   cd ptamaraalmedinatravel
   ```
   Atau download ZIP dari GitHub dan extract ke:
   ```
   C:\xampp\htdocs\ptamaraalmedinatravel
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install --no-dev --prefer-dist --optimize-autoloader
   ```
   Proses ini akan membuat folder `vendor/`.

3. **Install Frontend Dependencies**
   ```bash
   npm install
   ```

4. **Build Frontend Assets**
   ```bash
   npm run build
   ```
   Proses ini akan membuat folder `public/build/`.

5. **Setup .env**
   ```bash
   copy .env.example .env
   ```
   Edit file dengan konfigurasi database (lihat Opsi 1 step 3).

6. **Generate Key dan Database**
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   ```

7. **Akses Aplikasi**
   - Sama seperti Opsi 1

---

## XAMPP Virtual Host Setup (Opsional)

Jika ingin menggunakan domain lokal (misalnya `amara.local`):

### Apache Configuration

**File: `C:\xampp\apache\conf\extra\httpd-vhosts.conf`**

Tambahkan di akhir file:
```apache
<VirtualHost *:80>
    DocumentRoot "C:\xampp\htdocs\ptamaraalmedinatravel\public"
    ServerName amara.local
    ErrorLog "C:\xampp\apache\logs\amara-error.log"
    CustomLog "C:\xampp\apache\logs\amara-access.log" common
    
    <Directory "C:\xampp\htdocs\ptamaraalmedinatravel\public">
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^ index.php [L]
        </IfModule>
    </Directory>
</VirtualHost>
```

### Hosts File

**File: `C:\Windows\System32\drivers\etc\hosts`**

Tambahkan:
```
127.0.0.1  amara.local
```

### Restart Apache

1. Buka XAMPP Control Panel
2. Stop Apache
3. Start Apache lagi

Sekarang akses: http://amara.local

---

## Folder Structure Checklist

Setelah setup, pastikan struktur folder berikut ada:

```
ptamaraalmedinatravel/
├── vendor/              ✓ (PHP dependencies)
├── node_modules/        ✓ (Frontend dependencies) 
├── public/
│   ├── build/           ✓ (Built CSS/JS)
│   ├── storage/         ✓ (Symlink ke storage/app/public)
│   └── index.php        ✓
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── .env                 ✓ (Configured)
└── app/
    ├── Models/
    ├── Http/
    └── Filament/        ✓ (Admin panel)
```

---

## Troubleshooting

### ❌ "Class not found" atau Autoload errors
**Solusi**: `vendor/` belum di-install
```bash
composer install --no-dev
composer dump-autoload -o
```

### ❌ CSS/JS tidak muncul (halaman terlihat jelek)
**Solusi**: `public/build/` belum di-build
```bash
npm run build
php artisan optimize
```

### ❌ "404 Not Found" atau Laravel tidak berjalan
**Solusi**: Apache mod_rewrite belum aktif atau .htaccess tidak ada
- Pastikan `public/.htaccess` ada
- Enable mod_rewrite di XAMPP:
  1. Buka `C:\xampp\apache\conf\httpd.conf`
  2. Cari dan uncomment: `LoadModule rewrite_module modules/mod_rewrite.so`
  3. Restart Apache

### ❌ Database connection error
**Solusi**: MySQL tidak running atau kredensial salah
```bash
# Pastikan MySQL running di XAMPP Control Panel
mysql -u root -p
# Jika sukses, test .env credentials:
php artisan tinker
# Ketik: DB::connection()->getPdo();
# Harus return object jika OK
```

### ❌ "Permission denied" atau upload tidak bekerja
**Solusi**: Folder permissions
```bash
# Run Command Prompt as Administrator:
icacls "C:\xampp\htdocs\ptamaraalmedinatravel\storage" /grant Everyone:F /T
icacls "C:\xampp\htdocs\ptamaraalmedinatravel\public\storage" /grant Everyone:F /T
```

### ❌ Admin login tidak bekerja
**Solusi**: Check seeder atau create user manual
```bash
php artisan tinker
# User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'), 'is_admin' => true]);
exit
```

---

## Development vs Production

### Development (Apa yang sekarang Anda jalankan)
- `APP_DEBUG=true` → errors ditampilkan
- `npm run dev` → watch untuk changes
- Slower performance

### Production (Untuk deploy ke server)
- `APP_DEBUG=false`
- `npm run build` (sekali saja)
- `php artisan config:cache`
- `php artisan route:cache`
- Optimize untuk speed

---

## File Penting

| File | Purpose |
|------|---------|
| `.env.example` | Template konfigurasi |
| `.env` | Konfigurasi aktual (jangan commit) |
| `.htaccess` | Apache rewrite rules |
| `composer.json` | PHP dependencies list |
| `package.json` | Node dependencies list |
| `vite.config.js` | Frontend build config |
| `artisan` | Laravel CLI tool |

---

## Quick Commands Reference

```bash
# Development
php artisan serve                    # Run built-in server (optional)
npm run dev                          # Watch frontend changes
php artisan tinker                   # Interactive shell

# Database
php artisan migrate                  # Run migrations
php artisan migrate:rollback         # Undo migrations
php artisan migrate --force          # Force (use for XAMPP)
php artisan seed:run                 # Seed data

# Cache & Optimization
php artisan cache:clear
php artisan config:cache
php artisan optimize
php artisan filament:optimize        # Admin panel cache

# Storage & Assets
php artisan storage:link             # Create public symlink
npm run build                        # Build CSS/JS

# Testing
php artisan test                     # Run tests
php artisan test --parallel          # Run tests in parallel
```

---

## Support & Issues

Jika ada masalah:
1. Check file `.env` configuration
2. Check XAMPP services (Apache, MySQL) running
3. Check `storage/logs/laravel.log` untuk error details
4. Verify folder permissions
5. Clear cache: `php artisan cache:clear`

Lebih detail: Lihat [README.md](README.md) untuk dokumentasi lengkap.
