<?php

namespace MarkdownEditor\Auth;

use MarkdownEditor\Config\Config;

class SessionAuth
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $lifetime = 60 * 60 * 24 * 30;
            ini_set('session.gc_maxlifetime', (string)$lifetime);
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    public function login(string $username, string $password): bool
    {
        $adminUsername = Config::getAdminUsername();
        $adminPassword = Config::getAdminPassword();

        if (!$adminUsername || !$adminPassword) {
            return false;
        }

        if ($username === $adminUsername && $password === $adminPassword) {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public function getUsername(): ?string
    {
        return $_SESSION['username'] ?? null;
    }
}
