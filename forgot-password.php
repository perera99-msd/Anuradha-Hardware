<?php
require_once 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $message = "Please enter your email address";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                // Generate token
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", time() + 60 * 60); // 1 hour expiration
                
                // Store token in database
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, ?) 
                                      ON DUPLICATE KEY UPDATE token = ?, created_at = ?");
                $stmt->execute([$email, $token, $expires, $token, $expires]);
                
                // Send email (in a real application)
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=$token";
                
                // For demo purposes, we'll just show the link
                $message = "Password reset link has been sent to your email. For demo: <a href='$resetLink'>$resetLink</a>";
            } else {
                $message = "If this email exists in our system, a password reset link has been sent";
            }
        } catch (PDOException $e) {
            $message = "Error processing your request. Please try again.";
            error_log("Password reset error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Use the same styles as your login page */
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

        .forgot-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .forgot-card {
            background-color: var(--dark-card);
            border-radius: 8px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: var(--shadow);
        }

        h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .form-description {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 4px;
            background-color: var(--darker-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            transition: var(--transition);
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
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
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            color: var(--success-color);
            background-color: rgba(76, 175, 80, 0.1);
            border-left: 4px solid var(--success-color);
        }

        .error {
            color: var(--danger-color);
            background-color: rgba(244, 67, 54, 0.1);
            border-left: 4px solid var(--danger-color);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary-color);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-container">
            <div class="forgot-card">
                <h2>Forgot Password</h2>
                <p class="form-description">Enter your email address to receive a password reset link.</p>
                
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                        <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="forgot-password.php">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <button type="submit" class="btn">Send Reset Link</button>
                </form>
                
                <a href="login.php" class="back-link">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>