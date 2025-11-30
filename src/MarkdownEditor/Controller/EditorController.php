<?php

namespace MarkdownEditor\Controller;

class EditorController
{
    public function index(): void
    {
        require __DIR__ . '/../View/editor.php';
    }
}
