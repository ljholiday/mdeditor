<?php
/**
 * Router script for PHP built-in development server
 * Start server with: php -S localhost:8080 router.php
 */

// Serve static files directly
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . $_SERVER['REQUEST_URI'];

    // If it's a real file (not a directory) and exists, serve it
    if (is_file($file)) {
        return false;
    }
}

// Otherwise, route to index.php
require_once __DIR__ . '/index.php';
