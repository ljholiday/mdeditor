<?php

namespace MarkdownEditor\Auth;

use MarkdownEditor\Config\Config;

class SessionAuth
{
    private bool $authEnabled;

    public function __construct()
    {
        $this->authEnabled = $this->hasCredentials();
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

    private function hasCredentials(): bool
    {
        $adminUsername = Config::getAdminUsername();
        $adminPassword = Config::getAdminPassword();

        return !empty($adminUsername) && !empty($adminPassword);
    }

    public function isAuthEnabled(): bool
    {
        return $this->authEnabled;
    }

    public function isAuthenticated(): bool
    {
        if (!$this->authEnabled) {
            return true;
        }
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    public function login(string $username, string $password): bool
    {
        if (!$this->authEnabled) {
            return false;
        }

        $adminUsername = Config::getAdminUsername();
        $adminPassword = Config::getAdminPassword();

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
