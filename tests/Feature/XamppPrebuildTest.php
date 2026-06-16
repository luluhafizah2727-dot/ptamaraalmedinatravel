<?php

namespace Tests\Feature;

use App\Support\XamppAutoSetup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class XamppPrebuildTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_xampp_setup_detects_stale_marker_when_required_tables_are_missing(): void
    {
        $this->assertTrue(XamppAutoSetup::databaseHasRequiredTables());

        Schema::drop('site_settings');

        $this->assertFalse(XamppAutoSetup::databaseHasRequiredTables());
    }

    public function test_xampp_setup_recognizes_missing_database_errors_for_auto_repair(): void
    {
        $exception = new RuntimeException("SQLSTATE[42S02]: Base table or view not found: 1146 Table 'ptamaraalmedinatravel.sessions' doesn't exist");

        $this->assertTrue(XamppAutoSetup::isMissingDatabaseObject($exception));
        $this->assertFalse(XamppAutoSetup::isMissingDatabaseObject(new RuntimeException('Validasi form gagal.')));
    }
}
