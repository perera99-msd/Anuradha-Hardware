<?php
// Include database connection
include('anuradha-admin/includes/db.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header('Location: cart.php');
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

// Calculate cart totals
$subtotal = 0;
$itemCount = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $itemCount += $item['quantity'];
}

// Shipping calculation
$shipping = $subtotal > 10000 ? 0 : 500;
$tax = $subtotal * 0.12; // 12% tax
$total = $subtotal + $shipping + $tax;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create order
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("id", $user_id, $total);
    
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;
        
        // Add order items
        foreach ($_SESSION['cart'] as $item) {
            $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt2->execute();
            $stmt2->close();
        }
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Redirect to success page
        header('Location: order-success.php?id=' . $order_id);
        exit;
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/checkout.css">
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
                        <input type="text" name="search" placeholder="Search for products...">
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
                    <a href="account.php"><i class="fas fa-user"></i></a>
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
            <h1>Checkout</h1>
            <p>Complete your purchase</p>
        </div>
    </section>

    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container">
            <div class="checkout-container">
                <div class="checkout-steps">
                    <div class="step active">
                        <span class="step-number">1</span>
                        <span class="step-title">Shipping</span>
                    </div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <span class="step-title">Payment</span>
                    </div>
                    <div class="step">
                        <span class="step-number">3</span>
                        <span class="step-title">Confirmation</span>
                    </div>
                </div>
                
                <form method="POST" action="checkout.php" class="checkout-form">
                    <div class="checkout-columns">
                        <div class="checkout-left">
                            <div class="checkout-section">
                                <h3>Shipping Information</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="address">Shipping Address *</label>
                                    <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city">City *</label>
                                        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="postal_code">Postal Code *</label>
                                        <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="checkout-section">
                                <h3>Payment Method</h3>
                                <div class="payment-methods">
                                    <div class="payment-method">
                                        <input type="radio" id="credit-card" name="payment_method" value="credit_card" checked>
                                        <label for="credit-card">
                                            <i class="fas fa-credit-card"></i>
                                            Credit/Debit Card
                                        </label>
                                    </div>
                                    <div class="payment-method">
                                        <input type="radio" id="paypal" name="payment_method" value="paypal">
                                        <label for="paypal">
                                            <i class="fab fa-paypal"></i>
                                            PayPal
                                        </label>
                                    </div>
                                    <div class="payment-method">
                                        <input type="radio" id="bank-transfer" name="payment_method" value="bank_transfer">
                                        <label for="bank-transfer">
                                            <i class="fas fa-university"></i>
                                            Bank Transfer
                                        </label>
                                    </div>
                                    <div class="payment-method">
                                        <input type="radio" id="cash-on-delivery" name="payment_method" value="cash_on_delivery">
                                        <label for="cash-on-delivery">
                                            <i class="fas fa-money-bill-wave"></i>
                                            Cash on Delivery
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="payment-details" id="credit-card-details">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card_number">Card Number *</label>
                                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card_name">Name on Card *</label>
                                            <input type="text" id="card_name" name="card_name" placeholder="John Doe">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="expiry_date">Expiry Date *</label>
                                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                        </div>
                                        <div class="form-group">
                                            <label for="cvv">CVV *</label>
                                            <input type="text" id="cvv" name="cvv" placeholder="123">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checkout-right">
                            <div class="order-summary">
                                <h3>Order Summary</h3>
                                
                                <div class="order-items">
                                    <?php foreach ($_SESSION['cart'] as $item): 
                                        $image_path = 'images/products/default-product.jpg';
                                        if (!empty($item['image'])) {
                                            if (file_exists($item['image'])) {
                                                $image_path = $item['image'];
                                            } elseif (file_exists('anuradha-admin/' . $item['image'])) {
                                                $image_path = 'anuradha-admin/' . $item['image'];
                                            }
                                        }
                                    ?>
                                        <div class="order-item">
                                            <div class="item-image">
                                                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            </div>
                                            <div class="item-details">
                                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                                <p>Qty: <?php echo $item['quantity']; ?></p>
                                            </div>
                                            <div class="item-price">
                                                Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="order-totals">
                                    <div class="total-row">
                                        <span>Subtotal</span>
                                        <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                                    </div>
                                    <div class="total-row">
                                        <span>Shipping</span>
                                        <span>
                                            <?php if ($shipping == 0): ?>
                                                FREE
                                            <?php else: ?>
                                                Rs. <?php echo number_format($shipping, 2); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="total-row">
                                        <span>Tax</span>
                                        <span>Rs. <?php echo number_format($tax, 2); ?></span>
                                    </div>
                                    <div class="total-row grand-total">
                                        <span>Total</span>
                                        <span>Rs. <?php echo number_format($total, 2); ?></span>
                                    </div>
                                </div>
                                
                                <div class="terms-agreement">
                                    <input type="checkbox" id="terms" name="terms" required>
                                    <label for="terms">I agree to the <a href="terms.php">Terms and Conditions</a> and <a href="privacy.php">Privacy Policy</a></label>
                                </div>
                                
                                <button type="submit" class="btn-place-order">Place Order</button>
                                
                                <div class="security-notice">
                                    <i class="fas fa-lock"></i>
                                    <span>Your payment information is secure and encrypted</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <script src="js/checkout.js"></script>
</body>
</html>