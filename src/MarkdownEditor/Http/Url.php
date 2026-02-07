<?php

namespace MarkdownEditor\Http;

use MarkdownEditor\Config\Config;

class Url
{
    public static function basePath(): string
    {
        $override = Config::get('BASE_PATH');
        if (!empty($override)) {
            $override = '/' . trim($override, '/');
            return $override === '/' ? '' : $override;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (!empty($uri)) {
            $path = $uri;
            if (false !== $pos = strpos($path, '?')) {
                $path = substr($path, 0, $pos);
            }

            $segments = array_values(array_filter(explode('/', trim($path, '/'))));
            if (!empty($segments)) {
                $last = $segments[count($segments) - 1];
                if ($last === 'public' || str_ends_with($last, '.php')) {
                    self::failFastBasePath($path);
                }
            }
        }

        $phpSelf = $_SERVER['PHP_SELF'] ?? '';
        if (!empty($phpSelf)) {
            $dir = rtrim(str_replace('\\', '/', dirname($phpSelf)), '/');
            if ($dir !== '/' && $dir !== '.') {
                return $dir;
            }
        }

        $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';

        if ($scriptFilename && $docRoot && strpos($scriptFilename, $docRoot) === 0) {
            $relative = substr(dirname($scriptFilename), strlen($docRoot));
            $relative = rtrim(str_replace('\\', '/', $relative), '/');
            return $relative === '' ? '' : $relative;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName) {
            $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
            return $dir === '/' ? '' : $dir;
        }

        return '';
    }

    private static function failFastBasePath(string $path): void
    {
        $doc = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
        http_response_code(500);
        echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Markdown Editor - Configuration Error</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f8f8; padding: 2rem; }
        .box { max-width: 720px; margin: 0 auto; background: #fff; padding: 1.5rem; border: 1px solid #ddd; }
        h1 { margin-top: 0; font-size: 1.25rem; color: #b00020; }
        code { background: #f1f1f1; padding: 0.2rem 0.4rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Configuration Error</h1>
        <p>This app appears to be served from a subdirectory (<code>{$doc}</code>).</p>
        <p>Please set <code>BASE_PATH</code> in your <code>.env</code> file. Example:</p>
        <p><code>BASE_PATH=/mdeditor/public</code></p>
    </div>
</body>
</html>
HTML;
        exit;
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
