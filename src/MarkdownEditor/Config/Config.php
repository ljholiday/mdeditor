<?php

namespace MarkdownEditor\Config;

class Config
{
    private static array $config = [];

    public static function load(string $envPath): void
    {
        // .env file is optional - load if it exists
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // Parse KEY=VALUE
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    self::$config[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }

        // Validate and setup defaults
        self::validate();
    }

    private static function validate(): void
    {
        // Get repos path (will use default if not set)
        $reposPath = self::getReposPath();

        // Create repos directory if it doesn't exist
        if (!is_dir($reposPath)) {
            mkdir($reposPath, 0755, true);
        }
    }

    public static function get(string $key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }

    public static function getReposPath(): string
    {
        $path = self::$config['REPOS_PATH'] ?? '';

        // If REPOS_PATH is not set or is relative, use default
        if (empty($path) || $path[0] !== '/') {
            return dirname(__DIR__, 3) . '/repos';
        }

        return $path;
    }

    public static function getAdminUsername(): ?string
    {
        return self::$config['ADMIN_USERNAME'] ?? null;
    }

    public static function getAdminPassword(): ?string
    {
        return self::$config['ADMIN_PASSWORD'] ?? null;
    }
}
