<?php
if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if ($scriptDir === '' || $scriptDir === '/') {
        $baseUrl = '/';
    } else {
        if (basename($scriptDir) === 'Public') {
            $scriptDir = dirname($scriptDir);
        }

        $baseUrl = rtrim($scriptDir, '/') . '/';
    }

    define('BASE_URL', $baseUrl);
}