<?php
/**
 * Router script for PHP built-in development server
 * Start server with: php -S localhost:8080 router.php
 */

// Serve static files directly
if (php_sapi_name() === 'cli-server') {
    $uri = $_SERVER['REQUEST_URI'];

    // Support local subdirectory access (e.g., /mdeditor/*)
    if (strpos($uri, '/mdeditor/') === 0) {
        $uri = substr($uri, strlen('/mdeditor'));
        $_SERVER['REQUEST_URI'] = $uri ?: '/';
        $_SERVER['SCRIPT_NAME'] = '/mdeditor/index.php';
        $_SERVER['PHP_SELF'] = '/mdeditor/index.php';
    }

    $file = __DIR__ . $uri;

    // If it's a real file (not a directory) and exists, serve it
    if (is_file($file)) {
        return false;
    }
}

// Otherwise, route to index.php
require_once __DIR__ . '/index.php';
