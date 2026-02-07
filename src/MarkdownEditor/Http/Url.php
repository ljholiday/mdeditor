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

        $phpSelf = $_SERVER['PHP_SELF'] ?? '';
        if (!empty($phpSelf)) {
            $dir = rtrim(str_replace('\\', '/', dirname($phpSelf)), '/');
            if ($dir !== '/' && $dir !== '.') {
                return $dir;
            }
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (!empty($requestUri)) {
            $path = $requestUri;
            if (false !== $pos = strpos($path, '?')) {
                $path = substr($path, 0, $pos);
            }
            $path = rtrim(str_replace('\\', '/', $path), '/');
            $publicPos = strpos($path, '/public');
            if ($publicPos !== false) {
                $base = substr($path, 0, $publicPos + strlen('/public'));
                return $base === '' ? '' : $base;
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
