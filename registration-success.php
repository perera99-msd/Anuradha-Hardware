<?php
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3a86ff;
            --primary-hover: #2667cc;
            --secondary-color: #8338ec;
            --accent-color: #ff006e;
            --dark-bg: #121212;
            --darker-bg: #0a0a0a;
            --dark-card: #1e1e1e;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --text-muted: #777777;
            --border-color: #333333;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --info-color: #2196f3;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .success-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }

        .success-card {
            background-color: var(--dark-card);
            border-radius: 8px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: var(--shadow);
        }

        .success-icon {
            font-size: 5rem;
            color: var(--success-color);
            margin-bottom: 20px;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: var(--text-secondary);
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-secondary {
            background-color: var(--darker-bg);
            margin-left: 15px;
        }

        .btn-secondary:hover {
            background-color: var(--border-color);
        }

        .btn-group {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Registration Successful!</h1>
                <p>Thank you for registering with Anuradha Hardware. Your account has been created successfully.</p>
                <p>You can now log in to your account to access exclusive features and benefits.</p>
                <div class="btn-group">
                    <a href="login.html" class="btn">Go to Login Page</a>
                    <a href="index.html" class="btn btn-secondary">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>