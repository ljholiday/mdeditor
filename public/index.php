<?php
/**
 * Markdown Editor - Front Controller
 * Self-contained, no external dependencies
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple PSR-4 autoloader
spl_autoload_register(function ($class) {
    $prefix = 'MarkdownEditor\\';
    $base_dir = __DIR__ . '/../src/MarkdownEditor/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use MarkdownEditor\Config\Config;
use MarkdownEditor\Router;

try {
    // Initialize configuration
    Config::load(__DIR__ . '/../.env');

    // Create and dispatch router
    $router = new Router();
    $router->dispatch();
} catch (\RuntimeException $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Configuration error\n\n";
    echo $e->getMessage() . "\n";
    exit;
}
