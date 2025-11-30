<?php

namespace MarkdownEditor\Service;

use MarkdownEditor\Entity\User;

class UserService
{
    private string $usersFile;

    public function __construct()
    {
        $this->usersFile = __DIR__ . '/../../../users.json';
    }

    private function loadUsers(): array
    {
        if (!file_exists($this->usersFile)) {
            return [];
        }

        $content = file_get_contents($this->usersFile);
        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return [];
        }

        $users = [];
        foreach ($data as $userData) {
            $users[$userData['username']] = User::fromArray($userData);
        }

        return $users;
    }

    private function saveUsers(array $users): bool
    {
        $data = [];
        foreach ($users as $user) {
            $data[] = $user->toArray();
        }

        $json = json_encode($data, JSON_PRETTY_PRINT);
        return file_put_contents($this->usersFile, $json) !== false;
    }

    public function findByUsername(string $username): ?User
    {
        $users = $this->loadUsers();
        return $users[$username] ?? null;
    }

    public function findByEmail(string $email): ?User
    {
        $users = $this->loadUsers();
        foreach ($users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }

    public function createUser(string $username, string $email, string $password): bool
    {
        // Validate username
        if (empty($username) || strlen($username) < 3) {
            return false;
        }

        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Validate password
        if (empty($password) || strlen($password) < 8) {
            return false;
        }

        $users = $this->loadUsers();

        // Check if username already exists
        if (isset($users[$username])) {
            return false;
        }

        // Check if email already exists
        foreach ($users as $existingUser) {
            if ($existingUser->getEmail() === $email) {
                return false;
            }
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($username, $email, $passwordHash);

        $users[$username] = $user;
        return $this->saveUsers($users);
    }

    public function updatePassword(string $username, string $newPassword): bool
    {
        if (empty($newPassword) || strlen($newPassword) < 8) {
            return false;
        }

        $users = $this->loadUsers();

        if (!isset($users[$username])) {
            return false;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $users[$username]->setPasswordHash($passwordHash);

        return $this->saveUsers($users);
    }

    public function updateEmail(string $username, string $newEmail): bool
    {
        if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $users = $this->loadUsers();

        if (!isset($users[$username])) {
            return false;
        }

        // Check if email is already in use by another user
        foreach ($users as $existingUsername => $existingUser) {
            if ($existingUsername !== $username && $existingUser->getEmail() === $newEmail) {
                return false;
            }
        }

        // Update the user's email via reflection since we don't have a setter
        $reflection = new \ReflectionClass($users[$username]);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($users[$username], $newEmail);

        return $this->saveUsers($users);
    }

    public function authenticate(string $username, string $password): ?User
    {
        $user = $this->findByUsername($username);

        if ($user === null) {
            return null;
        }

        if ($user->verifyPassword($password)) {
            return $user;
        }

        return null;
    }

    public function hasUsers(): bool
    {
        $users = $this->loadUsers();
        return count($users) > 0;
    }

    public function initiatePasswordReset(string $email): ?array
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            return null;
        }

        // Generate secure reset token
        $token = bin2hex(random_bytes(32));

        // Token expires in 1 hour
        $expiry = date('Y-m-d H:i:s', time() + 3600);

        $users = $this->loadUsers();
        $users[$user->getUsername()]->setResetToken($token, $expiry);

        if ($this->saveUsers($users)) {
            return [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'token' => $token
            ];
        }

        return null;
    }

    public function resetPasswordWithToken(string $token, string $newPassword): bool
    {
        if (empty($newPassword) || strlen($newPassword) < 8) {
            return false;
        }

        $users = $this->loadUsers();

        foreach ($users as $username => $user) {
            if ($user->isResetTokenValid($token)) {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $users[$username]->setPasswordHash($passwordHash);
                $users[$username]->clearResetToken();

                return $this->saveUsers($users);
            }
        }

        return false;
    }

    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        $scriptPath = $scriptPath === '/' ? '' : $scriptPath;
        $resetUrl = $baseUrl . $scriptPath . '/reset-password?token=' . urlencode($token);

        $subject = 'Password Reset - Markdown Editor';
        $message = "Hello,\n\n";
        $message .= "You requested a password reset for your Markdown Editor account.\n\n";
        $message .= "Click the link below to reset your password:\n";
        $message .= $resetUrl . "\n\n";
        $message .= "This link will expire in 1 hour.\n\n";
        $message .= "If you did not request this reset, please ignore this email.\n\n";
        $message .= "Thanks,\n";
        $message .= "Markdown Editor";

        $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "Reply-To: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        return mail($email, $subject, $message, $headers);
    }
}
