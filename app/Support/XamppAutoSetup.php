<?php

namespace App\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class XamppAutoSetup
{
    public static function preflight(string $basePath, ?array $server = null): void
    {
        $server ??= $_SERVER;

        if (! self::isHttpRequest($server) || ! self::isLocalRequest($server)) {
            return;
        }

        self::ensureRuntimeDirectories($basePath);

        $envPath = $basePath.DIRECTORY_SEPARATOR.'.env';
        $examplePath = $basePath.DIRECTORY_SEPARATOR.'.env.example';

        if (! is_file($envPath)) {
            if (! is_file($examplePath)) {
                throw new RuntimeException('File .env belum ada dan .env.example tidak ditemukan.');
            }

            if (! copy($examplePath, $envPath)) {
                throw new RuntimeException('Gagal membuat file .env dari .env.example. Pastikan folder aplikasi writable.');
            }
        }

        $env = self::readEnv($envPath);

        if (! self::truthy($env['XAMPP_AUTO_SETUP'] ?? false)) {
            return;
        }

        self::ensureRequiredExtensions([
            'dom',
            'fileinfo',
            'filter',
            'iconv',
            'intl',
            'openssl',
            'pdo_mysql',
            'session',
            'tokenizer',
            'xmlreader',
            'zip',
        ]);

        $updates = [];

        if (self::blank($env['APP_KEY'] ?? null)) {
            $updates['APP_KEY'] = 'base64:'.base64_encode(random_bytes(32));
        }

        if (self::truthy($env['XAMPP_AUTO_DETECT_URL'] ?? true)) {
            $baseUrl = self::detectBaseUrl($server);
            $storageUrl = self::detectBasePath($server).'/storage';

            $updates['APP_URL'] = $baseUrl;
            $updates['FILESYSTEM_PUBLIC_URL'] = $storageUrl === '/storage' ? '/storage' : $storageUrl;
        }

        if (self::truthy($env['XAMPP_AUTO_FILE_RUNTIME'] ?? true)) {
            $updates['SESSION_DRIVER'] = 'file';
            $updates['CACHE_STORE'] = 'file';
            $updates['QUEUE_CONNECTION'] = 'sync';
        }

        if ($updates !== []) {
            self::writeEnv($envPath, $updates);
            $env = array_merge($env, $updates);
        }

        self::clearBootstrapCaches($basePath);

        if (self::truthy($env['XAMPP_AUTO_CREATE_DATABASE'] ?? false)) {
            self::createDatabaseIfNeeded($env);
        }
    }

    public static function ensureInstalled(Application $app, bool $force = false): void
    {
        if ($app->runningInConsole() || $app->environment('testing')) {
            return;
        }

        if (! self::truthy((string) env('XAMPP_AUTO_SETUP', false))) {
            return;
        }

        $shouldMigrate = self::truthy((string) env('XAMPP_AUTO_MIGRATE', true));
        $shouldSeed = self::truthy((string) env('XAMPP_AUTO_SEED', true));

        if (! $shouldMigrate && ! $shouldSeed) {
            return;
        }

        $basePath = $app->basePath();
        self::ensureRuntimeDirectories($basePath);

        $markerPath = $basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'.xampp-installed.json';
        $signature = self::setupSignature($basePath);

        $databaseReady = self::databaseHasRequiredTables();

        if (! $force && self::markerMatches($markerPath, $signature) && $databaseReady) {
            return;
        }

        $lockPath = $basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'xampp-setup.lock';
        $lockHandle = fopen($lockPath, 'c');

        if ($lockHandle === false) {
            throw new RuntimeException('Gagal membuat lock auto-setup. Pastikan folder storage/framework writable.');
        }

        try {
            if (! flock($lockHandle, LOCK_EX)) {
                throw new RuntimeException('Gagal mengunci proses auto-setup XAMPP.');
            }

            $databaseReady = self::databaseHasRequiredTables();

            if (! $force && self::markerMatches($markerPath, $signature) && $databaseReady) {
                return;
            }

            if (! $shouldMigrate && ! $databaseReady) {
                throw new RuntimeException('Database belum memiliki tabel aplikasi. Aktifkan XAMPP_AUTO_MIGRATE=true atau jalankan migrasi manual.');
            }

            if ($shouldMigrate) {
                self::runArtisan('migrate', ['--force' => true]);
            }

            if ($shouldSeed) {
                self::runArtisan('db:seed', ['--force' => true]);
            }

            $payload = [
                'installed_at' => gmdate('c'),
                'signature' => $signature,
                'migrate' => $shouldMigrate,
                'seed' => $shouldSeed,
            ];

            if (file_put_contents($markerPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
                throw new RuntimeException('Gagal menulis marker auto-setup XAMPP.');
            }
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    public static function databaseHasRequiredTables(): bool
    {
        try {
            foreach (['migrations', 'users', 'site_settings'] as $table) {
                if (! Schema::hasTable($table)) {
                    return false;
                }
            }
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    public static function isMissingDatabaseObject(Throwable $exception): bool
    {
        do {
            $message = $exception->getMessage();
            $lowerMessage = strtolower($message);
            $code = (string) $exception->getCode();

            if (
                str_contains($lowerMessage, 'base table or view not found')
                || str_contains($lowerMessage, 'no such table')
                || str_contains($lowerMessage, 'unknown database')
                || str_contains($lowerMessage, "doesn't exist")
                || $code === '42S02'
            ) {
                return true;
            }
        } while ($exception = $exception->getPrevious());

        return false;
    }

    public static function renderSetupError(Throwable $exception): never
    {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');

        echo self::errorHtml($exception);

        exit(1);
    }

    public static function setupErrorResponse(Throwable $exception): Response
    {
        return new Response(self::errorHtml($exception), 500, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private static function createDatabaseIfNeeded(array $env): void
    {
        $connection = strtolower((string) ($env['DB_CONNECTION'] ?? ''));

        if (! in_array($connection, ['mysql', 'mariadb'], true)) {
            return;
        }

        if (! extension_loaded('pdo_mysql')) {
            throw new RuntimeException('Ekstensi pdo_mysql belum aktif di PHP XAMPP.');
        }

        $database = (string) ($env['DB_DATABASE'] ?? '');

        if ($database === '') {
            throw new RuntimeException('DB_DATABASE belum diisi di file .env.');
        }

        self::assertMysqlIdentifier($database, 'DB_DATABASE');

        $charset = (string) ($env['DB_CHARSET'] ?? 'utf8mb4');
        $collation = (string) ($env['DB_COLLATION'] ?? 'utf8mb4_unicode_ci');

        self::assertMysqlIdentifier($charset, 'DB_CHARSET');
        self::assertMysqlIdentifier($collation, 'DB_COLLATION');

        $host = (string) ($env['DB_HOST'] ?? '127.0.0.1');
        $port = (string) ($env['DB_PORT'] ?? '3306');
        $username = (string) ($env['DB_USERNAME'] ?? 'root');
        $password = (string) ($env['DB_PASSWORD'] ?? '');
        $socket = (string) ($env['DB_SOCKET'] ?? '');

        $dsn = $socket !== ''
            ? "mysql:unix_socket={$socket};charset={$charset}"
            : "mysql:host={$host};port={$port};charset={$charset}";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
                str_replace('`', '``', $database),
                $charset,
                $collation,
            ));
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Tidak bisa terhubung ke MySQL XAMPP. Pastikan service MySQL berjalan dan credential DB di .env benar.',
                0,
                $exception,
            );
        }
    }

    /**
     * @param  list<string>  $extensions
     */
    private static function ensureRequiredExtensions(array $extensions): void
    {
        $missing = array_values(array_filter(
            $extensions,
            fn (string $extension): bool => ! extension_loaded($extension),
        ));

        if ($missing === []) {
            return;
        }

        throw new RuntimeException(
            'Ekstensi PHP XAMPP belum aktif: '.implode(', ', $missing).'. Aktifkan extension tersebut di php.ini XAMPP, lalu restart Apache.',
        );
    }

    private static function assertMysqlIdentifier(string $value, string $name): void
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $value)) {
            throw new RuntimeException("Nilai {$name} hanya boleh memakai huruf, angka, dan underscore.");
        }
    }

    private static function runArtisan(string $command, array $parameters = []): void
    {
        $exitCode = Artisan::call($command, $parameters);

        if ($exitCode !== 0) {
            $output = trim(Artisan::output());
            throw new RuntimeException("Perintah php artisan {$command} gagal.".($output !== '' ? "\n\n{$output}" : ''));
        }
    }

    private static function setupSignature(string $basePath): string
    {
        $files = glob($basePath.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'*.php') ?: [];
        $files[] = $basePath.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'seeders'.DIRECTORY_SEPARATOR.'DatabaseSeeder.php';

        sort($files);

        $parts = [];

        foreach ($files as $file) {
            if (is_file($file)) {
                $parts[] = basename($file).':'.sha1_file($file);
            }
        }

        return hash('sha256', implode('|', $parts));
    }

    private static function markerMatches(string $markerPath, string $signature): bool
    {
        if (! is_file($markerPath)) {
            return false;
        }

        $payload = json_decode((string) file_get_contents($markerPath), true);

        return is_array($payload) && ($payload['signature'] ?? null) === $signature;
    }

    private static function isHttpRequest(array $server): bool
    {
        return isset($server['HTTP_HOST'], $server['REQUEST_URI']);
    }

    private static function isLocalRequest(array $server): bool
    {
        $rawHost = strtolower((string) ($server['HTTP_HOST'] ?? ''));
        $host = parse_url('http://'.$rawHost, PHP_URL_HOST) ?: $rawHost;
        $host = trim($host, '[]');

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        if (str_ends_with($host, '.local')) {
            return true;
        }

        return preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[0-1])\.)/', $host) === 1;
    }

    private static function detectBaseUrl(array $server): string
    {
        $scheme = (! empty($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off')
            ? 'https'
            : ((string) ($server['REQUEST_SCHEME'] ?? 'http'));

        return $scheme.'://'.($server['HTTP_HOST'] ?? 'localhost').self::detectBasePath($server);
    }

    private static function detectBasePath(array $server): string
    {
        $scriptName = str_replace('\\', '/', (string) ($server['SCRIPT_NAME'] ?? '/index.php'));
        $basePath = preg_replace('#/index\.php$#', '', $scriptName) ?: '';

        return rtrim($basePath, '/');
    }

    private static function ensureRuntimeDirectories(string $basePath): void
    {
        $directories = [
            'bootstrap/cache',
            'database',
            'storage/app/public',
            'storage/app/private',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
        ];

        foreach ($directories as $directory) {
            $path = $basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $directory);

            if (! is_dir($path) && ! mkdir($path, 0775, true) && ! is_dir($path)) {
                throw new RuntimeException("Gagal membuat folder {$directory}. Pastikan folder aplikasi writable.");
            }
        }
    }

    private static function clearBootstrapCaches(string $basePath): void
    {
        $cachePath = $basePath.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache';
        $patterns = ['config*.php', 'routes*.php', 'events*.php'];

        foreach ($patterns as $pattern) {
            foreach (glob($cachePath.DIRECTORY_SEPARATOR.$pattern) ?: [] as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    private static function readEnv(string $envPath): array
    {
        $values = [];

        foreach (file($envPath, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $values[$key] = $value;
        }

        return $values;
    }

    private static function writeEnv(string $envPath, array $updates): void
    {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES) ?: [];
        $seen = [];

        foreach ($lines as $index => $line) {
            if (! str_contains($line, '=') || str_starts_with(trim($line), '#')) {
                continue;
            }

            [$key] = explode('=', $line, 2);
            $key = trim($key);

            if (array_key_exists($key, $updates)) {
                $lines[$index] = $key.'='.self::formatEnvValue((string) $updates[$key]);
                $seen[$key] = true;
            }
        }

        foreach ($updates as $key => $value) {
            if (! isset($seen[$key])) {
                $lines[] = $key.'='.self::formatEnvValue((string) $value);
            }
        }

        if (file_put_contents($envPath, implode(PHP_EOL, $lines).PHP_EOL) === false) {
            throw new RuntimeException('Gagal memperbarui file .env. Pastikan file .env writable.');
        }
    }

    private static function formatEnvValue(string $value): string
    {
        if ($value === '' || preg_match('/\s|#/', $value)) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }

    private static function truthy(mixed $value): bool
    {
        $value = strtolower(trim((string) $value, " \t\n\r\0\x0B\"'"));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private static function blank(mixed $value): bool
    {
        return trim((string) $value) === '';
    }

    private static function errorHtml(Throwable $exception): string
    {
        $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup XAMPP belum selesai</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; }
        main { max-width: 760px; margin: 48px auto; padding: 24px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; }
        h1 { margin-top: 0; font-size: 24px; }
        code, pre { background: #f1f5f9; border-radius: 6px; }
        pre { overflow: auto; padding: 12px; }
        li { margin: 8px 0; }
    </style>
</head>
<body>
<main>
    <h1>Setup XAMPP belum selesai</h1>
    <p>Aplikasi belum bisa dijalankan karena proses setup otomatis berhenti.</p>
    <pre>{$message}</pre>
    <p>Periksa hal berikut, lalu refresh halaman:</p>
    <ol>
        <li>Apache dan MySQL di XAMPP sudah running.</li>
        <li>Credential database di file <code>.env</code> benar.</li>
        <li>Folder aplikasi, <code>storage</code>, dan <code>bootstrap/cache</code> bisa ditulis.</li>
        <li>Ekstensi PHP <code>pdo_mysql</code>, <code>openssl</code>, <code>fileinfo</code>, <code>intl</code>, dan <code>zip</code> aktif.</li>
    </ol>
</main>
</body>
</html>
HTML;
    }
}
