<?php
/**
 * Simple Markdown Editor
 * A lightweight web-based markdown file editor
 */

// Configuration
define('REPOS_PATH', __DIR__ . '/repos');  // Path to your markdown repos
define('PASSWORD', 'HaveMore4un!');  // Change this!

// Simple authentication
session_start();
if (isset($_POST['password'])) {
    if ($_POST['password'] === PASSWORD) {
        $_SESSION['authenticated'] = true;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['authenticated'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Markdown Editor - Login</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background: #f5f5f5;
            }
            .login-box {
                background: white;
                padding: 2rem;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            input[type="password"] {
                padding: 0.5rem;
                font-size: 1rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                width: 250px;
            }
            button {
                padding: 0.5rem 1rem;
                font-size: 1rem;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin-left: 0.5rem;
            }
            button:hover {
                background: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>Markdown Editor</h2>
            <form method="post">
                <input type="password" name="password" placeholder="Password" autofocus>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'list') {
        // List all markdown files
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(REPOS_PATH, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['md', 'markdown'])) {
                $relativePath = str_replace(REPOS_PATH . '/', '', $file->getPathname());
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
        
        echo json_encode(['success' => true, 'files' => $files]);
        exit;
    }
    
    if ($_GET['action'] === 'load' && isset($_GET['file'])) {
        // Load file content
        $filePath = REPOS_PATH . '/' . $_GET['file'];
        
        // Security: prevent directory traversal
        $realPath = realpath($filePath);
        $realReposPath = realpath(REPOS_PATH);
        
        if ($realPath && $realReposPath && strpos($realPath, $realReposPath) === 0 && file_exists($filePath)) {
            $content = file_get_contents($filePath);
            echo json_encode(['success' => true, 'content' => $content]);
        } else {
            echo json_encode(['success' => false, 'error' => 'File not found or access denied']);
        }
        exit;
    }
    
    if ($_GET['action'] === 'save' && isset($_POST['file']) && isset($_POST['content'])) {
        // Save file content
        $filePath = REPOS_PATH . '/' . $_POST['file'];
        
        // Security: prevent directory traversal
        $realPath = realpath(dirname($filePath));
        $realReposPath = realpath(REPOS_PATH);
        
        if ($realPath && $realReposPath && strpos($realPath, $realReposPath) === 0) {
            if (file_put_contents($filePath, $_POST['content']) !== false) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to save file']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Access denied']);
        }
        exit;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown Editor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.25rem;
            font-weight: 500;
        }
        
        .header-right {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .current-file {
            font-size: 0.9rem;
            color: #ecf0f1;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .main {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        
        .sidebar {
            width: 300px;
            background: #ecf0f1;
            border-right: 1px solid #bdc3c7;
            overflow-y: auto;
            padding: 1rem;
        }
        
        .sidebar h3 {
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .file-list {
            list-style: none;
        }
        
        .file-group {
            margin-bottom: 1rem;
        }
        
        .file-group-title {
            font-weight: 600;
            color: #34495e;
            margin-bottom: 0.25rem;
            font-size: 0.85rem;
        }
        
        .file-item {
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background 0.2s;
            margin-bottom: 2px;
        }
        
        .file-item:hover {
            background: #d5dbdb;
        }
        
        .file-item.active {
            background: #3498db;
            color: white;
        }
        
        .editor-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .editor-toolbar {
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            gap: 0.5rem;
        }
        
        .editor-wrapper {
            flex: 1;
            overflow: hidden;
            padding: 1rem;
        }
        
        .CodeMirror {
            height: 100% !important;
            font-size: 14px;
        }
        
        .no-file-selected {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .status-message {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
        }
        
        .status-message.success {
            background: #27ae60;
            color: white;
        }
        
        .status-message.error {
            background: #e74c3c;
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📝 Markdown Editor</h1>
        <div class="header-right">
            <span class="current-file" id="currentFile">No file selected</span>
            <button class="btn btn-secondary" onclick="location.href='?logout=1'">Logout</button>
        </div>
    </div>
    
    <div class="main">
        <div class="sidebar">
            <h3>Files</h3>
            <div id="fileList">Loading...</div>
        </div>
        
        <div class="editor-container">
            <div class="editor-toolbar">
                <button class="btn btn-success" id="saveBtn" disabled>💾 Save</button>
                <button class="btn btn-primary" id="refreshBtn">🔄 Refresh Files</button>
            </div>
            <div class="editor-wrapper">
                <div id="noFileSelected" class="no-file-selected">
                    Select a file from the sidebar to begin editing
                </div>
                <textarea id="editor" style="display: none;"></textarea>
            </div>
        </div>
    </div>
    
    <div class="status-message" id="statusMessage"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked@9.1.6/marked.min.js"></script>
    <script>
        let editor = null;
        let currentFile = null;
        let originalContent = '';
        
        // Initialize EasyMDE
        function initEditor() {
            editor = new EasyMDE({
                element: document.getElementById('editor'),
                autosave: {
                    enabled: false
                },
                spellChecker: false,
                status: ['lines', 'words', 'cursor'],
                toolbar: [
                    'bold', 'italic', 'heading', '|',
                    'quote', 'unordered-list', 'ordered-list', '|',
                    'link', 'image', '|',
                    'preview', 'side-by-side', 'fullscreen', '|',
                    'guide'
                ]
            });
            
            editor.codemirror.on('change', function() {
                checkForChanges();
            });
        }
        
        // Load file list
        function loadFileList() {
            fetch('?action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayFileList(data.files);
                    }
                })
                .catch(error => {
                    console.error('Error loading files:', error);
                    document.getElementById('fileList').innerHTML = '<p style="color: red;">Error loading files</p>';
                });
        }
        
        // Display file list grouped by directory
        function displayFileList(files) {
            const fileList = document.getElementById('fileList');
            
            if (files.length === 0) {
                fileList.innerHTML = '<p style="color: #7f8c8d;">No markdown files found</p>';
                return;
            }
            
            // Group files by directory
            const grouped = {};
            files.forEach(file => {
                if (!grouped[file.dir]) {
                    grouped[file.dir] = [];
                }
                grouped[file.dir].push(file);
            });
            
            // Build HTML
            let html = '<div class="file-list">';
            Object.keys(grouped).sort().forEach(dir => {
                html += '<div class="file-group">';
                html += `<div class="file-group-title">${dir === '.' ? 'Root' : dir}</div>`;
                grouped[dir].forEach(file => {
                    const active = currentFile === file.path ? 'active' : '';
                    html += `<div class="file-item ${active}" onclick="loadFile('${file.path}')">${file.name}</div>`;
                });
                html += '</div>';
            });
            html += '</div>';
            
            fileList.innerHTML = html;
        }
        
        // Load file content
        function loadFile(filePath) {
            if (editor && hasUnsavedChanges()) {
                if (!confirm('You have unsaved changes. Do you want to discard them?')) {
                    return;
                }
            }
            
            fetch(`?action=load&file=${encodeURIComponent(filePath)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentFile = filePath;
                        originalContent = data.content;
                        
                        if (!editor) {
                            document.getElementById('noFileSelected').style.display = 'none';
                            document.getElementById('editor').style.display = 'block';
                            initEditor();
                        }
                        
                        editor.value(data.content);
                        document.getElementById('currentFile').textContent = filePath;
                        document.getElementById('saveBtn').disabled = true;
                        
                        // Update active state in file list
                        document.querySelectorAll('.file-item').forEach(item => {
                            item.classList.remove('active');
                        });
                        event.target.classList.add('active');
                    } else {
                        showStatus('Error loading file: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showStatus('Error loading file', 'error');
                });
        }
        
        // Save file
        function saveFile() {
            if (!currentFile) return;
            
            const content = editor.value();
            const formData = new FormData();
            formData.append('file', currentFile);
            formData.append('content', content);
            
            fetch('?action=save', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        originalContent = content;
                        document.getElementById('saveBtn').disabled = true;
                        showStatus('File saved successfully!', 'success');
                    } else {
                        showStatus('Error saving file: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showStatus('Error saving file', 'error');
                });
        }
        
        // Check for unsaved changes
        function hasUnsavedChanges() {
            if (!editor || !currentFile) return false;
            return editor.value() !== originalContent;
        }
        
        function checkForChanges() {
            if (currentFile) {
                document.getElementById('saveBtn').disabled = !hasUnsavedChanges();
            }
        }
        
        // Show status message
        function showStatus(message, type) {
            const statusEl = document.getElementById('statusMessage');
            statusEl.textContent = message;
            statusEl.className = 'status-message ' + type;
            statusEl.style.display = 'block';
            
            setTimeout(() => {
                statusEl.style.display = 'none';
            }, 3000);
        }
        
        // Event listeners
        document.getElementById('saveBtn').addEventListener('click', saveFile);
        document.getElementById('refreshBtn').addEventListener('click', loadFileList);
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+S or Cmd+S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                if (!document.getElementById('saveBtn').disabled) {
                    saveFile();
                }
            }
        });
        
        // Warn before leaving with unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (hasUnsavedChanges()) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // Load file list on page load
        loadFileList();
    </script>
</body>
</html>
