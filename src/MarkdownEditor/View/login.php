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
            min-width: 320px;
        }
        h2 {
            margin: 0 0 1.5rem 0;
            color: #2c3e50;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        input[type="password"] {
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        input[type="password"]:focus {
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
        }
        button:hover {
            background: #0056b3;
        }
        button:active {
            background: #004085;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Markdown Editor</h2>
        <form method="post" action="<?php $base = dirname($_SERVER['SCRIPT_NAME']); echo ($base === '/' ? '' : $base); ?>/login">
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" autofocus required>
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
