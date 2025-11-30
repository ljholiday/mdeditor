<?php

namespace MarkdownEditor\Auth;

use MarkdownEditor\Service\UserService;

class SessionAuth
{
    private UserService $userService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->userService = new UserService();
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['username']) && !empty($_SESSION['username']);
    }

    public function getCurrentUsername(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->userService->authenticate($username, $password);

        if ($user !== null) {
            $_SESSION['username'] = $user->getUsername();
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        session_destroy();
    }
}
