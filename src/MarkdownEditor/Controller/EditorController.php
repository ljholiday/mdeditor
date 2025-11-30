<?php

namespace MarkdownEditor\Controller;

use MarkdownEditor\Auth\SessionAuth;

class EditorController
{
    public function index(): void
    {
        $auth = new SessionAuth();
        $username = $auth->getCurrentUsername();

        require __DIR__ . '/../View/editor.php';
    }
}
