<?php
require_once 'config/db.php';

$error = '';
$success = '';
$validToken = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Check if token exists and is not expired
        $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND created_at > NOW()");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();
        
        if ($resetRequest) {
            $validToken = true;
            $email = $resetRequest['email'];
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = trim($_POST['password']);
                $confirmPassword = trim($_POST['confirmPassword']);
                
                if (empty($password) || empty($confirmPassword)) {
                    $error = "Both password fields are required";
                } elseif ($password !== $confirmPassword) {
                    $error = "Passwords do not match";
                } elseif (strlen($password) < 8) {
                    $error = "Password must be at least 8 characters long";
                } else {
                    // Update password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $stmt->execute([$hashedPassword, $email]);
                    
                    // Delete the token
                    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                    $stmt->execute([$token]);
                    
                    $success = "Password updated successfully. You can now <a href='login.php'>login</a> with your new password.";
                    $validToken = false; // Token is now invalid
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Error processing your request. Please try again.";
        error_log("Password reset error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Use the same styles as forgot-password.php */
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

        .reset-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .reset-card {
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

        .password-hint {
            color: var(--text-secondary);
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .password-strength {
            height: 4px;
            background: var(--darker-bg);
            margin-top: 5px;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0;
            background: var(--danger-color);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <div class="reset-card">
                <h2>Reset Password</h2>
                
                <?php if (!$validToken): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-circle"></i> Invalid or expired password reset link. Please request a new one.
                    </div>
                    <a href="forgot-password.php" class="btn">Request New Reset Link</a>
                <?php else: ?>
                    <p class="form-description">Enter your new password below.</p>
                    
                    <?php if (!empty($error)): ?>
                        <div class="message error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="message success">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="reset-password.php?token=<?php echo htmlspecialchars($token); ?>">
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" id="password" name="password" required>
                                <div class="password-hint">Minimum 8 characters</div>
                                <div class="password-strength">
                                    <div class="strength-meter" id="strengthMeter"></div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword" name="confirmPassword" required>
                            </div>
                            
                            <button type="submit" class="btn">Reset Password</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                
                <a href="login.php" class="back-link">Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('strengthMeter');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;
                
                // Character variety
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;
                
                // Update meter
                let width = 0;
                let color = '';
                
                if (strength <= 1) {
                    width = 25;
                    color = '#f44336'; // Red
                } else if (strength <= 3) {
                    width = 50;
                    color = '#ff9800'; // Orange
                } else if (strength <= 4) {
                    width = 75;
                    color = '#4caf50'; // Green
                } else {
                    width = 100;
                    color = '#3a86ff'; // Blue
                }
                
                strengthMeter.style.width = width + '%';
                strengthMeter.style.background = color;
            });
        }
    </script>
</body>
</html>