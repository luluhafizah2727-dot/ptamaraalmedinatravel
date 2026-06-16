<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    use HasFactory;

    public const BRAND_LOGO_KEY = 'brand_logo_path';
    public const FAVICON_KEY = 'favicon_path';
    public const HERO_IMAGE_KEY = 'hero_image_path';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            self::BRAND_LOGO_KEY => [
                'label' => 'Logo Website',
                'description' => 'Logo yang tampil di header website dan panel admin. Gunakan gambar persegi atau mendekati persegi.',
                'type' => 'image',
                'directory' => 'site-settings',
                'basename' => 'brand-logo',
                'fallback' => 'images/site/logo.png',
                'aspect_ratio' => '1:1',
                'resize_width' => '512',
                'resize_height' => '512',
                'max_size' => 2048,
                'preview_height' => '160',
            ],
            self::FAVICON_KEY => [
                'label' => 'Favicon',
                'description' => 'Ikon kecil di tab browser. Gunakan gambar persegi yang sederhana dan jelas.',
                'type' => 'image',
                'directory' => 'site-settings',
                'basename' => 'favicon',
                'fallback' => 'images/site/logo.png',
                'aspect_ratio' => '1:1',
                'resize_width' => '256',
                'resize_height' => '256',
                'max_size' => 1024,
                'preview_height' => '120',
            ],
            self::HERO_IMAGE_KEY => [
                'label' => 'Foto Hero Beranda',
                'description' => 'Foto utama di halaman beranda. Sistem akan memotong ke rasio lebar 16:9 agar tampil rapi.',
                'type' => 'image',
                'directory' => 'site-settings',
                'basename' => 'hero-image',
                'fallback' => 'images/site/beranda-img.jpg',
                'aspect_ratio' => '16:9',
                'resize_width' => '1600',
                'resize_height' => '900',
                'max_size' => 4096,
                'preview_height' => '220',
            ],
            'hero_title_highlight' => [
                'label' => 'Label Kecil Hero',
                'description' => 'Teks kecil di atas judul utama beranda.',
                'type' => 'text',
            ],
            'hero_title' => [
                'label' => 'Judul Utama Hero',
                'description' => 'Judul besar yang tampil di halaman beranda.',
                'type' => 'text',
            ],
            'hero_subtitle' => [
                'label' => 'Deskripsi Hero',
                'description' => 'Kalimat penjelasan di bawah judul beranda.',
                'type' => 'textarea',
            ],
            'cta_whatsapp' => [
                'label' => 'Nomor WhatsApp Tombol CTA',
                'description' => 'Nomor WhatsApp untuk tombol hubungi kami. Boleh memakai format 08... atau 62...',
                'type' => 'text',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function selectableOptions(): array
    {
        return collect(static::definitions())
            ->mapWithKeys(fn (array $definition, string $key): array => [$key => $definition['label']])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function definition(?string $key): array
    {
        return static::definitions()[$key] ?? [
            'label' => $key ?: 'Pengaturan',
            'description' => 'Pengaturan tambahan.',
            'type' => 'textarea',
        ];
    }

    public static function labelFor(?string $key): string
    {
        return (string) (static::definition($key)['label'] ?? $key ?? 'Pengaturan');
    }

    public static function descriptionFor(?string $key): string
    {
        return (string) (static::definition($key)['description'] ?? 'Pengaturan tambahan.');
    }

    public static function typeFor(?string $key): string
    {
        return (string) (static::definition($key)['type'] ?? 'textarea');
    }

    public static function isImageKey(?string $key): bool
    {
        return static::typeFor($key) === 'image';
    }

    public static function isTextareaKey(?string $key): bool
    {
        return static::typeFor($key) === 'textarea';
    }

    public static function storeReplacementImage(?string $key, UploadedFile $file, ?string $currentPath = null): string
    {
        $definition = static::definition($key);
        $directory = trim((string) ($definition['directory'] ?? 'site-settings'), '/');
        $basename = (string) ($definition['basename'] ?? ($key ?: 'setting-image'));
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $path = "{$directory}/{$basename}.{$extension}";
        $disk = Storage::disk('public');

        foreach ($disk->files($directory) as $existingPath) {
            if (pathinfo($existingPath, PATHINFO_FILENAME) === $basename) {
                $disk->delete($existingPath);
            }
        }

        if ($currentPath && str_starts_with($currentPath, $directory.'/')) {
            $disk->delete($currentPath);
        }

        $file->storeAs($directory, "{$basename}.{$extension}", 'public');

        return $path;
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function mediaUrl(string $key, ?string $fallback = null): string
    {
        $definition = static::definition($key);
        $fallback ??= $definition['fallback'] ?? null;

        return static::resolveMediaUrl(static::getValue($key), $fallback);
    }

    public static function resolveMediaUrl(?string $path, ?string $fallback = null): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return asset($fallback ?: 'images/site/logo.png');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        $storagePath = str_starts_with($path, 'storage/')
            ? substr($path, strlen('storage/'))
            : $path;

        if (Storage::disk('public')->exists($storagePath)) {
            return asset('storage/'.$storagePath);
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        if ($fallback !== null && is_file(public_path($fallback))) {
            return asset($fallback);
        }

        return asset($fallback ?: $path);
    }
}
