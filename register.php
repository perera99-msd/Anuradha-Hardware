<?php
include('db.php');
$error = '';
$success = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $accept_terms = isset($_POST['accept_terms']);
    $subscribe_newsletter = isset($_POST['subscribe_newsletter']) ? 1 : 0;

    // Validation
    if (!$accept_terms) {
        $error = "You must agree to the terms and privacy policy.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check && $check->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, subscribed_to_newsletter) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $hashed_password, $subscribe_newsletter);
            if ($stmt->execute()) {
                $success = "Registration successful! Redirecting to login...";
                header("refresh:2;url=login.php");
            } else {
                $error = "Error registering account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="register.css" />
</head>

<body>

    <section class="page-header">
        <div class="container">
            <h1>Register</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item"><a href="login.php">Login</a></li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="register-section">
        <div class="container">
            <div class="register-container">
                <div class="register-form">
                    <h2>Create an Account</h2>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required />
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required />
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required />
                        </div>
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required />
                            <div class="password-strength">
                                <span id="strength-label">Weak</span>
                                <div class="strength-meter">
                                    <span id="strength-bar" class="strength-bar weak"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required />
                        </div>
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="accept_terms" name="accept_terms" required />
                            <label for="accept_terms">
                                I agree to the <a href="terms.html">Terms</a> & <a href="privacy.html">Privacy Policy</a> *
                            </label>
                        </div>
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="subscribe_newsletter" name="subscribe_newsletter" />
                            <label for="subscribe_newsletter">Subscribe to our newsletter</label>
                        </div>
                        <button type="submit" class="btn btn-block">Register</button>

                        <?php if (!empty($error)): ?>
                            <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
                        <?php elseif (!empty($success)): ?>
                            <p style="color:green; text-align:center;"><?= htmlspecialchars($success) ?></p>
                        <?php endif; ?>
                    </form>
                    <div class="login-link">
                        Already have an account? <a href="login.php">Login here</a>
                    </div>
                </div>
                <div class="register-benefits">
                    <h3>Why Register With Us?</h3>
                    <div class="benefit-item"><i class="fas fa-truck"></i>
                        <p>Fast & Easy Checkout</p>
                    </div>
                    <div class="benefit-item"><i class="fas fa-history"></i>
                        <p>Track your orders</p>
                    </div>
                    <div class="benefit-item"><i class="fas fa-heart"></i>
                        <p>Save favorite products</p>
                    </div>
                    <div class="benefit-item"><i class="fas fa-percentage"></i>
                        <p>Get special discounts</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="js/register.js"></script>
</body>

</html>