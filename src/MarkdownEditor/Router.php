<?php

namespace MarkdownEditor;

use MarkdownEditor\Auth\SessionAuth;
use MarkdownEditor\Controller\AuthController;
use MarkdownEditor\Controller\EditorController;
use MarkdownEditor\Controller\FileController;

class Router
{
    private SessionAuth $auth;
    private array $routes = [];

    public function __construct()
    {
        $this->auth = new SessionAuth();
        $this->defineRoutes();
    }

    private function defineRoutes(): void
    {
        // Public routes (authentication not required)
        $this->routes = [
            'POST /login' => [AuthController::class, 'login', true],

            // Protected routes (require authentication)
            'GET /' => [EditorController::class, 'index', false],
            'GET /logout' => [AuthController::class, 'logout', false],
            'GET /api/files' => [FileController::class, 'list', false],
        ];
    }

    public function dispatch(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove script name/subdirectory from URI
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '/' && $scriptName !== '.') {
            $uri = substr($uri, strlen($scriptName));
        }

        $uri = rawurldecode($uri);
        $uri = $uri ?: '/';

        // Check for API file routes with dynamic paths
        if (strpos($uri, '/api/files/') === 0) {
            $filePath = substr($uri, strlen('/api/files/'));
            $this->handleApiFileRoute($httpMethod, $filePath);
            return;
        }

        // Check static routes
        $routeKey = "$httpMethod $uri";
        if (isset($this->routes[$routeKey])) {
            [$controllerClass, $method, $isPublic] = $this->routes[$routeKey];

            // Check authentication for protected routes
            if (!$isPublic && !$this->auth->isAuthenticated()) {
                $this->showLogin();
                return;
            }

            $controller = new $controllerClass();
            $controller->$method();
            return;
        }

        $this->notFound();
    }

    private function handleApiFileRoute(string $method, string $filePath): void
    {
        if (!$this->auth->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $controller = new FileController();

        if ($method === 'GET') {
            $controller->load($filePath);
        } elseif ($method === 'POST') {
            $controller->save($filePath);
        } else {
            $this->methodNotAllowed();
        }
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 Not Found';
    }

    private function methodNotAllowed(): void
    {
        http_response_code(405);
        echo '405 Method Not Allowed';
    }

    private function showLogin(): void
    {
        $controller = new AuthController();
        $controller->showLogin();
    }
}
