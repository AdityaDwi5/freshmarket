<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Path Laravel di subfolder "backend"
$laravelPath = __DIR__ . '/backend';

// Cek mode maintenance
if (file_exists($maintenance = $laravelPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoload dari vendor Laravel
require $laravelPath.'/vendor/autoload.php';

// Bootstrap Laravel
(require_once $laravelPath.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
