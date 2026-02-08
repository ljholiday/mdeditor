<?php

namespace MarkdownEditor\Controller;

use MarkdownEditor\Auth\SessionAuth;
use MarkdownEditor\Config\Config;

class EditorController
{
    public function index(): void
    {
        $auth = new SessionAuth();
        $username = $auth->getUsername();
        $authEnabled = $auth->isAuthEnabled();
        $rootLabel = basename(Config::getReposPath()) ?: 'Root';

        require __DIR__ . '/../View/editor.php';
    }
}
