<?php
// account.php
require_once 'auth.php';

// Get user details from database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header("Location: logout.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while fetching your account information.");
}

// Handle form submission
$update_success = false;
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $company = isset($_POST['company']) ? trim($_POST['company']) : '';
    
    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        $update_error = "Please fill in all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $update_error = "Please enter a valid email address";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $update_error = "Please enter a valid phone number";
    } else {
        try {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($stmt->rowCount() > 0) {
                $update_error = "This email is already registered to another account";
            } else {
                // Update user details
                $stmt = $pdo->prepare("UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?, 
                    business_name = ?
                    WHERE id = ?");
                
                $stmt->execute([
                    htmlspecialchars($first_name),
                    htmlspecialchars($last_name),
                    htmlspecialchars($email),
                    htmlspecialchars($phone),
                    htmlspecialchars($company),
                    $_SESSION['user_id']
                ]);
                
                // Update session data
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                
                $update_success = true;
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
            }
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            $update_error = "An error occurred while updating your account. Please try again.";
        }
    }
}

// Handle password change
$password_success = false;
$password_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password)) {
        $password_error = "Please enter your current password";
    } elseif (empty($new_password) || empty($confirm_password)) {
        $password_error = "Please enter and confirm your new password";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match";
    } elseif (strlen($new_password) < 8) {
        $password_error = "Password must be at least 8 characters long";
    } else {
        try {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $db_user = $stmt->fetch();
            
            if ($db_user && password_verify($current_password, $db_user['password'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $password_success = true;
            } else {
                $password_error = "Current password is incorrect";
            }
        } catch (PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            $password_error = "An error occurred while changing your password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Anuradha Hardware</title>
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
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
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
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1581578029524-0b8b1b9a0f9c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80') no-repeat center center;
            background-size: cover;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 30px;
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

        /* Account Dashboard */
        .account-dashboard {
            padding: 40px 0;
            margin-bottom: 60px;
        }

        .account-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        .account-sidebar {
            flex: 0 0 250px;
            background-color: var(--dark-card);
            border-radius: 8px;
            padding: 20px;
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .user-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--darker-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-email {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .account-menu {
            margin-bottom: 20px;
        }

        .account-menu h3 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .account-menu ul li {
            margin-bottom: 12px;
        }

        .account-menu ul li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .account-menu ul li a:hover, 
        .account-menu ul li a.active {
            color: var(--primary-color);
            padding-left: 5px;
        }

        .account-menu ul li a i {
            width: 20px;
            text-align: center;
        }

        .account-content {
            flex: 1;
            min-width: 300px;
        }

        .account-section {
            background-color: var(--dark-card);
            border-radius: 8px;
            padding: 30px;
            box-shadow: var(--shadow);
            display: none;
        }

        .account-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .account-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--darker-bg);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(58, 134, 255, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px;
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--primary-color);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .recent-orders {
            margin-top: 30px;
        }

        .recent-orders h3 {
            margin-bottom: 20px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .orders-table th {
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .orders-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }

        .order-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-completed {
            background-color: rgba(76, 175, 80, 0.2);
            color: var(--success-color);
        }

        .status-processing {
            background-color: rgba(255, 152, 0, 0.2);
            color: var(--warning-color);
        }

        .status-pending {
            background-color: rgba(33, 150, 243, 0.2);
            color: var(--info-color);
        }

        .status-cancelled {
            background-color: rgba(244, 67, 54, 0.2);
            color: var(--danger-color);
        }

        .btn-view {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-view:hover {
            color: var(--primary-hover);
        }

        /* Form Styles */
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

        .password-toggle {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 38px;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .address-card {
            background-color: var(--darker-bg);
            border-radius: 8px;
            padding: 20px;
            position: relative;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .address-card:hover {
            border-color: var(--primary-color);
        }

        .address-card.default {
            border-color: var(--primary-color);
            background-color: rgba(58, 134, 255, 0.05);
        }

        .address-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .address-title {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .default-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .address-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .address-actions a {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .address-actions a:hover {
            color: var(--primary-color);
        }

        .address-actions a.delete:hover {
            color: var(--danger-color);
        }

        .add-address {
            background-color: transparent;
            border: 2px dashed var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 200px;
            cursor: pointer;
            transition: var(--transition);
        }

        .add-address:hover {
            border-color: var(--primary-color);
        }

        .add-address i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .add-address span {
            color: var(--text-secondary);
            font-weight: 600;
        }

        /* Success and Error Messages */
        .success-message {
            color: var(--success-color);
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(76, 175, 80, 0.1);
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message {
            color: var(--danger-color);
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(244, 67, 54, 0.1);
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .account-sidebar {
                flex: 0 0 100%;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        @media (max-width: 768px) {
            .account-stats {
                grid-template-columns: 1fr;
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
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
                    <a href="index.php">
                        <div style="width:40px;height:40px;background:#3a86ff;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold">AH</div>
                        <span class="logo-text">Anuradha <span>Hardware</span></span>
                    </a>
                </div>
                <div class="search-bar">
                    <form action="products.php" method="GET">
                        <input type="text" placeholder="Search for products..." class="search-input" name="search">
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
                    <a href="account.php" class="user-action" title="My Account">
                        <i class="fas fa-user"></i>
                        <span class="action-label">Account</span>
                    </a>
                    <a href="wishlist.php" class="user-action" title="Wishlist">
                        <i class="fas fa-heart"></i>
                        <span class="action-label">Wishlist</span>
                        <span class="wishlist-count">3</span>
                    </a>
                    <a href="cart.php" class="user-action cart-icon" title="Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="action-label">Cart</span>
                        <span class="cart-count">2</span>
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
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="offers.php">Special Offers</a></li>
                <li><a href="blog.php">Blog</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>My Account</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>Account</span>
            </div>
        </div>
    </section>

    <!-- Account Dashboard -->
    <section class="account-dashboard">
        <div class="container">
            <div class="account-container">
                <div class="account-sidebar">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['first_name'].'+'.$user['last_name']); ?>&background=3a86ff&color=fff" alt="User Avatar">
                        </div>
                        <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    
                    <div class="account-menu">
                        <h3>Dashboard</h3>
                        <ul>
                            <li><a href="#" class="active" data-section="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="#" data-section="orders"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                            <li><a href="#" data-section="addresses"><i class="fas fa-map-marker-alt"></i> Addresses</a></li>
                            <li><a href="#" data-section="account-details"><i class="fas fa-user-cog"></i> Account Details</a></li>
                            <li><a href="#" data-section="wishlist"><i class="fas fa-heart"></i> Wishlist</a></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                    
                    <div class="account-menu">
                        <h3>Account</h3>
                        <ul>
                            <li><a href="#"><i class="fas fa-file-invoice"></i> Invoices</a></li>
                            <li><a href="#"><i class="fas fa-truck"></i> Track Order</a></li>
                            <li><a href="#"><i class="fas fa-question-circle"></i> Help & Support</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="account-content">
                    <!-- Dashboard Section -->
                    <div class="account-section active" id="dashboard">
                        <div class="section-header">
                            <h2 class="section-title">Dashboard</h2>
                            <div>Welcome back, <strong><?php echo htmlspecialchars($user['first_name']); ?></strong></div>
                        </div>
                        
                        <div class="account-stats">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stat-value">7</div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-sync-alt"></i>
                                </div>
                                <div class="stat-value">2</div>
                                <div class="stat-label">Pending Orders</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="stat-value">5</div>
                                <div class="stat-label">Wishlist Items</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="stat-value">3</div>
                                <div class="stat-label">Saved Addresses</div>
                            </div>
                        </div>
                        
                        <div class="recent-orders">
                            <h3>Recent Orders</h3>
                            <div style="overflow-x: auto;">
                                <table class="orders-table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#AH-2023-0012</td>
                                            <td>Oct 15, 2023</td>
                                            <td>3 Items</td>
                                            <td>Rs. 24,850.00</td>
                                            <td><span class="order-status status-completed">Completed</span></td>
                                            <td><button class="btn-view">View</button></td>
                                        </tr>
                                        <tr>
                                            <td>#AH-2023-0010</td>
                                            <td>Oct 10, 2023</td>
                                            <td>5 Items</td>
                                            <td>Rs. 42,150.00</td>
                                            <td><span class="order-status status-processing">Processing</span></td>
                                            <td><button class="btn-view">View</button></td>
                                        </tr>
                                        <tr>
                                            <td>#AH-2023-0008</td>
                                            <td>Oct 5, 2023</td>
                                            <td>2 Items</td>
                                            <td>Rs. 15,700.00</td>
                                            <td><span class="order-status status-pending">Payment Pending</span></td>
                                            <td><button class="btn-view">View</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Orders Section -->
                    <div class="account-section" id="orders">
                        <div class="section-header">
                            <h2 class="section-title">My Orders</h2>
                        </div>
                        
                        <div style="overflow-x: auto;">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>#AH-2023-0012</td>
                                        <td>Oct 15, 2023</td>
                                        <td>3 Items</td>
                                        <td>Rs. 24,850.00</td>
                                        <td><span class="order-status status-completed">Completed</span></td>
                                        <td><button class="btn-view">View</button></td>
                                    </tr>
                                    <tr>
                                        <td>#AH-2023-0010</td>
                                        <td>Oct 10, 2023</td>
                                        <td>5 Items</td>
                                        <td>Rs. 42,150.00</td>
                                        <td><span class="order-status status-processing">Processing</span></td>
                                        <td><button class="btn-view">View</button></td>
                                    </tr>
                                    <tr>
                                        <td>#AH-2023-0009</td>
                                        <td>Oct 8, 2023</td>
                                        <td>1 Item</td>
                                        <td>Rs. 8,500.00</td>
                                        <td><span class="order-status status-completed">Completed</span></td>
                                        <td><button class="btn-view">View</button></td>
                                    </tr>
                                    <tr>
                                        <td>#AH-2023-0008</td>
                                        <td>Oct 5, 2023</td>
                                        <td>2 Items</td>
                                        <td>Rs. 15,700.00</td>
                                        <td><span class="order-status status-pending">Payment Pending</span></td>
                                        <td><button class="btn-view">View</button></td>
                                    </tr>
                                    <tr>
                                        <td>#AH-2023-0006</td>
                                        <td>Oct 1, 2023</td>
                                        <td>4 Items</td>
                                        <td>Rs. 32,000.00</td>
                                        <td><span class="order-status status-completed">Completed</span></td>
                                        <td><button class="btn-view">View</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Addresses Section -->
                    <div class="account-section" id="addresses">
                        <div class="section-header">
                            <h2 class="section-title">My Addresses</h2>
                            <button class="btn btn-primary" id="addAddressBtn">Add New Address</button>
                        </div>
                        
                        <div class="address-grid">
                            <div class="address-card default">
                                <div class="address-header">
                                    <div class="address-title"><?php echo htmlspecialchars($user['account_type'] === 'business' ? $user['business_name'] : 'Home Address'); ?></div>
                                    <div class="default-badge">Default</div>
                                </div>
                                <div class="address-details">
                                    <p><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                    <p><?php echo htmlspecialchars($user['address']); ?></p>
                                    <p><?php echo htmlspecialchars($user['city']); ?></p>
                                    <p><?php echo htmlspecialchars($user['country']); ?></p>
                                    <p>Phone: <?php echo htmlspecialchars($user['phone']); ?></p>
                                    <?php if ($user['account_type'] === 'business' && !empty($user['vat_number'])): ?>
                                        <p>VAT: <?php echo htmlspecialchars($user['vat_number']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="address-actions">
                                    <a href="#" class="edit-address"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="#" class="delete"><i class="fas fa-trash-alt"></i> Delete</a>
                                </div>
                            </div>
                            
                            <div class="address-card add-address" id="addAddressCard">
                                <i class="fas fa-plus"></i>
                                <span>Add New Address</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Details Section -->
                    <div class="account-section" id="account-details">
                        <div class="section-header">
                            <h2 class="section-title">Account Details</h2>
                        </div>
                        
                        <?php if ($update_success): ?>
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i> Your account details have been updated successfully!
                            </div>
                        <?php elseif (!empty($update_error)): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($update_error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="accountForm" method="POST">
                            <input type="hidden" name="update_account" value="1">
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="company">Company (Optional)</label>
                                <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($user['business_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="section-header" style="margin-top: 40px; margin-bottom: 20px;">
                                <h3>Password Change</h3>
                            </div>
                            
                            <?php if ($password_success): ?>
                                <div class="success-message">
                                    <i class="fas fa-check-circle"></i> Your password has been changed successfully!
                                </div>
                            <?php elseif (!empty($password_error)): ?>
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($password_error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group password-toggle">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" name="current_password">
                                <span class="toggle-password" data-target="currentPassword">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group password-toggle">
                                        <label for="newPassword">New Password</label>
                                        <input type="password" id="newPassword" name="new_password">
                                        <span class="toggle-password" data-target="newPassword">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group password-toggle">
                                        <label for="confirmPassword">Confirm Password</label>
                                        <input type="password" id="confirmPassword" name="confirm_password">
                                        <span class="toggle-password" data-target="confirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                    
                    <!-- Wishlist Section -->
                    <div class="account-section" id="wishlist">
                        <div class="section-header">
                            <h2 class="section-title">My Wishlist</h2>
                        </div>
                        
                        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px;">
                            <!-- Wishlist Item 1 -->
                            <div class="product-card">
                                <div class="product-badge">-15%</div>
                                <div class="product-wishlist active"><i class="fas fa-heart"></i></div>
                                <div class="product-thumb">
                                    <a href="product-detail.php"><img src="https://images.unsplash.com/photo-1588200908342-23b585c03e26?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" alt="Professional Hammer"></a>
                                </div>
                                <div class="product-details">
                                    <h3><a href="product-detail.php">Professional Hammer 16oz</a></h3>
                                    <div class="product-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <span>(24)</span>
                                    </div>
                                    <div class="product-price">
                                        <span class="price">Rs. 1,850.00</span>
                                        <span class="old-price">Rs. 2,200.00</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Wishlist Item 2 -->
                            <div class="product-card">
                                <div class="product-wishlist active"><i class="fas fa-heart"></i></div>
                                <div class="product-thumb">
                                    <a href="product-detail.php"><img src="https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" alt="Cordless Drill"></a>
                                </div>
                                <div class="product-details">
                                    <h3><a href="product-detail.php">18V Cordless Drill Set</a></h3>
                                    <div class="product-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <span>(18)</span>
                                    </div>
                                    <div class="product-price">
                                        <span class="price">Rs. 12,500.00</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Wishlist Item 3 -->
                            <div class="product-card">
                                <div class="product-wishlist active"><i class="fas fa-heart"></i></div>
                                <div class="product-thumb">
                                    <a href="product-detail.php"><img src="https://images.unsplash.com/photo-1560439514-4e9645039924?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" alt="Interior Paint"></a>
                                </div>
                                <div class="product-details">
                                    <h3><a href="product-detail.php">Premium Interior Paint 5L</a></h3>
                                    <div class="product-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <span>(29)</span>
                                    </div>
                                    <div class="product-price">
                                        <span class="price">Rs. 4,200.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter" style="padding: 60px 0; background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80') no-repeat center center; background-size: cover; color: white;">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3>Subscribe to Our Newsletter</h3>
                    <p>Get updates on special offers and new products</p>
                </div>
                <form class="newsletter-form" style="display: flex; flex: 1; min-width: 300px; max-width: 500px;">
                    <input type="email" placeholder="Your email address" required style="flex: 1; padding: 15px; border: none; border-radius: 4px 0 0 4px; background-color: var(--dark-card); color: var(--text-primary);">
                    <button type="submit" class="btn btn-primary" style="border-radius: 0 4px 4px 0; padding: 0 25px;">Subscribe</button>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="services.php">Services</a></li>
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
                        <li><a href="size-guide.php">Size Guide</a></li>
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
    <a href="#" class="back-to-top" id="backToTop" style="position: fixed; bottom: 20px; right: 20px; width: 40px; height: 40px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; opacity: 0; pointer-events: none; transition: var(--transition); z-index: 99;">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Account navigation
            const menuLinks = document.querySelectorAll('.account-menu a[data-section]');
            const accountSections = document.querySelectorAll('.account-section');
            
            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all links and sections
                    menuLinks.forEach(l => l.classList.remove('active'));
                    accountSections.forEach(s => s.classList.remove('active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                    
                    // Show corresponding section
                    const sectionId = this.getAttribute('data-section');
                    document.getElementById(sectionId).classList.add('active');
                });
            });
            
            // Toggle password visibility
            const toggleButtons = document.querySelectorAll('.toggle-password');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle eye icon
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            });
            
            // Add address functionality
            const addAddressBtn = document.getElementById('addAddressCard');
            addAddressBtn.addEventListener('click', function() {
                alert('Opening address form... In a real application, this would open a modal with an address form.');
            });
            
            // Set default address
            const setDefaultLinks = document.querySelectorAll('.set-default');
            setDefaultLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('Default address changed successfully!');
                });
            });
            
            // Back to top button
            const backToTop = document.getElementById('backToTop');
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('active');
                    backToTop.style.opacity = '1';
                    backToTop.style.pointerEvents = 'all';
                } else {
                    backToTop.classList.remove('active');
                    backToTop.style.opacity = '0';
                    backToTop.style.pointerEvents = 'none';
                }
            });
            
            backToTop.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
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
        });
    </script>
</body>
</html>