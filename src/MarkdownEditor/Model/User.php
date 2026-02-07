<?php

namespace MarkdownEditor\Model;

class User
{
    private string $username;
    private ?string $email;
    private ?string $createdAt;
    private ?string $passwordHash;

    public function __construct(string $username, ?string $email, ?string $createdAt, ?string $passwordHash)
    {
        $this->username = $username;
        $this->email = $email;
        $this->createdAt = $createdAt;
        $this->passwordHash = $passwordHash;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }
}
