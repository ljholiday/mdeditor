<?php

namespace MarkdownEditor\Auth;

use MarkdownEditor\Config\Config;

class SessionAuth
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    public function login(string $password): bool
    {
        $passwordHash = Config::getPasswordHash();

        if (password_verify($password, $passwordHash)) {
            $_SESSION['authenticated'] = true;
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        session_destroy();
    }
}
