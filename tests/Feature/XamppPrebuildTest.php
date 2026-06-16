<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class XamppPrebuildTest extends TestCase
{
    public function test_storage_route_serves_public_disk_files_without_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('xampp/test.txt', 'ok');

        $this->get('/storage/xampp/test.txt')
            ->assertOk()
            ->assertSee('ok');
    }

    public function test_root_xampp_entrypoint_and_rewrite_files_are_present(): void
    {
        $this->assertFileExists(base_path('index.php'));
        $this->assertStringContainsString("vendor/autoload.php", file_get_contents(base_path('index.php')));
        $this->assertStringContainsString("bootstrap/app.php", file_get_contents(base_path('index.php')));

        $htaccess = file_get_contents(base_path('.htaccess'));

        $this->assertStringContainsString('public/$0', $htaccess);
        $this->assertStringContainsString('^storage(/.*)?$ index.php', $htaccess);
        $this->assertStringContainsString('RewriteRule ^ index.php [L]', $htaccess);
    }
}
