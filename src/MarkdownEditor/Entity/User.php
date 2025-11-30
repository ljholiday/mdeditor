<?php

namespace MarkdownEditor\Entity;

class User
{
    private string $username;
    private string $email;
    private string $passwordHash;
    private string $createdAt;
    private ?string $resetToken = null;
    private ?string $resetTokenExpiry = null;

    public function __construct(
        string $username,
        string $email,
        string $passwordHash,
        ?string $createdAt = null,
        ?string $resetToken = null,
        ?string $resetTokenExpiry = null
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->resetToken = $resetToken;
        $this->resetTokenExpiry = $resetTokenExpiry;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function getResetTokenExpiry(): ?string
    {
        return $this->resetTokenExpiry;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function setResetToken(string $token, string $expiry): void
    {
        $this->resetToken = $token;
        $this->resetTokenExpiry = $expiry;
    }

    public function clearResetToken(): void
    {
        $this->resetToken = null;
        $this->resetTokenExpiry = null;
    }

    public function isResetTokenValid(string $token): bool
    {
        if ($this->resetToken === null || $this->resetTokenExpiry === null) {
            return false;
        }

        if ($this->resetToken !== $token) {
            return false;
        }

        // Check if token has expired
        $expiryTime = strtotime($this->resetTokenExpiry);
        return $expiryTime > time();
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'created_at' => $this->createdAt,
            'reset_token' => $this->resetToken,
            'reset_token_expiry' => $this->resetTokenExpiry
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['username'],
            $data['email'] ?? '',
            $data['password_hash'],
            $data['created_at'] ?? null,
            $data['reset_token'] ?? null,
            $data['reset_token_expiry'] ?? null
        );
    }
}
