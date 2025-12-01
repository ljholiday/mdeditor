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
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        require __DIR__ . '/../View/login.php';
    }

    public function login(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($this->auth->login($username, $password)) {
            $this->redirect('/');
        } else {
            $_SESSION['login_error'] = 'Invalid username or password';
            $this->redirect('/');
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect('/');
    }

    private function redirect(string $path): void
    {
        $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        $baseUrl = $baseUrl === '/' ? '' : $baseUrl;
        header('Location: ' . $baseUrl . $path);
        exit;
    }
}
