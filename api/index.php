<?php

/**
 * Vercel Serverless Entry Point for Laravel 11
 */

// Set writable paths for serverless environment
putenv('VIEW_COMPILED_PATH=/tmp/views');
putenv('LOG_CHANNEL=stderr');
putenv('SESSION_DRIVER=cookie');
putenv('CACHE_STORE=array');

// Ensure tmp directories exist
foreach (['/tmp/views', '/tmp/cache', '/tmp/sessions', '/tmp/storage/framework/views', '/tmp/storage/framework/cache', '/tmp/storage/framework/sessions', '/tmp/storage/logs'] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

require __DIR__ . '/../vendor/autoload.php';

// Laravel 11 bootstrap style
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Override storage path for serverless
$app->useStoragePath('/tmp/storage');

// Handle the request using Laravel 11's method
$app->handleRequest(
    \Illuminate\Http\Request::capture()
);
