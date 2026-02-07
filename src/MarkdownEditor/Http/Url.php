<?php

namespace MarkdownEditor\Http;

class Url
{
    public static function basePath(): string
    {
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
