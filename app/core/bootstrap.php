<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Composer Autoload
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Environment Variables
|--------------------------------------------------------------------------
*/

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->safeLoad();

/*
|--------------------------------------------------------------------------
| PHP Configuration
|--------------------------------------------------------------------------
*/

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');

if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}