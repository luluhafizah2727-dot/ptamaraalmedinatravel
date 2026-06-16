<?php

namespace Tests\Unit;

use App\Support\XamppAutoSetup;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class XamppAutoSetupTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ptamara-xampp-test-'.bin2hex(random_bytes(6));
        mkdir($this->basePath, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->basePath);

        parent::tearDown();
    }

    public function test_preflight_creates_env_key_and_detects_subdirectory_urls(): void
    {
        $this->writeExampleEnv([
            'APP_KEY=',
            'APP_URL=http://localhost/old',
            'XAMPP_AUTO_SETUP=true',
            'XAMPP_AUTO_DETECT_URL=true',
            'XAMPP_AUTO_CREATE_DATABASE=false',
            'DB_CONNECTION=mysql',
            'DB_DATABASE=ptamaraalmedinatravel',
            'FILESYSTEM_PUBLIC_URL=/storage',
        ]);

        XamppAutoSetup::preflight($this->basePath, [
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/lulu/',
            'SCRIPT_NAME' => '/lulu/index.php',
            'REQUEST_SCHEME' => 'http',
        ]);

        $env = file_get_contents($this->basePath.DIRECTORY_SEPARATOR.'.env');

        $this->assertStringContainsString('APP_KEY=base64:', $env);
        $this->assertStringContainsString('APP_URL=http://localhost/lulu', $env);
        $this->assertStringContainsString('FILESYSTEM_PUBLIC_URL=/lulu/storage', $env);
        $this->assertDirectoryExists($this->basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public');
        $this->assertDirectoryExists($this->basePath.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache');
    }

    public function test_preflight_rejects_unsafe_mysql_database_name(): void
    {
        $this->writeExampleEnv([
            'APP_KEY=base64:already-set',
            'APP_URL=http://localhost/lulu',
            'XAMPP_AUTO_SETUP=true',
            'XAMPP_AUTO_CREATE_DATABASE=true',
            'DB_CONNECTION=mysql',
            'DB_DATABASE=bad-name',
            'DB_USERNAME=root',
            'DB_PASSWORD=',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_DATABASE');

        XamppAutoSetup::preflight($this->basePath, [
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/lulu/',
            'SCRIPT_NAME' => '/lulu/index.php',
            'REQUEST_SCHEME' => 'http',
        ]);
    }

    /**
     * @param  list<string>  $lines
     */
    private function writeExampleEnv(array $lines): void
    {
        file_put_contents(
            $this->basePath.DIRECTORY_SEPARATOR.'.env.example',
            implode(PHP_EOL, $lines).PHP_EOL,
        );
    }

    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($path);
    }
}
