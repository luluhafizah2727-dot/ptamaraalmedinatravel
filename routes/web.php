<?php

use App\Http\Controllers\PublicPageController;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

Route::get('/storage/{path}', function (string $path) {
    abort_if(str_contains($path, '..') || str_contains($path, '\\'), 404);

    $disk = Storage::disk('public');

    abort_unless($disk->exists($path), 404);

    return response($disk->get($path), 200, [
        'Cache-Control' => 'public, max-age=3600',
        'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
    ]);
})->where('path', '.*')->name('storage.public');

Route::middleware(TrackVisitor::class)->controller(PublicPageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/profil', 'profile')->name('profile');
    Route::get('/paket-umrah', 'packages')->name('packages');
    Route::get('/paket-umrah/{package}', 'packageDetail')->name('packages.show');
    Route::get('/jadwal', 'schedules')->name('schedules');
    Route::get('/galeri', 'galleries')->name('galleries');
    Route::get('/kontak', 'contact')->name('contact');
});
