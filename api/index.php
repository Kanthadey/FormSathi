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

    // Fallback APP_KEY if not set in Vercel environment variables
    if (empty($_ENV['APP_KEY']) && !getenv('APP_KEY')) {
        $appKey = 'base64:kFZmUA95+rcgirKEFSerwkOMzeW5nAqppLsf8MqcOgQ=';
        putenv("APP_KEY={$appKey}");
        $_ENV['APP_KEY'] = $appKey;
        $_SERVER['APP_KEY'] = $appKey;
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

    // Enable debug mode for diagnostics if not explicitly disabled
    if (empty($_ENV['APP_DEBUG']) && !getenv('APP_DEBUG')) {
        putenv('APP_DEBUG=true');
        $_ENV['APP_DEBUG'] = 'true';
        $_SERVER['APP_DEBUG'] = 'true';
    }
}

// Forward to public/index.php with exception catching
try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>FormSathi - Error</title><style>body{font-family:sans-serif;padding:30px;background:#f9f9f9;color:#333;}pre{background:#eee;padding:15px;border-radius:6px;overflow:auto;}</style></head><body>';
    echo '<h1>FormSathi - Serverless Exception</h1>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Location:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<h3>Stack Trace</h3>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</body></html>';
}
