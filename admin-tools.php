#!/usr/bin/env php
<?php
/**
 * Admin Tools for Markdown Editor
 * Emergency recovery tool for server administrators
 * Usage: php admin-tools.php
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line\n");
}

// Local PSR-4 autoloader (no Composer dependency)
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

use MarkdownEditor\Service\UserService;

$userService = new UserService();

function showMenu() {
    echo "\n=== Markdown Editor Admin Tools ===\n\n";
    echo "1. List all users\n";
    echo "2. Add email to user account\n";
    echo "3. Reset user password\n";
    echo "4. Exit\n\n";
    echo "Choose an option: ";
}

function listUsers(UserService $userService) {
    $usersFile = __DIR__ . '/users.json';
    if (!file_exists($usersFile)) {
        echo "No users found.\n";
        return;
    }

    $content = file_get_contents($usersFile);
    $data = json_decode($content, true);

    if (empty($data)) {
        echo "No users registered.\n";
        return;
    }

    echo "\nRegistered Users:\n";
    echo str_repeat("-", 70) . "\n";
    printf("%-20s %-30s %-20s\n", "Username", "Email", "Created");
    echo str_repeat("-", 70) . "\n";

    foreach ($data as $userData) {
        printf("%-20s %-30s %-20s\n",
            $userData['username'],
            $userData['email'] ?: '(no email)',
            substr($userData['created_at'] ?? 'Unknown', 0, 19)
        );
    }

    echo str_repeat("-", 70) . "\n";
    echo "Total users: " . count($data) . "\n";
}

function addEmail(UserService $userService) {
    echo "\nEnter username: ";
    $username = trim(fgets(STDIN));

    $user = $userService->findByUsername($username);
    if (!$user) {
        echo "Error: User '$username' not found\n";
        return;
    }

    $currentEmail = $user->getEmail();
    if (!empty($currentEmail)) {
        echo "Current email: $currentEmail\n";
        echo "Overwrite? (y/n): ";
        $confirm = trim(fgets(STDIN));
        if (strtolower($confirm) !== 'y') {
            echo "Cancelled.\n";
            return;
        }
    }

    echo "Enter email address: ";
    $email = trim(fgets(STDIN));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Error: Invalid email format\n";
        return;
    }

    if ($userService->updateEmail($username, $email)) {
        echo "Success! Email updated for user '$username'\n";
    } else {
        echo "Error: Email is already in use by another account\n";
    }
}

function resetPassword(UserService $userService) {
    echo "\nEnter username: ";
    $username = trim(fgets(STDIN));

    $user = $userService->findByUsername($username);
    if (!$user) {
        echo "Error: User '$username' not found\n";
        return;
    }

    echo "Enter new password (min 8 characters): ";
    system('stty -echo');
    $password = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";

    if (strlen($password) < 8) {
        echo "Error: Password must be at least 8 characters\n";
        return;
    }

    echo "Confirm new password: ";
    system('stty -echo');
    $confirmPassword = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";

    if ($password !== $confirmPassword) {
        echo "Error: Passwords do not match\n";
        return;
    }

    if ($userService->updatePassword($username, $password)) {
        echo "Success! Password reset for user '$username'\n";
    } else {
        echo "Error: Failed to reset password\n";
    }
}

// Main loop
while (true) {
    showMenu();
    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case '1':
            listUsers($userService);
            break;
        case '2':
            addEmail($userService);
            break;
        case '3':
            resetPassword($userService);
            break;
        case '4':
            echo "Goodbye!\n";
            exit(0);
        default:
            echo "Invalid option. Please try again.\n";
    }
}
