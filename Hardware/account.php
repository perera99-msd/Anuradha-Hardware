<?php
// Include database connection
include('anuradha-admin/includes/db.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile update
$update_success = '';
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);

    // Update user data
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, postal_code = ?, country = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $first_name, $last_name, $phone, $address, $city, $postal_code, $country, $user_id);

    if ($stmt->execute()) {
        $update_success = 'Profile updated successfully!';
        // Update session variables
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        $_SESSION['user_first_name'] = $first_name;
        // Refresh user data
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['phone'] = $phone;
        $user['address'] = $address;
        $user['city'] = $city;
        $user['postal_code'] = $postal_code;
        $user['country'] = $country;
    } else {
        $update_error = 'Failed to update profile. Please try again.';
    }
    $stmt->close();
}

// Handle password change
$password_success = '';
$password_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);

                if ($stmt->execute()) {
                    $password_success = 'Password changed successfully!';
                } else {
                    $password_error = 'Failed to change password. Please try again.';
                }
                $stmt->close();
            } else {
                $password_error = 'New password must be at least 6 characters long.';
            }
        } else {
            $password_error = 'New passwords do not match.';
        }
    } else {
        $password_error = 'Current password is incorrect.';
    }
}

// Get user orders - FIXED: Changed user_id to customer_id and order_date to created_at
$orders = [];
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// Get wishlist items - FIXED: Changed table name from wishlist to wishlist_items
$wishlist = [];
$stmt = $conn->prepare("SELECT p.id, p.name, p.price, p.image FROM wishlist_items w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $wishlist[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/account.css">
</head>
<body>
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
                    <a href="account.php" class="active"><i class="fas fa-user"></i></a>
                    <a href="wishlist.php"><i class="fas fa-heart"></i></a>
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

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

    <section class="page-header">
        <div class="container">
            <h1>My Account</h1>
            <p>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</p>
        </div>
    </section>

    <section class="account-section">
        <div class="account-container">
            <div class="account-sidebar">
                <div class="account-user">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-info">
                        <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>

                <ul class="account-menu">
                    <li><a href="#dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#profile"><i class="fas fa-user"></i> Profile Information</a></li>
                    <li><a href="#orders"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                    <li><a href="#wishlist"><i class="fas fa-heart"></i> My Wishlist</a></li>
                    <li><a href="#addresses"><i class="fas fa-map-marker-alt"></i> Addresses</a></li>
                    <li><a href="#password"><i class="fas fa-lock"></i> Change Password</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>

            <div class="account-content">
                <div id="dashboard" class="account-tab">
                    <h2>Account Dashboard</h2>
                    <div class="dashboard-grid">
                        <div class="overview-card">
                            <div class="card-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="card-content">
                                <h3>Recent Orders</h3>
                                <p>You have <?php echo count($orders); ?> recent orders</p>
                                <a href="#orders" class="view-all">View All Orders <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="overview-card">
                            <div class="card-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="card-content">
                                <h3>Wishlist</h3>
                                <p>You have <?php echo count($wishlist); ?> items in your wishlist</p>
                                <a href="#wishlist" class="view-all">View Wishlist <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="overview-card">
                            <div class="card-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="card-content">
                                <h3>Account Information</h3>
                                <p>Manage your personal information</p>
                                <a href="#profile" class="view-all">Edit Profile <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="overview-card">
                            <div class="card-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="card-content">
                                <h3>Address Book</h3>
                                <p>Manage your shipping addresses</p>
                                <a href="#addresses" class="view-all">Manage Addresses <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="recent-orders">
                        <h3>Recent Orders</h3>
                        <?php if (count($orders) > 0): ?>
                            <div class="orders-table-container">
                                <table class="orders-table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                <td><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                                <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <a href="order-details.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="btn-view">View</a>
                                                    <?php if ($order['status'] == 'Pending' || $order['status'] == 'Processing'): ?>
                                                        <a href="#" class="btn-cancel">Cancel</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>You haven't placed any orders yet.</p>
                            <a href="products.php" class="btn btn-primary">Start Shopping</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="profile" class="account-tab">
                    <h2>Profile Information</h2>

                    <?php if (!empty($update_success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($update_success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($update_error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($update_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="account.php">
                        <div class="form-row double">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small>Email cannot be changed. Contact support if needed.</small>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>

                        <div class="form-row double">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="country" required>
                                <option value="Sri Lanka" <?php echo ($user['country'] == 'Sri Lanka') ? 'selected' : ''; ?>>Sri Lanka</option>
                                </select>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>

                <div id="password" class="account-tab">
                    <h2>Change Password</h2>

                    <?php if (!empty($password_success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($password_success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($password_error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($password_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="account.php">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="current_password" name="current_password" required>
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="new_password" name="new_password" required>
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
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-match" class="password-feedback"></div>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>

                <div id="orders" class="account-tab">
                    <h2>My Orders</h2>
                    <p>Order history and details will be displayed here.</p>
                </div>

                <div id="wishlist" class="account-tab">
                    <h2>My Wishlist</h2>
                    <p>Your saved items will be displayed here.</p>
                </div>

                <div id="addresses" class="account-tab">
                    <h2>Address Book</h2>
                    <p>Your saved addresses will be displayed here.</p>
                </div>
            </div>
        </div>
    </section>

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
                    <img src="images/payments/American Express Gift Cards Coupons.jpeg', 'alt='American Express">
                    <img src="images/payments/c6b792c8-f1d2-4fe2-9474-bb1e8faaa66b.jpeg" alt="PayPal">
                </div>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
    <script src="js/account.js"></script>
</body>
</html>