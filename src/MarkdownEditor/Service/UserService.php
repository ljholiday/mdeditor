<?php

namespace MarkdownEditor\Service;

use MarkdownEditor\Model\User;

class UserService
{
    private string $usersFile;

    public function __construct()
    {
        $this->usersFile = dirname(__DIR__, 3) . '/users.json';
    }

    public function findByUsername(string $username): ?User
    {
        $data = $this->loadUsers();
        foreach ($data as $userData) {
            if (($userData['username'] ?? '') === $username) {
                return new User(
                    $userData['username'],
                    $userData['email'] ?? null,
                    $userData['created_at'] ?? null,
                    $userData['password_hash'] ?? null
                );
            }
        }

        return null;
    }

    public function updateEmail(string $username, string $email): bool
    {
        $data = $this->loadUsers();

        foreach ($data as $userData) {
            if (!empty($userData['email']) && $userData['email'] === $email && ($userData['username'] ?? '') !== $username) {
                return false;
            }
        }

        $updated = false;
        foreach ($data as &$userData) {
            if (($userData['username'] ?? '') === $username) {
                $userData['email'] = $email;
                $updated = true;
                break;
            }
        }
        unset($userData);

        if (!$updated) {
            return false;
        }

        return $this->saveUsers($data);
    }

    public function updatePassword(string $username, string $password): bool
    {
        $data = $this->loadUsers();
        $updated = false;
        foreach ($data as &$userData) {
            if (($userData['username'] ?? '') === $username) {
                $userData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                $updated = true;
                break;
            }
        }
        unset($userData);

        if (!$updated) {
            return false;
        }

        return $this->saveUsers($data);
    }

    private function loadUsers(): array
    {
        if (!file_exists($this->usersFile)) {
            return [];
        }

        $content = file_get_contents($this->usersFile);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    private function saveUsers(array $data): bool
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        if ($json === false) {
            return false;
        }

        return file_put_contents($this->usersFile, $json) !== false;
    }
}
