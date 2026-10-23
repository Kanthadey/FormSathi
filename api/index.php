<?php

// Vercel serverless entry point for Laravel
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL')) {
    // Direct storage to writable /tmp
    $storagePath = '/tmp/storage';
    putenv("LARAVEL_STORAGE_PATH={$storagePath}");
    $_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
    $_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;

    // Required storage subdirectories
    $subDirs = [
        "{$storagePath}/framework/views",
        "{$storagePath}/framework/cache/data",
        "{$storagePath}/framework/sessions",
        "{$storagePath}/logs",
        "{$storagePath}/app/public",
    ];

    foreach ($subDirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Forward to public/index.php
require __DIR__ . '/../public/index.php';
