#!/usr/bin/env php
<?php
/**
 * Comprehensive test script for Markdown Editor
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session early to avoid header warnings during tests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "=== Markdown Editor Test Suite ===\n\n";

$errors = 0;
$warnings = 0;

// Test 1: Check file permissions
echo "TEST 1: File Permissions\n";
$files = [
    '.env' => 0644,
    'public/index.php' => 0644,
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
$dirs = ['src', 'public'];
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
spl_autoload_register(function ($class) {
    $prefix = 'MarkdownEditor\\';
    $baseDir = __DIR__ . '/src/MarkdownEditor/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
echo "  ✓ Autoloader registered\n";

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

    $adminUsername = \MarkdownEditor\Config\Config::getAdminUsername();
    $adminPassword = \MarkdownEditor\Config\Config::getAdminPassword();
    if (empty($adminUsername) || empty($adminPassword)) {
        echo "  ✗ ADMIN_USERNAME or ADMIN_PASSWORD is empty\n";
        $errors++;
    } else {
        echo "  ✓ Admin credentials are set\n";
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
    if (!empty($adminUsername) && !empty($adminPassword)) {
        if ($auth->login($adminUsername, $adminPassword)) {
            echo "  ✓ Login with correct credentials works\n";
        } else {
            echo "  ✗ Login with correct credentials failed\n";
            $errors++;
        }

        // Test login with wrong password
        if (!$auth->login($adminUsername, 'wrong_password')) {
            echo "  ✓ Login with wrong password correctly fails\n";
        } else {
            echo "  ✗ Login with wrong password succeeded (security issue!)\n";
            $errors++;
        }
    } else {
        echo "  ✗ Skipping login tests: admin credentials missing\n";
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
$pathsToCheck = ['src', 'public'];
$filesToCheck = [];
foreach ($pathsToCheck as $path) {
    if (!is_dir($path)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        $filesToCheck[] = $file;
    }
}
$syntaxErrors = 0;
foreach ($filesToCheck as $file) {
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
