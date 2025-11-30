<?php

namespace MarkdownEditor\Controller;

use MarkdownEditor\Service\FileService;

class FileController
{
    private FileService $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    /**
     * List all markdown files
     */
    public function list(): void
    {
        header('Content-Type: application/json');

        $files = $this->fileService->listMarkdownFiles();

        echo json_encode([
            'success' => true,
            'files' => $files
        ]);
    }

    /**
     * Load a specific file
     *
     * @param string $path Relative path to the file
     */
    public function load(string $path): void
    {
        header('Content-Type: application/json');

        $content = $this->fileService->loadFile($path);

        if ($content === null) {
            echo json_encode([
                'success' => false,
                'error' => 'File not found or access denied'
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'content' => $content
        ]);
    }

    /**
     * Save a file
     *
     * @param string $path Relative path to the file
     */
    public function save(string $path): void
    {
        header('Content-Type: application/json');

        // Get the content from POST data
        $content = $_POST['content'] ?? file_get_contents('php://input');

        if ($content === null || $content === false) {
            echo json_encode([
                'success' => false,
                'error' => 'No content provided'
            ]);
            return;
        }

        $success = $this->fileService->saveFile($path, $content);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to save file or access denied'
            ]);
        }
    }
}
