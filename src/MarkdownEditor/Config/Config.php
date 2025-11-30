<?php

namespace MarkdownEditor\Config;

class Config
{
    private static array $config = [];

    public static function load(string $envPath): void
    {
        if (!file_exists($envPath)) {
            throw new \RuntimeException("Configuration file not found: {$envPath}");
        }

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

        // Validate required configuration
        self::validate();
    }

    private static function validate(): void
    {
        $required = ['REPOS_PATH', 'PASSWORD_HASH'];

        foreach ($required as $key) {
            if (empty(self::$config[$key])) {
                throw new \RuntimeException("Required configuration missing: {$key}");
            }
        }

        // Validate repos path exists
        if (!is_dir(self::$config['REPOS_PATH'])) {
            throw new \RuntimeException("Repos directory does not exist: " . self::$config['REPOS_PATH']);
        }
    }

    public static function get(string $key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }

    public static function getReposPath(): string
    {
        return self::$config['REPOS_PATH'];
    }

    public static function getPasswordHash(): string
    {
        return self::$config['PASSWORD_HASH'];
    }
}
