# PT Amara Al Medina Travel

Website profil dan panel admin untuk PT Amara Al Medina Travel. Aplikasi ini merupakan sistem berbasis Laravel dengan panel admin Filament untuk mengelola konten (paket umrah, jadwal, galeri, profil perusahaan, dan kontak).

## Stack

- PHP 8.2.12
- Laravel 12
- Filament 5 untuk panel admin `/admin`
- MySQL/MariaDB
- Tailwind/Vite untuk asset CSS dan JavaScript

## Requirements

Minimal dan rekomendasi lingkungan untuk menjalankan proyek ini:

- **PHP**: 8.2.12 atau kompatibel dengan constraint `^8.2` di `composer.json`.
- **Composer**: versi 2.x.
- **Node.js**: Node 22+ untuk build frontend dari source.
- **npm / pnpm / yarn**: gunakan versi yang sesuai dengan Node.
- **Database**: MySQL atau MariaDB (MySQL 5.7+/8.x atau MariaDB setara).

PHP extensions yang umumnya diperlukan:

- `ctype`
- `fileinfo`
- `intl`
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
- `public/images/seed/` berisi gambar fallback dan referensi seed.
- Upload dari admin disimpan di disk `public` Laravel dan diakses melalui symlink `public/storage`.

## Setup dari Source

Untuk development dari source code, install dependency dan build asset:

```bash
composer install
npm ci
npm run build
cp .env.example .env
```

Pada XAMPP prebuild, perintah `key:generate`, `migrate --seed`, `storage:link`, dan `optimize:clear` tidak wajib dijalankan manual karena auto-setup lokal akan menanganinya saat halaman pertama dibuka.

Seeder admin awal membaca environment berikut bila tersedia:

```env
ADMIN_INITIAL_EMAIL=admin@example.com
ADMIN_INITIAL_PASSWORD=ganti-password-ini
```

Jika `ADMIN_INITIAL_EMAIL` atau `ADMIN_INITIAL_PASSWORD` kosong, seeder tidak membuat akun admin awal. Jangan simpan credential database, password admin, atau secret `.env` di repository.

## Deployment (vhost / server)

- Document root harus diarahkan ke `public/`.
- Aktifkan rewrite Laravel sehingga semua permintaan diarahkan ke `public/index.php`.
- Gunakan PHP-FPM/PHP runtime 8.2.12 atau versi PHP 8.2 yang kompatibel.
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
php artisan optimize:clear
```

Untuk smoke-check lokal, panggil endpoint publik yang relevan menggunakan host atau alamat yang sesuai dengan konfigurasi lokal Anda.

## Release ZIP untuk XAMPP

Jika menggunakan release prebuild dari GitHub, file yang diunduh berupa ZIP berisi aplikasi siap pakai dengan:
- **vendor/** - PHP dependencies (Composer packages)
- **public/build/** - Frontend assets (CSS, JS yang sudah di-build oleh Vite)
- **index.php** dan **.htaccess** di root - Compatibility layer agar bisa dibuka dari `htdocs\lulu`
- **storage/** - Direktori untuk aplikasi (sudah ada struktur dan permissions)
- **.env.example** - Template konfigurasi lokal dan auto-setup XAMPP

Release ZIP tidak menyertakan `.env`, `node_modules/`, `tests/`, `.git/`, `.github/`, log, cache runtime, atau file lokal sensitif.

### Instalasi dari Release ZIP

1. **Download ZIP** dari [GitHub Releases](https://github.com/luluhafizah2727-dot/ptamaraalmedinatravel/releases)
2. **Extract** ke folder XAMPP:
   ```
   C:\xampp\htdocs\lulu
   ```
3. **Start Apache dan MySQL** dari XAMPP Control Panel.
4. **Buka aplikasi**:
   - Jika folder extract bernama `lulu`: `http://localhost/lulu/`
   - Jika folder extract bernama `ptamaraalmedinatravel`: `http://localhost/ptamaraalmedinatravel/`
   - Admin: `http://localhost/lulu/admin/login`

Pada request pertama, aplikasi otomatis:
- membuat `.env` dari `.env.example` jika belum ada,
- mengisi `APP_KEY`,
- mendeteksi `APP_URL` dan `FILESYSTEM_PUBLIC_URL` dari URL yang dibuka,
- membuat database MySQL jika belum ada,
- menjalankan migration dan seeder sekali,
- melayani `/storage/...` tanpa wajib membuat symlink `public/storage`.

Default MySQL mengikuti XAMPP: host `127.0.0.1`, user `root`, password kosong. Jika credential berbeda, edit `.env` setelah extract lalu refresh halaman.

### Alternatif: Setup lokal tanpa Release ZIP

Jika ingin setup dari source code tanpa menunggu release:

1. **Clone atau download** repository
2. **Extract** ke `C:\xampp\htdocs\ptamaraalmedinatravel`
3. **Install dependencies**:
   ```bash
   composer install --no-dev --prefer-dist --optimize-autoloader
   npm ci
   npm run build
   ```
4. **Salin dan konfigurasi .env**:
   ```bash
   copy .env.example .env
   ```
5. **Buka dari browser** atau jalankan manual fallback jika auto-setup dinonaktifkan:
   ```bash
   php artisan key:generate
   php artisan migrate --seed --force
   php artisan optimize:clear
   ```

### XAMPP Konfigurasi Virtual Host (Opsional)

Jika ingin menggunakan domain custom (misalnya `ptamara.local`) alih-alih path:

**File: `C:\xampp\apache\conf\extra\httpd-vhosts.conf`**
```apache
<VirtualHost *:80>
    DocumentRoot "C:\xampp\htdocs\ptamaraalmedinatravel\public"
    ServerName ptamara.local
    <Directory "C:\xampp\htdocs\ptamaraalmedinatravel\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**File: `C:\Windows\System32\drivers\etc\hosts`**
```
127.0.0.1  ptamara.local
```

Kemudian akses via `http://ptamara.local` atau `http://ptamara.local/admin/login`.

### Troubleshooting XAMPP

**Issue: 404 atau "page not found"**
- Pastikan `.htaccess` di root project dan folder `public/` ada
- Pastikan Apache module `mod_rewrite` aktif di XAMPP
- Untuk extract langsung ke `htdocs\lulu`, akses `http://localhost/lulu/`, bukan `http://localhost/lulu/public/`

**Issue: Database connection error**
- Pastikan MySQL service di XAMPP sudah running
- Periksa username/password di `.env` (default: `root` dengan password kosong)
- Jika `XAMPP_AUTO_CREATE_DATABASE=true`, database dibuat otomatis. Jika gagal, buat manual di phpMyAdmin.

**Issue: Storage/upload tidak bekerja**
- Pastikan folder `storage/` dan `public/` writable.
- Release prebuild melayani `/storage/...` lewat route Laravel, sehingga `php artisan storage:link` tidak wajib untuk XAMPP.

**Manual fallback jika auto-setup gagal**

```bash
php artisan key:generate
php artisan migrate --seed --force
php artisan optimize:clear
```

Jika release ZIP sudah berisi folder `public`, Anda boleh tetap memakai virtual host ke folder `public/`. Untuk mode extract langsung, root `index.php` dan `.htaccess` sudah disiapkan agar URL `http://localhost/lulu/` berjalan tanpa konfigurasi vhost.
