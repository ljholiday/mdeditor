<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown Editor - Forgot Password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f5f5f5;
            padding: 1rem;
        }
        .forgot-box {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            margin: 0 0 1.5rem 0;
            color: #2c3e50;
            text-align: center;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .info {
            background: #e7f3ff;
            color: #004085;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        label {
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        input[type="email"] {
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        input[type="email"]:focus {
            outline: none;
            border-color: #007bff;
        }
        button {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }
        button:hover {
            background: #0056b3;
        }
        button:active {
            background: #004085;
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="forgot-box">
        <h2>Forgot Password</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php else: ?>
            <div class="info">
                Enter your email address and we'll send you a password reset link.
            </div>
            <form method="post" action="<?php $base = dirname($_SERVER['SCRIPT_NAME']); echo ($base === '/' ? '' : $base); ?>/forgot-password">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="your@email.com" autofocus required>
                </div>
                <button type="submit">Send Reset Link</button>
            </form>
        <?php endif; ?>
        <div class="back-link">
            <a href="<?php $base = dirname($_SERVER['SCRIPT_NAME']); echo ($base === '/' ? '' : $base); ?>/">Back to Login</a>
        </div>
    </div>
</body>
</html>
