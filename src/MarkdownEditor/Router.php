<?php

namespace MarkdownEditor;

use FastRoute\RouteCollector;
use MarkdownEditor\Auth\SessionAuth;
use MarkdownEditor\Controller\AuthController;
use MarkdownEditor\Controller\EditorController;
use MarkdownEditor\Controller\FileController;

class Router
{
    private SessionAuth $auth;

    public function __construct()
    {
        $this->auth = new SessionAuth();
    }

    public function dispatch(): void
    {
        $dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            // Public routes (authentication not required)
            $r->addRoute('GET', '/register', [AuthController::class, 'showRegister']);
            $r->addRoute('POST', '/register', [AuthController::class, 'register']);
            $r->addRoute('POST', '/login', [AuthController::class, 'login']);
            $r->addRoute('GET', '/forgot-password', [AuthController::class, 'showForgotPassword']);
            $r->addRoute('POST', '/forgot-password', [AuthController::class, 'forgotPassword']);
            $r->addRoute('GET', '/reset-password', [AuthController::class, 'showResetPassword']);
            $r->addRoute('POST', '/reset-password', [AuthController::class, 'resetPassword']);

            // Protected routes (require authentication)
            $r->addRoute('GET', '/', [EditorController::class, 'index']);
            $r->addRoute('GET', '/logout', [AuthController::class, 'logout']);
            $r->addRoute('GET', '/account-settings', [AuthController::class, 'showAccountSettings']);
            $r->addRoute('POST', '/account-settings', [AuthController::class, 'updateAccountSettings']);
            $r->addRoute('GET', '/change-password', [AuthController::class, 'showChangePassword']);
            $r->addRoute('POST', '/change-password', [AuthController::class, 'changePassword']);
            $r->addRoute('GET', '/api/files', [FileController::class, 'list']);
            $r->addRoute('GET', '/api/files/{path:.+}', [FileController::class, 'load']);
            $r->addRoute('POST', '/api/files/{path:.+}', [FileController::class, 'save']);
        });

        // Get the request URI and method
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Parse the URI - handle subdirectory deployment
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

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                $this->notFound();
                break;

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $this->methodNotAllowed();
                break;

            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                // List of routes that don't require authentication
                $publicRoutes = ['login', 'register', 'showRegister', 'showForgotPassword', 'forgotPassword', 'showResetPassword', 'resetPassword'];

                // Check authentication for protected routes
                if (!in_array($handler[1], $publicRoutes)) {
                    if (!$this->auth->isAuthenticated()) {
                        $this->showLogin();
                        return;
                    }
                }

                // Call the controller method
                $controller = new $handler[0]();
                call_user_func_array([$controller, $handler[1]], $vars);
                break;
        }
    }

    private function notFound(): void
    {
        http_response_code(404);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
        echo "404 Not Found - $method $uri";
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
