# Setup XAMPP untuk PT Amara Al Medina Travel

Panduan ini adalah versi ringkas untuk menjalankan prebuild di XAMPP Windows dengan PHP 8.2.12. Untuk penjelasan yang lebih lengkap dan ramah pemula, baca `README.md`.

## File yang Dipakai

Gunakan asset release:

```text
ptamaraalmedinatravel-v1.0.0.zip
```

Jangan gunakan **Source code (zip)** dari GitHub untuk instalasi XAMPP, karena file tersebut tidak membawa dependency dan asset build.

## Langkah Cepat

1. Extract ZIP ke:

   ```text
   C:\xampp\htdocs\ptamaraalmedinatravel
   ```

2. Start `Apache` dan `MySQL` dari XAMPP Control Panel.

3. Buka:

   ```text
   Website: http://localhost/ptamaraalmedinatravel/
   Admin:   http://localhost/ptamaraalmedinatravel/admin/login
   ```

Pada request pertama, aplikasi akan membuat `.env`, mengisi `APP_KEY`, membuat database jika belum ada, menjalankan migration, menjalankan seeder, dan menyiapkan storage.

## Default Database XAMPP

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ptamaraalmedinatravel
DB_USERNAME=root
DB_PASSWORD=
```

Jika MySQL Anda memakai password, edit file `.env` setelah extract lalu refresh halaman.

## Admin Awal

```env
ADMIN_INITIAL_NAME=Admin
ADMIN_INITIAL_EMAIL=admin@example.com
ADMIN_INITIAL_PASSWORD=admin!2345
```

Ubah nilai di atas sebelum setup pertama jika ingin credential berbeda.

## Troubleshooting Singkat

- Jika halaman tidak terbuka, pastikan Apache running dan URL sesuai nama folder di `htdocs`.
- Jika muncul error database, pastikan MySQL running dan credential `.env` benar.
- Jika CSS/JS tidak tampil, pastikan `public/build/manifest.json` dan `public/build/assets/` ada.
- Jika memakai source code, jalankan `composer install`, `npm ci`, dan `npm run build`.

Manual fallback dari folder project:

```bat
php artisan key:generate
php artisan migrate --seed --force
php artisan optimize:clear
```

Untuk prebuild XAMPP, `php artisan storage:link` tidak wajib karena route `/storage/...` sudah disiapkan.
