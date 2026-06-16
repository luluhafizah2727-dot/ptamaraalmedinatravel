# Setup PT Amara Al Medina Travel untuk XAMPP

Panduan ini fokus untuk menjalankan release prebuild di XAMPP Windows dengan PHP 8.2.12.

## Instal dari Release ZIP

Gunakan file asset release, bukan source archive GitHub:

```text
ptamaraalmedinatravel-v1.0.0.zip
```

Release ZIP sudah berisi:

- `vendor/` dari `composer install --no-dev`
- `public/build/` dari `npm run build`
- root `index.php` dan `.htaccess` untuk akses langsung dari `htdocs\lulu`
- `.env.example` dengan auto-setup XAMPP
- struktur `storage/` dan `bootstrap/cache/`

Release ZIP tidak menyertakan `.env`, `node_modules/`, `tests/`, `.git/`, `.github/`, log, cache runtime, atau file lokal sensitif.

## Prasyarat

- XAMPP dengan PHP 8.2.12
- Apache dan MySQL/MariaDB dari XAMPP
- Apache `mod_rewrite` aktif

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

Composer dan Node.js tidak wajib untuk release ZIP karena dependency runtime dan asset frontend sudah ikut di dalam ZIP.

## Langkah Instalasi Cepat

1. Download `ptamaraalmedinatravel-v1.0.0.zip` dari GitHub Releases.

2. Extract isi ZIP ke folder:

   ```text
   C:\xampp\htdocs\lulu
   ```

3. Start `Apache` dan `MySQL` dari XAMPP Control Panel.

4. Buka:

   ```text
   Website: http://localhost/lulu/
   Admin:   http://localhost/lulu/admin/login
   ```

Pada request pertama, aplikasi otomatis:

- membuat `.env` dari `.env.example` jika belum ada,
- mengisi `APP_KEY`,
- mengatur `APP_URL=http://localhost/lulu`,
- mengatur `FILESYSTEM_PUBLIC_URL=/lulu/storage`,
- membuat database MySQL jika belum ada,
- menjalankan migration dan seeder sekali,
- melayani upload `/storage/...` tanpa wajib `php artisan storage:link`.

Default database memakai konfigurasi XAMPP:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ptamaraalmedinatravel
DB_USERNAME=root
DB_PASSWORD=
```

Untuk menghindari error session sebelum migration selesai, prebuild memakai:

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Data utama aplikasi tetap tersimpan di database MySQL.

Jika username/password MySQL berbeda, edit file `.env` setelah extract lalu refresh halaman.

## Admin Awal

Akun admin dibuat dari nilai di `.env`:

```env
ADMIN_INITIAL_NAME=Admin
ADMIN_INITIAL_EMAIL=admin@example.com
ADMIN_INITIAL_PASSWORD=change-this-password-before-seeding
```

Ubah email/password di `.env` sebelum membuka halaman pertama jika ingin credential berbeda. Jika sudah terlanjur seed, edit `.env`, hapus marker `storage/app/.xampp-installed.json`, lalu refresh halaman atau jalankan manual fallback.

## Install dari Source Code

Gunakan opsi ini jika clone repository, bukan release ZIP.

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
```

Setelah itu buka aplikasi dari XAMPP. Jika auto-setup dinonaktifkan, gunakan manual fallback.

## Virtual Host Opsional

Mode prebuild tidak membutuhkan virtual host. Jika ingin memakai domain lokal seperti `amara.local`, arahkan document root Apache ke folder `public`.

File:

```text
C:\xampp\apache\conf\extra\httpd-vhosts.conf
```

Tambahkan:

```apache
<VirtualHost *:80>
    DocumentRoot "C:\xampp\htdocs\lulu\public"
    ServerName amara.local

    <Directory "C:\xampp\htdocs\lulu\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Tambahkan ke hosts file:

```text
127.0.0.1  amara.local
```

Lalu restart Apache dan akses `http://amara.local`.

## Checklist Isi Release ZIP

```text
lulu/
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
|-- .htaccess
|-- index.php
|-- artisan
|-- composer.json
|-- composer.lock
```

Tidak perlu ada `node_modules/` pada release ZIP.

## Troubleshooting

### Halaman setup otomatis muncul

Ikuti pesan di halaman tersebut. Penyebab paling umum:

- MySQL di XAMPP belum running.
- Credential `DB_USERNAME` / `DB_PASSWORD` di `.env` tidak cocok.
- Folder aplikasi tidak writable.
- Ekstensi `pdo_mysql` belum aktif.

### `Class not found` atau `vendor/autoload.php` tidak ditemukan

File yang dipakai bukan release prebuild, atau ZIP tidak lengkap. Gunakan asset release `ptamaraalmedinatravel-v1.0.0.zip`.

### CSS/JS tidak tampil

Pastikan `public/build/manifest.json` ada. Jika setup dari source:

```bat
npm ci
npm run build
```

### 404 pada `http://localhost/lulu/`

- Pastikan folder extract adalah `C:\xampp\htdocs\lulu`.
- Pastikan root `.htaccess` ada.
- Aktifkan `mod_rewrite` di Apache XAMPP.
- Restart Apache setelah mengubah konfigurasi.

### Admin tidak bisa login

Pastikan `ADMIN_INITIAL_EMAIL` dan `ADMIN_INITIAL_PASSWORD` di `.env` sesuai. Jika ingin seed ulang:

```bat
del storage\app\.xampp-installed.json
php artisan db:seed --force
```

### Manual fallback

Jalankan dari folder aplikasi jika auto-setup tetap gagal:

```bat
php artisan key:generate
php artisan migrate --seed --force
php artisan optimize:clear
```
