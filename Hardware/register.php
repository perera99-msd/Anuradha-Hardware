<?php
// Include database connection
include('anuradha-admin/includes/db.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

// Handle registration form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    $account_type = $_POST['account_type'];
    $business_name = $account_type == 'business' ? trim($_POST['business_name']) : '';
    $vat_number = $account_type == 'business' ? trim($_POST['vat_number']) : '';
    $agree_terms = isset($_POST['agree_terms']);
    
    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || 
        empty($password) || empty($confirm_password) || empty($address) || 
        empty($city) || empty($postal_code) || empty($country)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!$agree_terms) {
        $error = 'You must agree to the Terms and Conditions.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email address is already registered.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, address, city, postal_code, country, account_type, business_name, vat_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssss", $first_name, $last_name, $email, $phone, $hashed_password, $address, $city, $postal_code, $country, $account_type, $business_name, $vat_number);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
                
                // Clear form
                $first_name = $last_name = $email = $phone = $address = $city = $postal_code = $country = '';
            } else {
                $error = 'Registration failed. Please try again later.';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/auth.css">
    <style>
        /* Fix form consistency issues */
        .auth-form input, 
        .auth-form select, 
        .auth-form textarea {
            font-size: 16px;
            padding: 14px 15px 14px 45px;
            height: auto;
        }
        
        .auth-form textarea {
            min-height: 100px;
            padding-top: 14px;
            padding-bottom: 14px;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-row {
            margin-bottom: 24px;
        }
        
        .form-row .form-group {
            margin-bottom: 0;
        }
        
        .input-with-icon i {
            font-size: 18px;
        }
        
        .account-type-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 24px;
        }
        
        .account-type-option {
            flex: 1;
            border: 2px solid #ddd;
            padding: 20px 15px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .account-type-option.active {
            border-color: #f57224;
            background-color: #fff8f5;
        }
        
        .account-type-option i {
            font-size: 28px;
            margin-bottom: 12px;
            color: #666;
        }
        
        .account-type-option.active i {
            color: #f57224;
        }
        
        .account-type-option h4 {
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .account-type-option p {
            font-size: 14px;
            margin: 0;
            color: #666;
        }
        
        .business-fields {
            display: none;
        }
        
        .business-fields.active {
            display: block;
        }
        
        /* Fix alignment issues */
        .business-fields .form-row {
            margin-bottom: 0;
        }
        
        /* Password strength meter improvements */
        .password-strength {
            margin-top: 10px;
        }
        
        .strength-meter {
            height: 6px;
            margin-bottom: 8px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .auth-form input, 
            .auth-form select, 
            .auth-form textarea {
                padding: 14px 15px 14px 40px;
            }
            
            .account-type-selector {
                flex-direction: column;
            }
            
            .business-fields .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="contact-info">
                    <span><i class="fas fa-phone"></i> +94 112 345 678</span>
                    <span><i class="fas fa-envelope"></i> info@anuradhahardware.com</span>
                </div>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <img src="images/logo/logo.jpg" alt="Anuradha Hardware">    
                    </a>
                </div>
                <div class="search-bar">
                    <form action="products.php" method="GET">
                        <input type="text" placeholder="Search for products...">
                        <select name="category">
                            <option value="all">All Categories</option>
                            <option value="tools">Tools</option>
                            <option value="building">Building Materials</option>
                            <option value="paints">Paints</option>
                            <option value="plumbing">Plumbing</option>
                            <option value="electrical">Electrical</option>
                        </select>
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="user-actions">
                    <a href="login.php"><i class="fas fa-user"></i></a>
                    <a href="wishlist.php"><i class="fas fa-heart"></i></a>
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li class="dropdown">
                    <a href="products.php">Products <i class="fas fa-chevron-down"></i></a>
                    <div class="dropdown-menu">
                        <div class="dropdown-column">
                            <h4>Tools</h4>
                            <a href="products.php?category=hand-tools">Hand Tools</a>
                            <a href="products.php?category=power-tools">Power Tools</a>
                            <a href="products.php?category=gardening">Gardening Tools</a>
                        </div>
                        <div class="dropdown-column">
                            <h4>Building Materials</h4>
                            <a href="products.php?category=cement">Cement & Aggregates</a>
                            <a href="products.php?category=bricks">Bricks & Blocks</a>
                            <a href="products.php?category=steel">Steel & Rods</a>
                        </div>
                        <div class="dropdown-column">
                            <h4>Paints & Decor</h4>
                            <a href="products.php?category=paints">Paints</a>
                            <a href="products.php?category=wallpaper">Wallpapers</a>
                            <a href="products.php?category=tiles">Tiles</a>
                        </div>
                        <div class="dropdown-column">
                            <h4>Plumbing</h4>
                            <a href="products.php?category=pipes">Pipes & Fittings</a>
                            <a href="products.php?category=bathroom">Bathroom Fixtures</a>
                            <a href="products.php?category=taps">Taps & Faucets</a>
                        </div>
                    </div>
                </li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="offers.php">Special Offers</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Create an Account</h1>
            <p>Join thousands of satisfied customers</p>
        </div>
    </section>

    <!-- Register Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-form">
                    <h2>Register</h2>
                    <p class="auth-subtitle">Create your account to enjoy exclusive benefits</p>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="register.php" id="registerForm">
                        <div class="form-group">
                            <label>Account Type</label>
                            <div class="account-type-selector">
                                <div class="account-type-option active" data-type="individual">
                                    <i class="fas fa-user"></i>
                                    <h4>Individual</h4>
                                    <p>Personal use</p>
                                </div>
                                <div class="account-type-option" data-type="business">
                                    <i class="fas fa-building"></i>
                                    <h4>Business</h4>
                                    <p>Company account</p>
                                </div>
                            </div>
                            <input type="hidden" name="account_type" id="account_type" value="individual">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" 
                                           value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" 
                                           value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email" 
                                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" 
                                       value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="business-fields" id="business_fields">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="business_name">Business Name</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-building"></i>
                                        <input type="text" id="business_name" name="business_name" placeholder="Enter business name" 
                                               value="<?php echo isset($business_name) ? htmlspecialchars($business_name) : ''; ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="vat_number">VAT Number</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-id-card"></i>
                                        <input type="text" id="vat_number" name="vat_number" placeholder="Enter VAT number" 
                                               value="<?php echo isset($vat_number) ? htmlspecialchars($vat_number) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-map-marker-alt"></i>
                                <textarea id="address" name="address" placeholder="Enter your full address" required><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-city"></i>
                                    <input type="text" id="city" name="city" placeholder="Enter your city" 
                                           value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="postal_code">Postal Code <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="text" id="postal_code" name="postal_code" placeholder="Enter postal code" 
                                           value="<?php echo isset($postal_code) ? htmlspecialchars($postal_code) : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-globe"></i>
                                <select id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    <option value="Sri Lanka" <?php echo (isset($country) && $country == 'Sri Lanka') ? 'selected' : ''; ?>>Sri Lanka</option>
                                    <option value="India">India</option>
                                    <option value="United States">United States</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <!-- Add more countries as needed -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Create a password" required>
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-meter">
                                    <div class="strength-bar"></div>
                                </div>
                                <span class="strength-text">Password strength</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-match" class="password-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-container">
                                <input type="checkbox" name="agree_terms" id="agree_terms" required>
                                <span class="checkmark"></span>
                                I agree to the <a href="terms.php">Terms and Conditions</a> and <a href="privacy.php">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-container">
                                <input type="checkbox" name="newsletter" id="newsletter" checked>
                                <span class="checkmark"></span>
                                Subscribe to our newsletter for updates and offers
                            </label>
                        </div>
                        
                        <button type="submit" name="register" class="btn btn-primary btn-full">Create Account</button>
                    </form>
                    
                    <div class="auth-divider">
                        <span>Or sign up with</span>
                    </div>
                    
                    <div class="social-login">
                        <a href="#" class="social-btn google-btn">
                            <img src="images/auth/google.svg" alt="Google">
                            Google
                        </a>
                        <a href="#" class="social-btn facebook-btn">
                            <img src="images/auth/facebook.svg" alt="Facebook">
                            Facebook
                        </a>
                    </div>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
                
                <div class="auth-promo">
                    <div class="auth-promo-content">
                        <h3>Why Create an Account?</h3>
                        <ul class="benefits-list">
                            <li>
                                <i class="fas fa-tags"></i>
                                <div>
                                    <h4>Exclusive Discounts</h4>
                                    <p>Get special offers available only to registered customers</p>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-history"></i>
                                <div>
                                    <h4>Order History</h4>
                                    <p>Track your orders and easily reorder favorite products</p>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-heart"></i>
                                <div>
                                    <h4>Wishlist</h4>
                                    <p>Save items you love for quick access later</p>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-truck-fast"></i>
                                <div>
                                    <h4>Faster Checkout</h4>
                                    <p>Save your address and payment details for quicker purchases</p>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-award"></i>
                                <div>
                                    <h4>Loyalty Rewards</h4>
                                    <p>Earn points on every purchase and redeem for discounts</p>
                                </div>
                            </li>
                        </ul>
                        
                        <div class="testimonial">
                            <div class="testimonial-content">
                                <p>"Since creating an account, I've saved both time and money on all my construction projects. The wishlist feature is especially helpful!"</p>
                                <div class="testimonial-author">
                                    <strong>Rajith Perera</strong>
                                    <span>Professional Contractor</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <img src="images/logo/logo.jpg" alt="Anuradha Hardware">
                        <h3>Anuradha Hardware</h3>
                    </div>
                    <p>Your trusted partner for quality hardware and construction materials since 1995.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="offers.php">Special Offers</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="shipping.php">Shipping Policy</a></li>
                        <li><a href="returns.php">Return Policy</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact Info</h4>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Galle Road, Colombo 04, Sri Lanka</li>
                        <li><i class="fas fa-phone"></i> +94 112 345 678</li>
                        <li><i class="fas fa-envelope"></i> info@anuradhahardware.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 8:30AM - 6:00PM</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 Anuradha Hardware. All Rights Reserved.</p>
                <div class="payment-methods">
                    <img src="images/payments/visa, method, card, payment icon.jpeg" alt="Visa">
                    <img src="images/payments/Brand New_ New Logo and Identity for MasterCard by….jpeg" alt="Mastercard">
                    <img src="images/payments/American Express Gift Cards Coupons.jpeg" alt="American Express">
                    <img src="images/payments/c6b792c8-f1d2-4fe2-9474-bb1e8faaa66b.jpeg" alt="PayPal">
                </div>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
    <script src="js/auth.js"></script>
    <script>
        // Account type selection
        document.querySelectorAll('.account-type-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.account-type-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                this.classList.add('active');
                document.getElementById('account_type').value = this.dataset.type;
                
                // Show/hide business fields
                const businessFields = document.getElementById('business_fields');
                if (this.dataset.type === 'business') {
                    businessFields.classList.add('active');
                } else {
                    businessFields.classList.remove('active');
                }
            });
        });
        
        // Password strength meter
        const passwordInput = document.getElementById('password');
        const strengthBar = document.querySelector('.strength-bar');
        const strengthText = document.querySelector('.strength-text');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength += 20;
            if (password.match(/[a-z]+/)) strength += 20;
            if (password.match(/[A-Z]+/)) strength += 20;
            if (password.match(/[0-9]+/)) strength += 20;
            if (password.match(/[$@#&!]+/)) strength += 20;
            
            strengthBar.style.width = strength + '%';
            
            if (strength < 40) {
                strengthBar.style.backgroundColor = '#e74c3c';
                strengthText.textContent = 'Weak password';
            } else if (strength < 80) {
                strengthBar.style.backgroundColor = '#f39c12';
                strengthText.textContent = 'Medium password';
            } else {
                strengthBar.style.backgroundColor = '#2ecc71';
                strengthText.textContent = 'Strong password';
            }
        });
        
        // Password confirmation check
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('password-match');
        
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                passwordMatch.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
                passwordMatch.className = 'password-feedback error';
            } else {
                passwordMatch.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
                passwordMatch.className = 'password-feedback success';
            }
        });
        
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>
</html>