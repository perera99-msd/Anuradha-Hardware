<?php
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $firstName = sanitizeInput($_POST['firstName']);
    $lastName = sanitizeInput($_POST['lastName']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $phone = sanitizeInput($_POST['phone']);
    $accountType = $_POST['accountType'];
    $businessName = isset($_POST['businessName']) ? sanitizeInput($_POST['businessName']) : null;
    $vatNumber = isset($_POST['vatNumber']) ? sanitizeInput($_POST['vatNumber']) : null;
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $postalCode = sanitizeInput($_POST['postalCode']);
    $country = sanitizeInput($_POST['country']);

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || 
        empty($confirmPassword) || empty($phone) || empty($address) || 
        empty($city) || empty($postalCode) || empty($country)) {
        $error = "All required fields must be filled!";
    }
    // Validate email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    }
    // Validate password match
    elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    }
    // Validate password strength
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    }
    // Validate terms checkbox
    elseif (!isset($_POST['terms'])) {
        $error = "You must agree to the Terms & Conditions!";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Email already exists!";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert into database
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, phone, account_type, business_name, vat_number, address, city, postal_code, country) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $firstName, 
                    $lastName, 
                    $email, 
                    $hashedPassword, 
                    $phone, 
                    $accountType, 
                    $businessName, 
                    $vatNumber, 
                    $address, 
                    $city, 
                    $postalCode, 
                    $country
                ]);

                // Redirect to success page
                header("Location: registration-success.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Registration failed. Please try again.";
            // Log the error for admin
            error_log("Registration error: " . $e->getMessage());
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
   <style>
        /* Base Styles */
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

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: var(--text-primary);
            transition: var(--transition);
        }

        a:hover {
            color: var(--primary-color);
        }

        img {
            max-width: 100%;
            height: auto;
        }

        ul {
            list-style: none;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
        }

        .view-all {
            color: var(--primary-color);
            font-weight: 600;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        /* Top Bar */
        .top-bar {
            background-color: var(--darker-bg);
            padding: 8px 0;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border-color);
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .contact-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .contact-info span {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--text-secondary);
        }

        .contact-info i {
            color: var(--primary-color);
        }

        .top-bar-actions {
            display: flex;
            gap: 15px;
        }

        .language-selector, .currency-selector {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .language-selector i, .currency-selector i {
            color: var(--primary-color);
        }

        .language-selector select, .currency-selector select {
            background-color: transparent;
            border: none;
            color: var(--text-primary);
            padding: 5px;
            cursor: pointer;
        }

        .language-selector select option, .currency-selector select option {
            background-color: var(--dark-card);
        }

        /* Header */
        .header {
            padding: 20px 0;
            background-color: var(--dark-bg);
            border-bottom: 1px solid var(--border-color);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            height: 40px;
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .logo-text span {
            color: var(--primary-color);
        }

        .search-bar {
            flex: 1;
            min-width: 300px;
            max-width: 600px;
        }

        .search-bar form {
            display: flex;
        }

        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px 0 0 4px;
            background-color: var(--darker-bg);
            color: var(--text-primary);
        }

        .search-category {
            border: 1px solid var(--border-color);
            border-left: none;
            background-color: var(--darker-bg);
            color: var(--text-primary);
            padding: 0 10px;
            cursor: pointer;
        }

        .search-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0 15px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }

        .search-btn:hover {
            background-color: var(--primary-hover);
        }

        .user-actions {
            display: flex;
            gap: 20px;
        }

        .user-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            color: var(--text-secondary);
            position: relative;
        }

        .user-action i {
            font-size: 1.3rem;
        }

        .action-label {
            font-size: 0.8rem;
        }

        .cart-count, .wishlist-count {
            position: absolute;
            top: -5px;
            right: 0;
            background-color: var(--primary-color);
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Navigation */
        .main-nav {
            background-color: var(--darker-bg);
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }

        .nav-menu {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }

        .nav-menu > li {
            position: relative;
        }

        .nav-menu > li > a {
            display: block;
            padding: 15px 20px;
            font-weight: 600;
            color: var(--text-primary);
            transition: var(--transition);
        }

        .nav-menu > li > a:hover, .nav-menu > li > a.active {
            color: var(--primary-color);
        }

        .nav-menu > li > a i {
            font-size: 0.8rem;
            margin-left: 5px;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1560439514-4e9645039924?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80') no-repeat center center;
            background-size: cover;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 50px;
        }

        .page-header h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
        }

        .breadcrumb {
            display: flex;
            justify-content: center;
            gap: 10px;
            color: var(--text-secondary);
        }

        .breadcrumb a {
            color: var(--text-secondary);
        }

        .breadcrumb a:hover {
            color: var(--primary-color);
        }

        .breadcrumb span {
            color: var(--primary-color);
        }

        /* Registration Section */
        .registration-section {
            padding: 60px 0;
            display: flex;
            justify-content: center;
        }

        .registration-container {
            display: flex;
            max-width: 1000px;
            width: 100%;
            background-color: var(--dark-card);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .registration-form {
            flex: 1;
            padding: 40px;
        }

        .registration-form h2 {
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

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-col {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border-radius: 4px;
            background-color: var(--darker-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            transition: var(--transition);
        }

        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .account-type {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .account-type label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 12px 20px;
            background-color: var(--darker-bg);
            border-radius: 4px;
            border: 1px solid var(--border-color);
            transition: var(--transition);
            flex: 1;
        }

        .account-type label:hover {
            border-color: var(--primary-color);
        }

        .account-type input[type="radio"] {
            display: none;
        }

        .account-type input[type="radio"]:checked + label {
            background-color: rgba(58, 134, 255, 0.1);
            border-color: var(--primary-color);
        }

        .terms {
            display: flex;
            gap: 10px;
            margin: 25px 0;
        }

        .terms input {
            margin-top: 3px;
        }

        .terms label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .terms a {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .form-footer {
            margin-top: 30px;
            text-align: center;
        }

        .social-login {
            margin: 30px 0;
            text-align: center;
        }

        .social-login p {
            color: var(--text-secondary);
            margin-bottom: 15px;
            position: relative;
        }

        .social-login p::before,
        .social-login p::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30%;
            height: 1px;
            background-color: var(--border-color);
        }

        .social-login p::before {
            left: 0;
        }

        .social-login p::after {
            right: 0;
        }

        .social-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .social-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 4px;
            background-color: var(--darker-bg);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: var(--transition);
        }

        .social-btn.google:hover {
            background-color: #4285F4;
            border-color: #4285F4;
        }

        .social-btn.facebook:hover {
            background-color: #4267B2;
            border-color: #4267B2;
        }

        .registration-image {
            flex: 1;
            background: linear-gradient(rgba(58, 134, 255, 0.2), rgba(58, 134, 255, 0.2)), url('https://images.unsplash.com/photo-1601924994987-69e26d50dc26?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: flex-end;
            padding: 40px;
            color: white;
        }

        .image-content h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .image-content ul {
            margin-left: 20px;
        }

        .image-content li {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .image-content li i {
            color: var(--primary-color);
        }

        /* Newsletter Section */
        .newsletter {
            padding: 60px 0;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80') no-repeat center center;
            background-size: cover;
            color: white;
        }

        .newsletter-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
        }

        .newsletter-text h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .newsletter-text p {
            color: var(--text-secondary);
        }

        .newsletter-form {
            display: flex;
            flex: 1;
            min-width: 300px;
            max-width: 500px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 4px 0 0 4px;
            background-color: var(--dark-card);
            color: var(--text-primary);
        }

        .newsletter-form button {
            border-radius: 0 4px 4px 0;
            padding: 0 25px;
        }

        /* Footer */
        .footer {
            background-color: var(--darker-bg);
            padding: 60px 0 0;
            border-top: 1px solid var(--border-color);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-col {
            padding: 0 15px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .footer-logo img {
            height: 40px;
        }

        .footer-logo h3 {
            font-size: 1.3rem;
        }

        .footer-col p {
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .footer-social {
            display: flex;
            gap: 15px;
        }

        .footer-social a {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--dark-card);
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .footer-social a:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .footer-col h4 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-col h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--primary-color);
        }

        .footer-col ul li {
            margin-bottom: 12px;
        }

        .footer-col ul li a {
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .footer-col ul li a:hover {
            color: var(--primary-color);
            padding-left: 5px;
        }

        .contact-info li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 15px;
            color: var(--text-secondary);
        }

        .contact-info i {
            color: var(--primary-color);
            margin-top: 3px;
        }

        .footer-bottom {
            padding: 20px 0;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-bottom p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .payment-methods {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .payment-methods img {
            height: 25px;
            filter: grayscale(100%);
            opacity: 0.7;
            transition: var(--transition);
        }

        .payment-methods img:hover {
            filter: grayscale(0%);
            opacity: 1;
        }

        /* Back to Top */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            opacity: 0;
            pointer-events: none;
            transition: var(--transition);
            z-index: 99;
        }

        .back-to-top.active {
            opacity: 1;
            pointer-events: all;
        }

        .back-to-top:hover {
            background-color: var(--primary-hover);
            transform: translateY(-3px);
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .registration-container {
                flex-direction: column;
            }
            
            .registration-image {
                min-height: 300px;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .account-type {
                flex-direction: column;
            }
            
            .page-header h1 {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 576px) {
            .top-bar-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 10px;
            }
            
            .header-content {
                flex-direction: column;
            }
            
            .search-bar {
                width: 100%;
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
                    <span><i class="fas fa-phone-alt"></i> +94 112 345 678</span>
                    <span><i class="fas fa-envelope"></i> info@anuradhahardware.com</span>
                    <span><i class="fas fa-map-marker-alt"></i> 123 Galle Road, Colombo 04</span>
                </div>
                <div class="top-bar-actions">
                    <div class="language-selector">
                        <i class="fas fa-globe"></i>
                        <select>
                            <option value="en">English</option>
                            <option value="si">සිංහල</option>
                            <option value="ta">தமிழ்</option>
                        </select>
                    </div>
                    <div class="currency-selector">
                        <i class="fas fa-money-bill-wave"></i>
                        <select>
                            <option value="lkr">LKR</option>
                            <option value="usd">USD</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.html">
                        <div style="width:40px;height:40px;background:#3a86ff;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold">AH</div>
                        <span class="logo-text">Anuradha <span>Hardware</span></span>
                    </a>
                </div>
                <div class="search-bar">
                    <form action="products.html" method="GET">
                        <input type="text" placeholder="Search for products..." class="search-input">
                        <select name="category" class="search-category">
                            <option value="all">All Categories</option>
                            <option value="tools">Tools</option>
                            <option value="building">Building Materials</option>
                            <option value="paints">Paints</option>
                            <option value="plumbing">Plumbing</option>
                            <option value="electrical">Electrical</option>
                        </select>
                        <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="user-actions">
                    <a href="login.html" class="user-action" title="My Account">
                        <i class="fas fa-user"></i>
                        <span class="action-label">Account</span>
                    </a>
                    <a href="wishlist.html" class="user-action" title="Wishlist">
                        <i class="fas fa-heart"></i>
                        <span class="action-label">Wishlist</span>
                        <span class="wishlist-count">0</span>
                    </a>
                    <a href="cart.html" class="user-action cart-icon" title="Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="action-label">Cart</span>
                        <span class="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu">
                <li><a href="index.html">Home</a></li>
                <li><a href="products.html">Products</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="services.html">Services</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="offers.html">Special Offers</a></li>
                <li><a href="blog.html">Blog</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Create Your Account</h1>
            <div class="breadcrumb">
                <a href="index.html">Home</a> / <span>Register</span>
            </div>
        </div>
    </section>

    <!-- Registration Section -->
    <section class="registration-section">
        <div class="container">
            <div class="registration-container">
                <div class="registration-form">
                    <h2>Register Account</h2>
                    <p class="form-description">Create your account to access special pricing, track orders, and manage your profile.</p>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="registrationForm" method="POST" action="register.php">
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" id="password" name="password" required>
                                    <div class="password-hint">Minimum 8 characters</div>
                                    <div class="password-strength">
                                        <div class="strength-meter" id="strengthMeter"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="confirmPassword">Confirm Password</label>
                                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Account Type</label>
                            <div class="account-type">
                                <input type="radio" id="individual" name="accountType" value="individual" <?php echo (!isset($accountType) || $accountType === 'individual') ? 'checked' : ''; ?>>
                                <label for="individual"><i class="fas fa-user"></i> Individual Account</label>
                                
                                <input type="radio" id="business" name="accountType" value="business" <?php echo (isset($accountType) && $accountType === 'business') ? 'checked' : ''; ?>>
                                <label for="business"><i class="fas fa-building"></i> Business Account</label>
                            </div>
                        </div>
                        
                        <div id="businessFields" style="display: <?php echo (isset($accountType) && $accountType === 'business' ? 'block' : 'none'); ?>;">
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="businessName">Business Name</label>
                                        <input type="text" id="businessName" name="businessName" value="<?php echo isset($businessName) ? htmlspecialchars($businessName) : ''; ?>">
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="vatNumber">VAT Number</label>
                                        <input type="text" id="vatNumber" name="vatNumber" value="<?php echo isset($vatNumber) ? htmlspecialchars($vatNumber) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3" required><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="postalCode">Postal Code</label>
                                    <input type="text" id="postalCode" name="postalCode" value="<?php echo isset($postalCode) ? htmlspecialchars($postalCode) : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="country" required>
                                <option value="Sri Lanka" <?php echo (isset($country) && $country === 'Sri Lanka' ? 'selected' : ''); ?>>Sri Lanka</option>
                                <option value="India" <?php echo (isset($country) && $country === 'India' ? 'selected' : ''); ?>>India</option>
                                <option value="United States" <?php echo (isset($country) && $country === 'United States' ? 'selected' : ''); ?>>United States</option>
                                <option value="United Kingdom" <?php echo (isset($country) && $country === 'United Kingdom' ? 'selected' : ''); ?>>United Kingdom</option>
                                <option value="Australia" <?php echo (isset($country) && $country === 'Australia' ? 'selected' : ''); ?>>Australia</option>
                            </select>
                        </div>
                        
                        <div class="terms">
                            <input type="checkbox" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                            <label for="terms">I agree to the <a href="terms.html">Terms & Conditions</a> and <a href="privacy.html">Privacy Policy</a></label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width:100%;padding:15px">Create Account</button>
                        
                        <div class="form-footer">
                            <p>Already have an account? <a href="login.html" style="color:var(--primary-color);font-weight:600">Sign In</a></p>
                        </div>
                    </form>
                    
                    <div class="social-login">
                        <p>Or register with</p>
                        <div class="social-buttons">
                            <div class="social-btn google">
                                <i class="fab fa-google"></i>
                                <span>Google</span>
                            </div>
                            <div class="social-btn facebook">
                                <i class="fab fa-facebook-f"></i>
                                <span>Facebook</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="registration-image">
                    <div class="image-content">
                        <h3>Benefits of Registration</h3>
                        <ul>
                            <li><i class="fas fa-check-circle"></i> Access to contractor discounts</li>
                            <li><i class="fas fa-check-circle"></i> Save up to 15% on bulk orders</li>
                            <li><i class="fas fa-check-circle"></i> Faster checkout process</li>
                            <li><i class="fas fa-check-circle"></i> Track order history</li>
                            <li><i class="fas fa-check-circle"></i> Manage multiple delivery addresses</li>
                            <li><i class="fas fa-check-circle"></i> Exclusive promotions and offers</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3>Subscribe to Our Newsletter</h3>
                    <p>Get updates on special offers and new products</p>
                </div>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <div style="width:40px;height:40px;background:#3a86ff;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold">AH</div>
                        <h3>Anuradha Hardware</h3>
                    </div>
                    <p>Your trusted partner for quality hardware and construction materials since 1995.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="products.html">Products</a></li>
                        <li><a href="services.html">Services</a></li>
                        <li><a href="offers.html">Special Offers</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="faq.html">FAQ</a></li>
                        <li><a href="shipping.html">Shipping Policy</a></li>
                        <li><a href="returns.html">Return Policy</a></li>
                        <li><a href="privacy.html">Privacy Policy</a></li>
                        <li><a href="terms.html">Terms & Conditions</a></li>
                        <li><a href="size-guide.html">Size Guide</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact Info</h4>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Galle Road, Colombo 04, Sri Lanka</li>
                        <li><i class="fas fa-phone"></i> +94 112 345 678</li>
                        <li><i class="fas fa-envelope"></i> info@anuradhahardware.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 8:30AM - 6:00PM</li>
                        <li><i class="fas fa-store"></i> Sun: 10:00AM - 4:00PM</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 Anuradha Hardware. All Rights Reserved.</p>
                <div class="payment-methods">
                    <div style="background:#eee;padding:2px 8px;border-radius:4px;font-weight:bold">VISA</div>
                    <div style="background:#eee;padding:2px 8px;border-radius:4px;font-weight:bold">MASTERCARD</div>
                    <div style="background:#eee;padding:2px 8px;border-radius:4px;font-weight:bold">AMEX</div>
                    <div style="background:#eee;padding:2px 8px;border-radius:4px;font-weight:bold">COD</div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle business fields
            const accountTypeRadios = document.querySelectorAll('input[name="accountType"]');
            const businessFields = document.getElementById('businessFields');
            
            accountTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'business') {
                        businessFields.style.display = 'block';
                    } else {
                        businessFields.style.display = 'none';
                    }
                });
            });
            
            // Password strength indicator
            const passwordInput = document.getElementById('password');
            const strengthMeter = document.getElementById('strengthMeter');
            
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
            
            // Form validation
            const registrationForm = document.getElementById('registrationForm');
            
            registrationForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                const termsChecked = document.getElementById('terms').checked;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return;
                }
                
                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long!');
                    return;
                }
                
                if (!termsChecked) {
                    e.preventDefault();
                    alert('You must agree to the Terms & Conditions!');
                    return;
                }
            });
            
            // Mobile menu toggle
            const menuToggle = document.querySelector('.menu-toggle');
            const navMenu = document.querySelector('.nav-menu');
            if (menuToggle) {
                menuToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                    menuToggle.innerHTML = navMenu.classList.contains('active') ? 
                        '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
                });
            }
            
            // Back to top button
            const backToTop = document.getElementById('backToTop');
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('active');
                } else {
                    backToTop.classList.remove('active');
                }
            });
            
            backToTop.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Social login buttons
            document.querySelectorAll('.social-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const platform = this.classList.contains('google') ? 'Google' : 'Facebook';
                    alert(`You selected ${platform} login. In a real application, this would redirect to ${platform} authentication.`);
                });
            });
        });
    </script>
</body>
</html>