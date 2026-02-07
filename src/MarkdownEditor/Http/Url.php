<?php

namespace MarkdownEditor\Http;

use MarkdownEditor\Config\Config;

class Url
{
    public static function basePath(): string
    {
        $override = Config::get('BASE_PATH');
        if (empty($override)) {
            http_response_code(500);
            echo 'Configuration error: BASE_PATH is required. Set BASE_PATH=/mdeditor/public in .env';
            exit;
        }

        $override = '/' . trim($override, '/');
        return $override === '/' ? '' : $override;
    }

    public static function absolute(string $path): string
    {
        $base = self::basePath();
        $path = '/' . ltrim($path, '/');

        $host = $_SERVER['HTTP_HOST'] ?? '';
        $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ($_SERVER['HTTPS'] ?? '');
        $scheme = (!empty($proto) && $proto !== 'off') ? 'https' : 'http';

        if ($host === '') {
            return $base . $path;
        }

        return $scheme . '://' . $host . $base . $path;
    }

    public static function stripBasePath(string $path): string
    {
        $base = self::basePath();
        if ($base !== '' && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
            if ($path === '') {
                $path = '/';
            }
        }

        return $path;
    }
}
