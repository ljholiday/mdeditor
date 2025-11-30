<?php
/**
 * Markdown Editor - Front Controller
 * PSR-4 compliant entry point
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use MarkdownEditor\Config\Config;
use MarkdownEditor\Router;

// Initialize configuration
Config::load(__DIR__ . '/.env');

// Create and dispatch router
$router = new Router();
$router->dispatch();
