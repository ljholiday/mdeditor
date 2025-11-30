<?php

namespace MarkdownEditor\Service;

use MarkdownEditor\Config\Config;

class FileService
{
    private string $reposPath;

    public function __construct()
    {
        $this->reposPath = Config::getReposPath();
    }

    /**
     * List all markdown files recursively
     *
     * @return array
     */
    public function listMarkdownFiles(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->reposPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['md', 'markdown'])) {
                $relativePath = str_replace($this->reposPath . '/', '', $file->getPathname());
                $files[] = [
                    'path' => $relativePath,
                    'name' => $file->getFilename(),
                    'dir' => dirname($relativePath),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime()
                ];
            }
        }

        // Sort by directory, then filename
        usort($files, function($a, $b) {
            $dirCmp = strcmp($a['dir'], $b['dir']);
            return $dirCmp !== 0 ? $dirCmp : strcmp($a['name'], $b['name']);
        });

        return $files;
    }

    /**
     * Load file content with security validation
     *
     * @param string $relativePath
     * @return string|null File content or null if invalid/not found
     */
    public function loadFile(string $relativePath): ?string
    {
        $filePath = $this->validatePath($relativePath);

        if ($filePath === null || !file_exists($filePath)) {
            return null;
        }

        return file_get_contents($filePath);
    }

    /**
     * Save file content with security validation
     *
     * @param string $relativePath
     * @param string $content
     * @return bool Success status
     */
    public function saveFile(string $relativePath, string $content): bool
    {
        $filePath = $this->validatePath($relativePath);

        if ($filePath === null) {
            return false;
        }

        // Create directory if it doesn't exist
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($filePath, $content) !== false;
    }

    /**
     * Validate file path to prevent directory traversal attacks
     * This preserves the security logic from the original index.php lines 124-127 and 141-144
     *
     * @param string $relativePath
     * @return string|null Validated absolute path or null if invalid
     */
    private function validatePath(string $relativePath): ?string
    {
        $filePath = $this->reposPath . '/' . $relativePath;

        // Security: prevent directory traversal
        // Get the real path of the directory (file may not exist yet for saves)
        $realPath = realpath(dirname($filePath));
        $realReposPath = realpath($this->reposPath);

        // Validate that the real path exists and is within the repos directory
        if (!$realPath || !$realReposPath || strpos($realPath, $realReposPath) !== 0) {
            return null;
        }

        // Reconstruct the full path with the filename
        return $realPath . '/' . basename($filePath);
    }
}
