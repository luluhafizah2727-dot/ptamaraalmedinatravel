<?php

use App\Support\XamppAutoSetup;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

try {
    XamppAutoSetup::preflight(__DIR__);
} catch (Throwable $exception) {
    XamppAutoSetup::renderSetupError($exception);
}

/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
