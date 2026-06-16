# PT Amara Al Medina Travel

Website profil dan panel admin untuk PT Amara Al Medina Travel. Aplikasi ini merupakan sistem berbasis Laravel dengan panel admin Filament untuk mengelola konten (paket umrah, jadwal, galeri, profil perusahaan, dan kontak).

## Stack

- PHP 8.3
- Laravel 13
- Filament 5 untuk panel admin `/admin`
- MySQL/MariaDB
- Tailwind/Vite untuk asset CSS dan JavaScript

## Requirements

Minimal dan rekomendasi lingkungan untuk menjalankan proyek ini:

- **PHP**: ^8.3 (sesuaikan dengan `composer.json`).
- **Composer**: versi 2.x.
- **Node.js**: direkomendasikan Node 18+ (untuk Vite dan build frontend).
- **npm / pnpm / yarn**: gunakan versi yang sesuai dengan Node (npm 9+ direkomendasikan).
- **Database**: MySQL atau MariaDB (MySQL 5.7+/8.x atau MariaDB setara).

PHP extensions yang umumnya diperlukan:

- `ctype`
- `fileinfo`
- `json`
- `mbstring`
- `openssl`
- `pdo` dan driver database (`pdo_mysql` untuk MySQL/MariaDB)
- `tokenizer`
- `xml`
- `zip` (disarankan untuk installer dan beberapa paket Composer)

Opsional (bergantung fitur yang dipakai):

- `gd` atau `imagick` untuk manipulasi gambar
- `exif` jika aplikasi memproses metadata gambar
- `curl` jika ada panggilan HTTP menggunakan ekstensi ini

Perangkat lunak/system tools:

- `git` (untuk kontrol versi dan workflow deploy)
- `unzip` (dibutuhkan Composer pada beberapa lingkungan)
- PHP-FPM (untuk deployment) atau built-in PHP server untuk pengujian

Contoh cara memeriksa versi dasar:

```bash
php -v
composer --version
node -v
npm -v
mysql --version
```

## Akses Lokal

Untuk pengujian lokal, jalankan aplikasi di server development (mis. Valet, Docker, atau built-in PHP server) atau gunakan vhost lokal yang memetakan host ke `127.0.0.1`.

Contoh pengecekan endpoint lokal menggunakan header Host (ganti `local.test` dengan host lokal Anda jika perlu):

```bash
curl -H 'Host: local.test' http://127.0.0.1/
curl -H 'Host: local.test' http://127.0.0.1/admin/login
```

Untuk akses melalui browser, tambahkan entri pada file `hosts` jika Anda memakai host custom, atau akses langsung pada alamat yang dikonfigurasi oleh environment Anda.

## Struktur Asset

- `public/images/site/` berisi asset aktif: `logo.png` dan `beranda-img.jpg`.
-- `public/images/seed/` berisi gambar fallback dan referensi seed.
-- Upload dari admin disimpan di disk `public` Laravel dan diakses melalui symlink `public/storage`.

## Setup

```bash
composer install
npm install
cp .env.example .env
/usr/bin/php8.3 artisan key:generate
/usr/bin/php8.3 artisan migrate --seed
/usr/bin/php8.3 artisan storage:link
npm run build
/usr/bin/php8.3 artisan optimize
/usr/bin/php8.3 artisan filament:optimize
```

Seeder admin awal membaca environment berikut bila tersedia:

```env
ADMIN_INITIAL_EMAIL=
ADMIN_INITIAL_PASSWORD=
```

Disk upload publik memakai URL relatif `/storage` secara default. Jika perlu override, gunakan `FILESYSTEM_PUBLIC_URL`.

Jangan simpan credential database, password admin, atau secret `.env` di repository.

## Deployment (vhost / server)

- Document root harus diarahkan ke `public/`.
- Aktifkan rewrite Laravel sehingga semua permintaan diarahkan ke `public/index.php`.
- Gunakan PHP-FPM 8.3 (atau versi yang kompatibel).
- Pastikan `storage/` dan `bootstrap/cache/` writable oleh user web server.
- Jalankan `php artisan storage:link` setelah deploy.
- Pastikan asset Livewire dan route publik dapat diakses dari server produksi.

## Admin

Panel admin tersedia di:

```text
/admin/login
```

Akses panel dibatasi untuk user dengan flag admin. Setelah login berhasil, user diarahkan ke dashboard Filament untuk mengelola paket umrah, jadwal, galeri, profil, kontak, dan pengaturan website.

Dashboard juga menampilkan grafik pengunjung 14 hari terakhir. Tracking hanya berjalan untuk route publik dan menyimpan hash IP/user-agent, bukan IP mentah.

## Test dan Build

Jalankan test dan build seperti biasa untuk proyek Laravel + frontend:

```bash
php artisan test
npm run build
php artisan optimize
php artisan filament:optimize
```

Untuk smoke-check lokal, panggil endpoint publik yang relevan menggunakan host atau alamat yang sesuai dengan konfigurasi lokal Anda.

## Release ZIP untuk XAMPP

Jika menggunakan release prebuild dari GitHub, file yang diunduh biasanya berupa ZIP berisi aplikasi siap pakai.

1. Extract file ZIP ke folder XAMPP, misalnya:
   - `C:\xampp\htdocs\ptamaraalmedinatravel`
2. Salin file `.env.example` menjadi `.env`.
3. Sesuaikan konfigurasi database di `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=nama_database`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=`
4. Pastikan database sudah dibuat di phpMyAdmin / MySQL.
5. Jalankan perintah dari folder aplikasi:

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
```

6. Akses aplikasi di browser melalui path XAMPP:
   - `http://localhost/ptamaraalmedinatravel`
   - `http://localhost/ptamaraalmedinatravel/admin/login`

Jika release ZIP sudah berisi folder `public`, pastikan XAMPP DocumentRoot mengarah ke `C:\xampp\htdocs\ptamaraalmedinatravel\public` jika memakai virtual host. Jika tidak, gunakan URL yang sesuai dengan lokasi ekstrak.
