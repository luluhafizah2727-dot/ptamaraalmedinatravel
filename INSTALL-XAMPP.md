# Setup PT Amara Al Medina Travel untuk XAMPP

Panduan ini fokus untuk menjalankan aplikasi Laravel di XAMPP pada Windows.

## Opsi 1: Instal dari Release ZIP

Gunakan opsi ini jika Anda mengunduh file prebuild seperti:

```text
ptamaraalmedinatravel-v1.0.1.zip
```

Release ZIP harus sudah berisi:

- `vendor/` dari `composer install --no-dev`
- `public/build/` dari `npm run build`
- `.env.example` sebagai template konfigurasi lokal
- struktur `storage/` dan `bootstrap/cache/` yang diperlukan Laravel

Release ZIP tidak menyertakan `.env`, `node_modules/`, `tests/`, `.git/`, `.github/`, log, cache runtime, atau file lokal sensitif.

## Prasyarat untuk Release ZIP

- XAMPP dengan PHP 8.3+
- Apache dan MySQL/MariaDB dari XAMPP
- PHP CLI dapat dijalankan dari terminal

Ekstensi PHP yang perlu aktif:

- `ctype`
- `curl`
- `dom` / `xml`
- `fileinfo`
- `filter`
- `iconv`
- `intl`
- `mbstring`
- `openssl`
- `pdo_mysql`
- `session`
- `tokenizer`
- `xmlreader`
- `zip`

Untuk instalasi dari release ZIP, Composer dan Node.js tidak wajib karena dependency runtime dan asset frontend sudah ikut di dalam ZIP.

## Langkah Instalasi Release ZIP

1. Download ZIP dari GitHub Releases.

2. Extract ke folder XAMPP:

   ```text
   C:\xampp\htdocs\ptamaraalmedinatravel
   ```

3. Buka terminal di folder aplikasi:

   ```bat
   cd C:\xampp\htdocs\ptamaraalmedinatravel
   ```

4. Buat file `.env`:

   ```bat
   copy .env.example .env
   ```

5. Edit `.env` untuk XAMPP:

   ```env
   APP_NAME="PT Amara Al Medina Travel"
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://localhost/ptamaraalmedinatravel

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ptamaraalmedinatravel
   DB_USERNAME=root
   DB_PASSWORD=

   ADMIN_INITIAL_NAME=Admin
   ADMIN_INITIAL_EMAIL=admin@example.com
   ADMIN_INITIAL_PASSWORD=ganti-password-ini
   ```

   Ganti `ADMIN_INITIAL_EMAIL` dan `ADMIN_INITIAL_PASSWORD` sebelum menjalankan seed.

6. Buat database lewat phpMyAdmin atau MySQL:

   ```sql
   CREATE DATABASE ptamaraalmedinatravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

7. Jalankan setup Laravel:

   ```bat
   php artisan key:generate
   php artisan migrate --seed --force
   php artisan storage:link
   php artisan optimize:clear
   ```

8. Akses aplikasi:

   ```text
   Website: http://localhost/ptamaraalmedinatravel
   Admin:   http://localhost/ptamaraalmedinatravel/admin/login
   ```

## Opsi 2: Instal dari Source Code

Gunakan opsi ini jika Anda clone repository atau mengunduh source archive GitHub, bukan release prebuild.

Prasyarat tambahan:

- Composer 2.x
- Node.js 22+ dan npm

Langkah:

```bat
git clone https://github.com/luluhafizah2727-dot/ptamaraalmedinatravel.git
cd ptamaraalmedinatravel
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
copy .env.example .env
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize:clear
```

Pastikan `.env` sudah diedit sebelum `migrate --seed --force`.

## Virtual Host Opsional

Jika ingin memakai domain lokal seperti `amara.local`, arahkan document root Apache ke folder `public`.

File:

```text
C:\xampp\apache\conf\extra\httpd-vhosts.conf
```

Tambahkan:

```apache
<VirtualHost *:80>
    DocumentRoot "C:\xampp\htdocs\ptamaraalmedinatravel\public"
    ServerName amara.local

    <Directory "C:\xampp\htdocs\ptamaraalmedinatravel\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Tambahkan ke hosts file:

```text
127.0.0.1  amara.local
```

Jika memakai virtual host, ubah `.env`:

```env
APP_URL=http://amara.local
```

Lalu restart Apache.

## Checklist Setelah Extract Release ZIP

```text
ptamaraalmedinatravel/
|-- app/
|-- bootstrap/
|   |-- cache/
|-- config/
|-- database/
|-- public/
|   |-- build/
|   |-- index.php
|   |-- .htaccess
|-- resources/
|-- routes/
|-- storage/
|   |-- app/
|   |-- framework/
|   |-- logs/
|-- vendor/
|-- .env.example
|-- artisan
|-- composer.json
|-- composer.lock
```

Tidak perlu ada `node_modules/` pada release ZIP.

## Troubleshooting

### `Class not found` atau `vendor/autoload.php` tidak ditemukan

File yang dipakai bukan release prebuild, atau ZIP tidak lengkap. Gunakan release ZIP yang memiliki `vendor/`.

### CSS/JS tidak tampil

Pastikan `public/build/manifest.json` ada. Jika setup dari source, jalankan:

```bat
npm ci
npm run build
```

### Admin tidak bisa login

Pastikan `ADMIN_INITIAL_EMAIL` dan `ADMIN_INITIAL_PASSWORD` sudah diisi sebelum menjalankan:

```bat
php artisan migrate --seed --force
```

Jika sudah terlanjur seed dengan credential salah, edit `.env`, lalu jalankan:

```bat
php artisan db:seed --force
```

### Upload gambar tidak bisa

Jalankan terminal sebagai Administrator jika `storage:link` gagal:

```bat
php artisan storage:link
```

Pastikan folder berikut writable:

```text
storage/
bootstrap/cache/
```

### 404 pada route Laravel

Aktifkan `mod_rewrite` di Apache XAMPP dan pastikan `public/.htaccess` ada.
