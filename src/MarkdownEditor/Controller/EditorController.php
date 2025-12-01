<?php

namespace MarkdownEditor\Controller;

use MarkdownEditor\Auth\SessionAuth;

class EditorController
{
    public function index(): void
    {
        $auth = new SessionAuth();
        $username = $auth->getUsername();

        require __DIR__ . '/../View/editor.php';
    }
}
