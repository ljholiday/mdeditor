<?php

namespace MarkdownEditor\Controller;

use MarkdownEditor\Auth\SessionAuth;

class AuthController
{
    private SessionAuth $auth;

    public function __construct()
    {
        $this->auth = new SessionAuth();
    }

    public function showLogin(): void
    {
        require __DIR__ . '/../View/login.php';
    }

    public function login(): void
    {
        if (!isset($_POST['password'])) {
            http_response_code(400);
            echo 'Password required';
            return;
        }

        if ($this->auth->login($_POST['password'])) {
            // Get the base URL for redirect
            $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
            $baseUrl = $baseUrl === '/' ? '' : $baseUrl;
            header('Location: ' . $baseUrl . '/');
            exit;
        } else {
            // Redirect back to login with error
            $this->showLogin();
        }
    }

    public function logout(): void
    {
        $this->auth->logout();

        // Get the base URL for redirect
        $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        $baseUrl = $baseUrl === '/' ? '' : $baseUrl;
        header('Location: ' . $baseUrl . '/');
        exit;
    }
}
