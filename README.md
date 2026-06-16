# PT Amara Al Medina Travel

PT Amara Al Medina Travel adalah aplikasi website profil perusahaan dan panel admin. Website publik dipakai untuk menampilkan informasi perusahaan, paket umrah, jadwal, galeri, dan kontak. Panel admin dipakai untuk mengelola isi website tersebut.

Panduan ini ditulis untuk pembaca yang baru pertama kali melihat project ini. Jika tujuan Anda hanya ingin menjalankan aplikasi di XAMPP, gunakan bagian **Cara Paling Mudah: Pakai Prebuild ZIP**.

## Ringkasan Singkat

- Aplikasi dibuat dengan Laravel 12.
- PHP yang direkomendasikan: PHP 8.2.12, sesuai bawaan XAMPP yang diuji.
- Database memakai MySQL/MariaDB dari XAMPP.
- Panel admin ada di `/admin/login`.
- Release prebuild sudah berisi dependency PHP dan asset frontend, jadi Composer dan Node.js tidak wajib dipasang untuk penggunaan biasa di XAMPP.

## Cara Paling Mudah: Pakai Prebuild ZIP

Gunakan cara ini jika Anda ingin menjalankan aplikasi di XAMPP tanpa proses build manual.

### 1. Siapkan XAMPP

Pastikan XAMPP sudah terpasang dan memakai PHP 8.2.12.

Buka XAMPP Control Panel, lalu nyalakan:

- Apache
- MySQL

Composer dan Node.js tidak diperlukan untuk menjalankan file prebuild.

### 2. Download File Prebuild

Buka halaman **Releases** di GitHub, lalu download file:

```text
ptamaraalmedinatravel-v1.0.0.zip
```

Catatan penting:

- Pilih file ZIP dengan nama seperti di atas.
- Jangan memakai tombol **Source code (zip)** dari GitHub untuk instalasi XAMPP, karena source archive belum berisi `vendor/` dan asset build.

### 3. Extract ke Folder htdocs

Extract isi ZIP ke folder XAMPP:

```text
C:\xampp\htdocs\ptamaraalmedinatravel
```

Jika XAMPP Anda berada di lokasi lain, sesuaikan bagian awal path-nya. Contoh:

```text
C:\Users\Nama Anda\Documents\xampp\htdocs\ptamaraalmedinatravel
```

Yang penting, folder project berada di dalam folder `htdocs`.

### 4. Buka di Browser

Jika foldernya bernama `ptamaraalmedinatravel`, buka:

```text
http://localhost/ptamaraalmedinatravel/
```

Panel admin:

```text
http://localhost/ptamaraalmedinatravel/admin/login
```

Pada pembukaan pertama, aplikasi akan melakukan setup otomatis. Tunggu sampai halaman selesai terbuka.

## Apa yang Otomatis Dilakukan Saat Pertama Dibuka

Prebuild XAMPP sudah dibuat agar pengguna tidak perlu menjalankan perintah ini secara manual:

```bash
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize:clear
```

Saat halaman pertama dibuka lewat `localhost`, aplikasi otomatis:

- membuat file `.env` dari `.env.example` jika belum ada,
- membuat `APP_KEY` jika masih kosong,
- menyesuaikan `APP_URL` berdasarkan URL yang sedang dibuka,
- menyesuaikan URL storage agar gambar tetap bisa tampil dari subfolder XAMPP,
- membuat database MySQL jika belum ada,
- menjalankan migration dan seeder satu kali,
- menyiapkan folder runtime seperti `storage/` dan `bootstrap/cache/`,
- melayani file `/storage/...` tanpa wajib menjalankan `php artisan storage:link`.

Setup otomatis ditandai dengan file:

```text
storage\app\.xampp-installed.json
```

Selama file marker ini masih cocok dengan migration dan seeder saat ini, proses setup tidak diulang pada setiap refresh.

## Login Admin Awal

Credential admin awal diambil dari file `.env`.

Default pada prebuild:

```env
ADMIN_INITIAL_EMAIL=admin@example.com
ADMIN_INITIAL_PASSWORD=admin!2345
```

Saran:

- Untuk penggunaan lokal biasa, Anda bisa login memakai credential default tersebut.
- Jika ingin mengganti credential sebelum setup pertama, edit `.env.example` atau `.env` sebelum membuka halaman pertama.
- Jika setup sudah terlanjur berjalan, ubah data user lewat database/phpMyAdmin atau jalankan ulang seeder secara manual.

## Memahami File .env

File `.env` adalah file konfigurasi lokal. File ini dibuat otomatis dari `.env.example` saat aplikasi pertama kali dibuka.

Jangan upload atau commit file `.env`, karena file ini bisa berisi password database, password email, dan secret aplikasi.

### Pengaturan Aplikasi

```env
APP_NAME="PT Amara Al Medina Travel"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost/ptamaraalmedinatravel
```

Penjelasan:

- `APP_NAME`: nama aplikasi yang tampil di beberapa bagian sistem.
- `APP_ENV`: lingkungan aplikasi. Untuk XAMPP lokal gunakan `local`.
- `APP_KEY`: kunci enkripsi Laravel. Pada prebuild, nilai ini dibuat otomatis jika kosong.
- `APP_DEBUG`: jika `true`, error tampil lebih detail. Untuk server publik, ubah ke `false`.
- `APP_URL`: alamat utama aplikasi. Pada XAMPP prebuild, nilai ini bisa dideteksi otomatis dari URL pertama yang dibuka.

### Pengaturan Auto-Setup XAMPP

```env
XAMPP_AUTO_SETUP=true
XAMPP_AUTO_DETECT_URL=true
XAMPP_AUTO_FILE_RUNTIME=true
XAMPP_AUTO_CREATE_DATABASE=true
XAMPP_AUTO_MIGRATE=true
XAMPP_AUTO_SEED=true
```

Penjelasan:

- `XAMPP_AUTO_SETUP`: mengaktifkan setup otomatis untuk XAMPP/local.
- `XAMPP_AUTO_DETECT_URL`: membuat aplikasi mengisi `APP_URL` dan `FILESYSTEM_PUBLIC_URL` dari URL browser.
- `XAMPP_AUTO_FILE_RUNTIME`: memakai session file, cache file, dan queue sync agar tidak bergantung pada tabel database saat request pertama.
- `XAMPP_AUTO_CREATE_DATABASE`: membuat database otomatis jika belum ada.
- `XAMPP_AUTO_MIGRATE`: menjalankan migration otomatis.
- `XAMPP_AUTO_SEED`: mengisi data awal otomatis.

Untuk pemakaian XAMPP yang sederhana, biarkan semua nilai di atas tetap `true`.

### Pengaturan Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ptamaraalmedinatravel
DB_USERNAME=root
DB_PASSWORD=
```

Penjelasan:

- `DB_CONNECTION`: jenis database. Untuk XAMPP gunakan `mysql`.
- `DB_HOST`: alamat database. Untuk XAMPP biasanya `127.0.0.1`.
- `DB_PORT`: port MySQL. Default XAMPP biasanya `3306`.
- `DB_DATABASE`: nama database yang akan dipakai. Jika auto-create aktif, database ini dibuat otomatis.
- `DB_USERNAME`: username MySQL. Default XAMPP biasanya `root`.
- `DB_PASSWORD`: password MySQL. Default XAMPP biasanya kosong.

Jika MySQL Anda memakai password, isi `DB_PASSWORD` sesuai password tersebut.

### Pengaturan Session, Cache, dan Queue

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Penjelasan:

- `SESSION_DRIVER=file`: data login/session disimpan di file lokal.
- `CACHE_STORE=file`: cache disimpan di file lokal.
- `QUEUE_CONNECTION=sync`: proses queue dijalankan langsung tanpa worker tambahan.

Nilai ini dipilih agar first-run di XAMPP lebih mudah dan tidak error sebelum tabel database dibuat.

### Pengaturan File dan Gambar

```env
FILESYSTEM_DISK=public
FILESYSTEM_PUBLIC_URL=/storage
```

Penjelasan:

- `FILESYSTEM_DISK=public`: upload dan gambar publik memakai storage Laravel public.
- `FILESYSTEM_PUBLIC_URL`: URL untuk membaca file publik. Pada mode subfolder XAMPP, nilai ini otomatis disesuaikan, misalnya `/ptamaraalmedinatravel/storage`.

Prebuild juga memiliki route fallback untuk `/storage/...`, sehingga `php artisan storage:link` tidak wajib untuk penggunaan XAMPP sederhana.

### Pengaturan Email

```env
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Default `MAIL_MAILER=log` berarti email tidak benar-benar dikirim, tetapi dicatat ke log. Ini aman untuk development lokal.

Jika aplikasi nanti perlu mengirim email sungguhan, ubah pengaturan `MAIL_*` sesuai layanan email yang dipakai.

### Pengaturan Admin Awal

```env
ADMIN_INITIAL_NAME=Admin
ADMIN_INITIAL_EMAIL=admin@example.com
ADMIN_INITIAL_PASSWORD=admin!2345
```

Penjelasan:

- `ADMIN_INITIAL_NAME`: nama akun admin awal.
- `ADMIN_INITIAL_EMAIL`: email login admin awal.
- `ADMIN_INITIAL_PASSWORD`: password login admin awal.

Jika email atau password dikosongkan, seeder tidak membuat admin awal.

## Isi Prebuild ZIP

Prebuild final berisi file yang dibutuhkan untuk menjalankan aplikasi:

```text
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
vendor/
.env.example
.htaccess
index.php
artisan
composer.json
composer.lock
```

File penting:

- `vendor/`: dependency PHP dari Composer.
- `public/build/`: asset CSS dan JavaScript hasil build.
- `index.php` di root: front controller khusus agar aplikasi bisa dibuka langsung dari subfolder `htdocs`.
- `.htaccess` di root: aturan rewrite untuk route Laravel dan asset publik.
- `public/index.php`: entrypoint Laravel standar untuk deployment normal atau virtual host.
- `.env.example`: template konfigurasi lokal.

File yang sengaja tidak disertakan:

- `.env`
- `.git/`
- `.github/`
- `node_modules/`
- `tests/`
- log runtime
- cache runtime
- file credential lokal seperti `auth.json`

## Jika Ingin Mengubah Nama Folder

Anda boleh mengganti nama folder hasil extract.

Contoh:

```text
C:\xampp\htdocs\ptamaraalmedinatravel
```

maka URL:

```text
http://localhost/ptamaraalmedinatravel/
```

Jika nama foldernya berbeda, URL juga mengikuti nama folder tersebut. Auto-setup akan mencoba menyesuaikan `APP_URL` dan `FILESYSTEM_PUBLIC_URL` dari URL yang pertama kali dibuka.

## Setup dari Source Code

Gunakan bagian ini hanya jika Anda ingin mengembangkan project, mengubah kode, atau membuat release baru.

Kebutuhan tambahan:

- Composer 2.x
- Node.js 22+
- npm

Langkah dasar:

```bash
composer install
npm ci
npm run build
copy .env.example .env
```

Setelah itu buka aplikasi dari XAMPP atau gunakan PHP development server. Jika auto-setup dinonaktifkan, jalankan:

```bash
php artisan key:generate
php artisan migrate --seed --force
php artisan optimize:clear
```

## Deployment dengan Virtual Host

Untuk server produksi atau virtual host lokal, lebih baik arahkan document root ke folder `public/`.

Contoh Apache virtual host:

```apache
<VirtualHost *:80>
    DocumentRoot "C:\xampp\htdocs\ptamaraalmedinatravel\public"
    ServerName ptamaraalmedinatravel.local

    <Directory "C:\xampp\htdocs\ptamaraalmedinatravel\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Tambahkan ke file hosts Windows jika memakai domain lokal:

```text
127.0.0.1  ptamaraalmedinatravel.local
```

Lalu restart Apache dan buka:

```text
http://ptamaraalmedinatravel.local/
```

## Troubleshooting

### Halaman tidak bisa dibuka

Periksa:

- Apache di XAMPP sudah running.
- Folder project berada di dalam `htdocs`.
- URL browser sesuai nama folder.
- File root `.htaccess` masih ada.
- Apache `mod_rewrite` aktif.

### Error database

Periksa:

- MySQL di XAMPP sudah running.
- `DB_HOST`, `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env` benar.
- Jika memakai default XAMPP, username biasanya `root` dan password kosong.
- Jika `XAMPP_AUTO_CREATE_DATABASE=true`, database akan dibuat otomatis.

### CSS atau JavaScript tidak tampil

Periksa:

- File `public/build/manifest.json` ada.
- Folder `public/build/assets/` ada.
- Jika memakai source code, jalankan `npm ci` lalu `npm run build`.

### Gambar storage tidak tampil

Untuk prebuild XAMPP, `/storage/...` sudah dilayani lewat route Laravel. Jika tetap bermasalah:

- Pastikan folder `storage/app/public/` ada.
- Pastikan folder project bisa ditulis oleh Apache.
- Refresh halaman setelah setup pertama selesai.

### Admin tidak bisa login

Periksa nilai:

```env
ADMIN_INITIAL_EMAIL=
ADMIN_INITIAL_PASSWORD=
```

Jika database sudah pernah diseed, mengubah `.env` tidak otomatis mengubah user lama. Ubah lewat database/phpMyAdmin atau jalankan seeder ulang secara manual.

### Manual fallback jika setup otomatis gagal

Buka terminal di folder project, lalu jalankan:

```bash
php artisan key:generate
php artisan migrate --seed --force
php artisan optimize:clear
```

Perintah `php artisan storage:link` biasanya tidak wajib untuk prebuild XAMPP, tetapi boleh dijalankan jika Anda memakai virtual host ke folder `public/`.

## Perintah Verifikasi Developer

Untuk developer yang ingin memastikan build bersih:

```bash
composer validate --strict --no-check-publish
composer check-platform-reqs --no-dev
php artisan test
npm run build
npm audit --audit-level=moderate
```

Perintah di atas tidak wajib untuk pengguna prebuild biasa.
