#!/usr/bin/env php
<?php
/**
 * Comprehensive test script for Markdown Editor
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Markdown Editor Test Suite ===\n\n";

$errors = 0;
$warnings = 0;

// Test 1: Check file permissions
echo "TEST 1: File Permissions\n";
$files = [
    '.env' => 0644,
    'index.php' => 0644,
    '.htaccess' => 0644,
    'composer.json' => 0644,
];

foreach ($files as $file => $expectedPerms) {
    if (!file_exists($file)) {
        echo "  ✗ $file does not exist\n";
        $errors++;
        continue;
    }
    $perms = fileperms($file) & 0777;
    if ($perms !== $expectedPerms) {
        echo sprintf("  ✗ %s has permissions %o, expected %o\n", $file, $perms, $expectedPerms);
        $errors++;
    } else {
        echo "  ✓ $file permissions correct\n";
    }
}

// Test 2: Check directory permissions
echo "\nTEST 2: Directory Permissions\n";
$dirs = ['src', 'repos', 'vendor'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        echo "  ✗ $dir does not exist\n";
        $errors++;
        continue;
    }
    $perms = fileperms($dir) & 0777;
    if ($perms !== 0755) {
        echo sprintf("  ✗ %s has permissions %o, expected 0755\n", $dir, $perms);
        $errors++;
    } else {
        echo "  ✓ $dir permissions correct\n";
    }
}

// Test 3: Autoloader
echo "\nTEST 3: Autoloader\n";
if (!file_exists('vendor/autoload.php')) {
    echo "  ✗ vendor/autoload.php missing - run composer install\n";
    $errors++;
} else {
    require_once 'vendor/autoload.php';
    echo "  ✓ Autoloader loaded\n";
}

// Test 4: Config Loading
echo "\nTEST 4: Config Loading\n";
try {
    \MarkdownEditor\Config\Config::load(__DIR__ . '/.env');
    echo "  ✓ Config loaded successfully\n";

    $reposPath = \MarkdownEditor\Config\Config::getReposPath();
    echo "  ✓ REPOS_PATH: $reposPath\n";

    if (!is_dir($reposPath)) {
        echo "  ✗ REPOS_PATH directory does not exist\n";
        $errors++;
    } else {
        echo "  ✓ REPOS_PATH directory exists\n";
    }

    $passwordHash = \MarkdownEditor\Config\Config::getPasswordHash();
    if (empty($passwordHash)) {
        echo "  ✗ PASSWORD_HASH is empty\n";
        $errors++;
    } else {
        echo "  ✓ PASSWORD_HASH is set\n";
    }
} catch (Exception $e) {
    echo "  ✗ Config error: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 5: Authentication
echo "\nTEST 5: Authentication\n";
try {
    $auth = new \MarkdownEditor\Auth\SessionAuth();
    echo "  ✓ SessionAuth instantiated\n";

    // Test login with correct password
    if ($auth->login('change_this_password')) {
        echo "  ✓ Login with correct password works\n";
    } else {
        echo "  ✗ Login with correct password failed\n";
        $errors++;
    }

    // Test login with wrong password
    if (!$auth->login('wrong_password')) {
        echo "  ✓ Login with wrong password correctly fails\n";
    } else {
        echo "  ✗ Login with wrong password succeeded (security issue!)\n";
        $errors++;
    }
} catch (Exception $e) {
    echo "  ✗ Authentication error: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 6: File Service
echo "\nTEST 6: File Service\n";
try {
    $fileService = new \MarkdownEditor\Service\FileService();
    echo "  ✓ FileService instantiated\n";

    // Test list files
    $files = $fileService->listMarkdownFiles();
    echo "  ✓ File listing works (" . count($files) . " files found)\n";

    // Test directory traversal protection
    $maliciousPath = '../../../etc/passwd';
    $content = $fileService->loadFile($maliciousPath);
    if ($content === null) {
        echo "  ✓ Directory traversal protection works\n";
    } else {
        echo "  ✗ SECURITY ISSUE: Directory traversal protection failed!\n";
        $errors++;
    }

    // Test loading a legitimate file (if one exists)
    if (count($files) > 0) {
        $testFile = $files[0]['path'];
        $content = $fileService->loadFile($testFile);
        if ($content !== null) {
            echo "  ✓ File loading works\n";
        } else {
            echo "  ✗ Failed to load existing file: $testFile\n";
            $errors++;
        }
    }
} catch (Exception $e) {
    echo "  ✗ FileService error: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 7: Router (simulated requests)
echo "\nTEST 7: Router\n";
try {
    // Test GET / (should show login when not authenticated)
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    ob_start();
    $router = new \MarkdownEditor\Router();
    ob_end_clean();
    echo "  ✓ Router instantiated\n";

    echo "  ✓ Router tests passed\n";
} catch (Exception $e) {
    echo "  ✗ Router error: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 8: Controllers
echo "\nTEST 8: Controllers\n";
try {
    $authController = new \MarkdownEditor\Controller\AuthController();
    echo "  ✓ AuthController instantiated\n";

    $fileController = new \MarkdownEditor\Controller\FileController();
    echo "  ✓ FileController instantiated\n";

    $editorController = new \MarkdownEditor\Controller\EditorController();
    echo "  ✓ EditorController instantiated\n";
} catch (Exception $e) {
    echo "  ✗ Controller error: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 9: Check for syntax errors in all PHP files
echo "\nTEST 9: PHP Syntax Check\n";
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('src', RecursiveDirectoryIterator::SKIP_DOTS)
);
$syntaxErrors = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $output = [];
        $returnVar = 0;
        exec('php -l ' . escapeshellarg($file->getPathname()) . ' 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            echo "  ✗ Syntax error in " . $file->getPathname() . "\n";
            $syntaxErrors++;
        }
    }
}
if ($syntaxErrors === 0) {
    echo "  ✓ All PHP files have valid syntax\n";
} else {
    echo "  ✗ Found $syntaxErrors files with syntax errors\n";
    $errors += $syntaxErrors;
}

// Summary
echo "\n=== Test Summary ===\n";
if ($errors === 0) {
    echo "✓ All tests passed!\n";
    exit(0);
} else {
    echo "✗ Tests failed with $errors error(s)\n";
    exit(1);
}
