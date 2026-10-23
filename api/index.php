<?php

// Register Composer autoloader early so class_exists checks work
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Vercel serverless entry point for Laravel
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL')) {
    // Direct storage to writable /tmp
    $storagePath = '/tmp/storage';
    putenv("LARAVEL_STORAGE_PATH={$storagePath}");
    $_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
    $_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;

    // Direct bootstrap cache to writable /tmp
    $bootstrapCache = '/tmp/bootstrap/cache';
    if (!is_dir($bootstrapCache)) {
        mkdir($bootstrapCache, 0755, true);
    }

    // Copy bundled bootstrap manifests if they exist
    $bundledCache = __DIR__ . '/../bootstrap/cache';
    if (file_exists("{$bundledCache}/packages.php") && !file_exists("{$bootstrapCache}/packages.php")) {
        copy("{$bundledCache}/packages.php", "{$bootstrapCache}/packages.php");
    }
    if (file_exists("{$bundledCache}/services.php") && !file_exists("{$bootstrapCache}/services.php")) {
        copy("{$bundledCache}/services.php", "{$bootstrapCache}/services.php");
    }

    // Filter out dev-only providers (e.g. Pail, Sail) that aren't installed in production
    if (file_exists("{$bootstrapCache}/services.php")) {
        $services = require "{$bootstrapCache}/services.php";
        if (isset($services['providers']) && is_array($services['providers'])) {
            $services['providers'] = array_values(array_filter($services['providers'], fn($p) => class_exists($p)));
        }
        if (isset($services['eager']) && is_array($services['eager'])) {
            $services['eager'] = array_values(array_filter($services['eager'], fn($p) => class_exists($p)));
        }
        file_put_contents("{$bootstrapCache}/services.php", '<?php return ' . var_export($services, true) . ';');
    }

    if (file_exists("{$bootstrapCache}/packages.php")) {
        $packages = require "{$bootstrapCache}/packages.php";
        if (is_array($packages)) {
            foreach ($packages as $pkg => $cfg) {
                if (isset($cfg['providers']) && is_array($cfg['providers'])) {
                    $packages[$pkg]['providers'] = array_values(array_filter($cfg['providers'], fn($p) => class_exists($p)));
                    if (empty($packages[$pkg]['providers'])) {
                        unset($packages[$pkg]);
                    }
                }
            }
            file_put_contents("{$bootstrapCache}/packages.php", '<?php return ' . var_export($packages, true) . ';');
        }
    }

    putenv("APP_PACKAGES_CACHE={$bootstrapCache}/packages.php");
    putenv("APP_SERVICES_CACHE={$bootstrapCache}/services.php");
    putenv("APP_CONFIG_CACHE={$bootstrapCache}/config.php");
    putenv("APP_ROUTES_CACHE={$bootstrapCache}/routes.php");
    putenv("APP_EVENTS_CACHE={$bootstrapCache}/events.php");

    $_ENV['APP_PACKAGES_CACHE'] = "{$bootstrapCache}/packages.php";
    $_ENV['APP_SERVICES_CACHE'] = "{$bootstrapCache}/services.php";
    $_SERVER['APP_PACKAGES_CACHE'] = "{$bootstrapCache}/packages.php";
    $_SERVER['APP_SERVICES_CACHE'] = "{$bootstrapCache}/services.php";

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

    // Fallback APP_KEY if not set in Vercel environment variables
    if (empty($_ENV['APP_KEY']) && !getenv('APP_KEY')) {
        $appKey = 'base64:kFZmUA95+rcgirKEFSerwkOMzeW5nAqppLsf8MqcOgQ=';
        putenv("APP_KEY={$appKey}");
        $_ENV['APP_KEY'] = $appKey;
        $_SERVER['APP_KEY'] = $appKey;
    }

    // Fallback database to /tmp/database.sqlite if no cloud DB configured yet
    if (empty($_ENV['DB_HOST']) && !getenv('DB_HOST')) {
        $sqliteFile = '/tmp/database.sqlite';
        if (!file_exists($sqliteFile)) {
            touch($sqliteFile);
        }
        putenv('DB_CONNECTION=sqlite');
        putenv("DB_DATABASE={$sqliteFile}");
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $sqliteFile;
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = $sqliteFile;
    }

    // Default session & cache to file (/tmp) so public pages don't crash without MySQL
    if (empty($_ENV['SESSION_DRIVER']) && !getenv('SESSION_DRIVER')) {
        putenv('SESSION_DRIVER=file');
        $_ENV['SESSION_DRIVER'] = 'file';
        $_SERVER['SESSION_DRIVER'] = 'file';
    }

    if (empty($_ENV['CACHE_STORE']) && !getenv('CACHE_STORE')) {
        putenv('CACHE_STORE=file');
        $_ENV['CACHE_STORE'] = 'file';
        $_SERVER['CACHE_STORE'] = 'file';
    }

    // Set view compiled path explicitly
    putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");
    $_ENV['VIEW_COMPILED_PATH'] = "{$storagePath}/framework/views";
    $_SERVER['VIEW_COMPILED_PATH'] = "{$storagePath}/framework/views";
}

// Forward to public/index.php
require __DIR__ . '/../public/index.php';
