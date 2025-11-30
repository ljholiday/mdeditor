<?php

namespace MarkdownEditor\Controller;

use MarkdownEditor\Auth\SessionAuth;
use MarkdownEditor\Service\UserService;

class AuthController
{
    private SessionAuth $auth;
    private UserService $userService;

    public function __construct()
    {
        $this->auth = new SessionAuth();
        $this->userService = new UserService();
    }

    public function showLogin(): void
    {
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        require __DIR__ . '/../View/login.php';
    }

    public function login(): void
    {
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            $_SESSION['login_error'] = 'Username and password required';
            $this->redirect('/');
            return;
        }

        if ($this->auth->login($_POST['username'], $_POST['password'])) {
            $this->redirect('/');
        } else {
            $_SESSION['login_error'] = 'Invalid username or password';
            $this->redirect('/');
        }
    }

    public function showRegister(): void
    {
        $error = $_SESSION['register_error'] ?? null;
        unset($_SESSION['register_error']);

        require __DIR__ . '/../View/register.php';
    }

    public function register(): void
    {
        if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['confirm_password'])) {
            $_SESSION['register_error'] = 'All fields are required';
            $this->redirect('/register');
            return;
        }

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if (strlen($username) < 3) {
            $_SESSION['register_error'] = 'Username must be at least 3 characters';
            $this->redirect('/register');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = 'Invalid email address';
            $this->redirect('/register');
            return;
        }

        if (strlen($password) < 8) {
            $_SESSION['register_error'] = 'Password must be at least 8 characters';
            $this->redirect('/register');
            return;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['register_error'] = 'Passwords do not match';
            $this->redirect('/register');
            return;
        }

        if ($this->userService->createUser($username, $email, $password)) {
            // Auto-login after registration
            $this->auth->login($username, $password);
            $this->redirect('/');
        } else {
            $_SESSION['register_error'] = 'Username or email already exists';
            $this->redirect('/register');
        }
    }

    public function showChangePassword(): void
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/');
            return;
        }

        $error = $_SESSION['password_error'] ?? null;
        $success = $_SESSION['password_success'] ?? null;
        unset($_SESSION['password_error']);
        unset($_SESSION['password_success']);

        require __DIR__ . '/../View/change-password.php';
    }

    public function changePassword(): void
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/');
            return;
        }

        if (!isset($_POST['current_password']) || !isset($_POST['new_password']) || !isset($_POST['confirm_password'])) {
            $_SESSION['password_error'] = 'All fields are required';
            $this->redirect('/change-password');
            return;
        }

        $username = $this->auth->getCurrentUsername();
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Verify current password
        if (!$this->userService->authenticate($username, $currentPassword)) {
            $_SESSION['password_error'] = 'Current password is incorrect';
            $this->redirect('/change-password');
            return;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['password_error'] = 'New password must be at least 8 characters';
            $this->redirect('/change-password');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['password_error'] = 'New passwords do not match';
            $this->redirect('/change-password');
            return;
        }

        if ($this->userService->updatePassword($username, $newPassword)) {
            $_SESSION['password_success'] = 'Password changed successfully';
            $this->redirect('/change-password');
        } else {
            $_SESSION['password_error'] = 'Failed to change password';
            $this->redirect('/change-password');
        }
    }

    public function showForgotPassword(): void
    {
        $error = $_SESSION['forgot_error'] ?? null;
        $success = $_SESSION['forgot_success'] ?? null;
        unset($_SESSION['forgot_error']);
        unset($_SESSION['forgot_success']);

        require __DIR__ . '/../View/forgot-password.php';
    }

    public function forgotPassword(): void
    {
        if (!isset($_POST['email'])) {
            $_SESSION['forgot_error'] = 'Email is required';
            $this->redirect('/forgot-password');
            return;
        }

        $email = trim($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['forgot_error'] = 'Invalid email address';
            $this->redirect('/forgot-password');
            return;
        }

        $resetData = $this->userService->initiatePasswordReset($email);

        if ($resetData !== null) {
            // Send password reset email
            $emailSent = $this->userService->sendPasswordResetEmail($resetData['email'], $resetData['token']);

            if ($emailSent) {
                $_SESSION['forgot_success'] = 'Password reset instructions have been sent to your email';
            } else {
                $_SESSION['forgot_success'] = 'Password reset initiated. Check your email for instructions';
            }
        } else {
            // Don't reveal if email exists or not for security
            $_SESSION['forgot_success'] = 'If that email exists in our system, you will receive password reset instructions';
        }

        $this->redirect('/forgot-password');
    }

    public function showResetPassword(): void
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['login_error'] = 'Invalid password reset link';
            $this->redirect('/');
            return;
        }

        $error = $_SESSION['reset_error'] ?? null;
        $success = $_SESSION['reset_success'] ?? null;
        unset($_SESSION['reset_error']);
        unset($_SESSION['reset_success']);

        require __DIR__ . '/../View/reset-password.php';
    }

    public function resetPassword(): void
    {
        if (!isset($_POST['token']) || !isset($_POST['password']) || !isset($_POST['confirm_password'])) {
            $_SESSION['reset_error'] = 'All fields are required';
            $this->redirect('/reset-password?token=' . urlencode($_POST['token'] ?? ''));
            return;
        }

        $token = $_POST['token'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if (strlen($password) < 8) {
            $_SESSION['reset_error'] = 'Password must be at least 8 characters';
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['reset_error'] = 'Passwords do not match';
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        if ($this->userService->resetPasswordWithToken($token, $password)) {
            $_SESSION['reset_success'] = 'Password has been reset successfully!';
            $success = $_SESSION['reset_success'];
            require __DIR__ . '/../View/reset-password.php';
        } else {
            $_SESSION['reset_error'] = 'Invalid or expired reset link';
            $this->redirect('/reset-password?token=' . urlencode($token));
        }
    }

    public function showAccountSettings(): void
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/');
            return;
        }

        $username = $this->auth->getCurrentUsername();
        $user = $this->userService->findByUsername($username);

        $currentEmail = $user ? $user->getEmail() : '';
        $error = $_SESSION['settings_error'] ?? null;
        $success = $_SESSION['settings_success'] ?? null;
        unset($_SESSION['settings_error']);
        unset($_SESSION['settings_success']);

        require __DIR__ . '/../View/account-settings.php';
    }

    public function updateAccountSettings(): void
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/');
            return;
        }

        if (!isset($_POST['email'])) {
            $_SESSION['settings_error'] = 'Email is required';
            $this->redirect('/account-settings');
            return;
        }

        $email = trim($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['settings_error'] = 'Invalid email address';
            $this->redirect('/account-settings');
            return;
        }

        $username = $this->auth->getCurrentUsername();

        if ($this->userService->updateEmail($username, $email)) {
            $_SESSION['settings_success'] = 'Email updated successfully';
            $this->redirect('/account-settings');
        } else {
            $_SESSION['settings_error'] = 'Email is already in use by another account';
            $this->redirect('/account-settings');
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect('/');
    }

    private function redirect(string $path): void
    {
        $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        $baseUrl = $baseUrl === '/' ? '' : $baseUrl;
        header('Location: ' . $baseUrl . $path);
        exit;
    }
}
