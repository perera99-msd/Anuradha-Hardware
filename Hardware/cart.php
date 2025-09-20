<?php
// Include database connection
include('anuradha-admin/includes/db.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// If user is logged in, sync cart with database
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    // Fetch user's cart items from database
    $stmt = $conn->prepare("
        SELECT ci.product_id, ci.quantity, p.name, p.price, p.image 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        WHERE ci.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Merge database cart with session cart
    while ($item = $result->fetch_assoc()) {
        $productId = $item['product_id'];
        
        if (isset($_SESSION['cart'][$productId])) {
            // Use the larger quantity between session and database
            $_SESSION['cart'][$productId]['quantity'] = max(
                $_SESSION['cart'][$productId]['quantity'],
                $item['quantity']
            );
        } else {
            // Add item from database to session
            $_SESSION['cart'][$productId] = [
                'id' => $productId,
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'image' => $item['image']
            ];
        }
    }
} else {
    // For guest users, sync with session-based cart items
    $sessionId = session_id();
    
    // Fetch session cart items from database
    $stmt = $conn->prepare("
        SELECT ci.product_id, ci.quantity, p.name, p.price, p.image 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        WHERE ci.session_id = ?
    ");
    $stmt->bind_param("s", $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Merge database cart with session cart
    while ($item = $result->fetch_assoc()) {
        $productId = $item['product_id'];
        
        if (isset($_SESSION['cart'][$productId])) {
            // Use the larger quantity between session and database
            $_SESSION['cart'][$productId]['quantity'] = max(
                $_SESSION['cart'][$productId]['quantity'],
                $item['quantity']
            );
        } else {
            // Add item from database to session
            $_SESSION['cart'][$productId] = [
                'id' => $productId,
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'image' => $item['image']
            ];
        }
    }
}

// Handle cart actions
if (isset($_GET['action'])) {
    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    switch ($_GET['action']) {
        case 'add':
            if ($productId > 0) {
                // Fetch product details from database
                $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $product = $result->fetch_assoc();
                    
                    if (isset($_SESSION['cart'][$productId])) {
                        $_SESSION['cart'][$productId]['quantity']++;
                    } else {
                        $_SESSION['cart'][$productId] = [
                            'id' => $productId,
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'quantity' => 1,
                            'image' => $product['image']
                        ];
                    }
                    
                    // Update database if user is logged in
                    if (isset($_SESSION['user_id'])) {
                        $userId = $_SESSION['user_id'];
                        
                        // Check if product exists in user's cart
                        $checkStmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
                        $checkStmt->bind_param("ii", $userId, $productId);
                        $checkStmt->execute();
                        $checkResult = $checkStmt->get_result();
                        
                        if ($checkResult->num_rows > 0) {
                            $cartItem = $checkResult->fetch_assoc();
                            $newQuantity = $cartItem['quantity'] + 1;
                            
                            $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
                            $updateStmt->bind_param("ii", $newQuantity, $cartItem['id']);
                            $updateStmt->execute();
                        } else {
                            $insertStmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1)");
                            $insertStmt->bind_param("ii", $userId, $productId);
                            $insertStmt->execute();
                        }
                    } else {
                        // For guest users
                        $sessionId = session_id();
                        
                        // Check if product exists in session cart
                        $checkStmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE session_id = ? AND product_id = ?");
                        $checkStmt->bind_param("si", $sessionId, $productId);
                        $checkStmt->execute();
                        $checkResult = $checkStmt->get_result();
                        
                        if ($checkResult->num_rows > 0) {
                            $cartItem = $checkResult->fetch_assoc();
                            $newQuantity = $cartItem['quantity'] + 1;
                            
                            $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
                            $updateStmt->bind_param("ii", $newQuantity, $cartItem['id']);
                            $updateStmt->execute();
                        } else {
                            $insertStmt = $conn->prepare("INSERT INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, 1)");
                            $insertStmt->bind_param("si", $sessionId, $productId);
                            $insertStmt->execute();
                        }
                    }
                    
                    header('Location: cart.php?message=added');
                    exit;
                }
            }
            break;
            
        case 'remove':
            if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
                
                // Remove from database if user is logged in
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                    $deleteStmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
                    $deleteStmt->bind_param("ii", $userId, $productId);
                    $deleteStmt->execute();
                } else {
                    // For guest users
                    $sessionId = session_id();
                    $deleteStmt = $conn->prepare("DELETE FROM cart_items WHERE session_id = ? AND product_id = ?");
                    $deleteStmt->bind_param("si", $sessionId, $productId);
                    $deleteStmt->execute();
                }
                
                header('Location: cart.php?message=removed');
                exit;
            }
            break;
            
        case 'update':
            if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
                $newQuantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                
                if ($newQuantity > 0) {
                    $_SESSION['cart'][$productId]['quantity'] = $newQuantity;
                    
                    // Update database if user is logged in
                    if (isset($_SESSION['user_id'])) {
                        $userId = $_SESSION['user_id'];
                        
                        $checkStmt = $conn->prepare("SELECT id FROM cart_items WHERE user_id = ? AND product_id = ?");
                        $checkStmt->bind_param("ii", $userId, $productId);
                        $checkStmt->execute();
                        $checkResult = $checkStmt->get_result();
                        
                        if ($checkResult->num_rows > 0) {
                            $cartItem = $checkResult->fetch_assoc();
                            $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
                            $updateStmt->bind_param("ii", $newQuantity, $cartItem['id']);
                            $updateStmt->execute();
                        } else {
                            $insertStmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                            $insertStmt->bind_param("iii", $userId, $productId, $newQuantity);
                            $insertStmt->execute();
                        }
                    } else {
                        // For guest users
                        $sessionId = session_id();
                        
                        $checkStmt = $conn->prepare("SELECT id FROM cart_items WHERE session_id = ? AND product_id = ?");
                        $checkStmt->bind_param("si", $sessionId, $productId);
                        $checkStmt->execute();
                        $checkResult = $checkStmt->get_result();
                        
                        if ($checkResult->num_rows > 0) {
                            $cartItem = $checkResult->fetch_assoc();
                            $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
                            $updateStmt->bind_param("ii", $newQuantity, $cartItem['id']);
                            $updateStmt->execute();
                        } else {
                            $insertStmt = $conn->prepare("INSERT INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, ?)");
                            $insertStmt->bind_param("sii", $sessionId, $productId, $newQuantity);
                            $insertStmt->execute();
                        }
                    }
                } else {
                    unset($_SESSION['cart'][$productId]);
                    
                    // Remove from database if user is logged in
                    if (isset($_SESSION['user_id'])) {
                        $userId = $_SESSION['user_id'];
                        $deleteStmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
                        $deleteStmt->bind_param("ii", $userId, $productId);
                        $deleteStmt->execute();
                    } else {
                        // For guest users
                        $sessionId = session_id();
                        $deleteStmt = $conn->prepare("DELETE FROM cart_items WHERE session_id = ? AND product_id = ?");
                        $deleteStmt->bind_param("si", $sessionId, $productId);
                        $deleteStmt->execute();
                    }
                }
                
                header('Location: cart.php?message=updated');
                exit;
            }
            break;
            
        case 'clear':
            $_SESSION['cart'] = [];
            
            // Clear database if user is logged in
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                $deleteStmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
                $deleteStmt->bind_param("i", $userId);
                $deleteStmt->execute();
            } else {
                // For guest users
                $sessionId = session_id();
                $deleteStmt = $conn->prepare("DELETE FROM cart_items WHERE session_id = ?");
                $deleteStmt->bind_param("s", $sessionId);
                $deleteStmt->execute();
            }
            
            header('Location: cart.php?message=cleared');
            exit;
            break;
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/cart.css">
    <style>
        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s, transform 0.3s;
        }
        
        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .cart-count.pulse {
            animation: pulse 0.3s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        /* Fix for image paths */
        .cart-item img {
            max-width: 100px;
            height: auto;
        }
        
        /* Button styles */
        .btn-checkout {
            background: #f57224;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 15px;
        }
        
        .btn-checkout:hover {
            background: #e5631d;
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="account.php"><i class="fas fa-user"></i></a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-user"></i></a>
                    <?php endif; ?>
                    <a href="wishlist.php"><i class="fas fa-heart"></i></a>
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $itemCount; ?></span>
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
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>
    </section>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success">
                    <?php
                    switch ($_GET['message']) {
                        case 'added': echo 'Product added to cart!'; break;
                        case 'removed': echo 'Product removed from cart!'; break;
                        case 'updated': echo 'Cart updated successfully!'; break;
                        case 'cleared': echo 'Cart cleared successfully!'; break;
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="cart-container">
                <?php if (count($_SESSION['cart']) > 0): ?>
                    <div class="cart-items">
                        <div class="cart-header">
                            <h2>Your Cart (<?php echo $itemCount; ?> items)</h2>
                            <a href="cart.php?action=clear" class="clear-cart" onclick="return confirm('Are you sure you want to clear your cart?')">
                                <i class="fas fa-trash"></i> Clear Cart
                            </a>
                        </div>
                        
                        <div class="cart-items-list">
                            <?php foreach ($_SESSION['cart'] as $item): 
                                // Improved image path resolver
                                $image_path = 'images/products/default-product.jpg';
                                if (!empty($item['image'])) {
                                    // Check if it's an absolute URL
                                    if (filter_var($item['image'], FILTER_VALIDATE_URL)) {
                                        $image_path = $item['image'];
                                    } 
                                    // Check if it's a relative path that exists
                                    else if (file_exists($item['image'])) {
                                        $image_path = $item['image'];
                                    } 
                                    // Check if it exists in the admin uploads directory
                                    else if (file_exists('anuradha-admin/' . $item['image'])) {
                                        $image_path = 'anuradha-admin/' . $item['image'];
                                    }
                                    // Check if it's just a filename and exists in uploads
                                    else if (file_exists('anuradha-admin/uploads/' . $item['image'])) {
                                        $image_path = 'anuradha-admin/uploads/' . $item['image'];
                                    }
                                    // Check if it's just a filename and exists in images/products
                                    else if (file_exists('images/products/' . $item['image'])) {
                                        $image_path = 'images/products/' . $item['image'];
                                    }
                                }
                            ?>
                                <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                                    <div class="item-image">
                                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="item-details">
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="item-sku">SKU: AH-<?php echo str_pad($item['id'], 4, '0', STR_PAD_LEFT); ?></p>
                                        <p class="item-stock in-stock">In Stock</p>
                                    </div>
                                    <div class="item-price">
                                        Rs. <?php echo number_format($item['price'], 2); ?>
                                    </div>
                                    <div class="item-quantity">
                                        <form method="POST" action="cart.php?action=update&id=<?php echo $item['id']; ?>">
                                            <button type="button" class="quantity-btn minus" data-id="<?php echo $item['id']; ?>">-</button>
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input" data-id="<?php echo $item['id']; ?>">
                                            <button type="button" class="quantity-btn plus" data-id="<?php echo $item['id']; ?>">+</button>
                                        </form>
                                    </div>
                                    <div class="item-total">
                                        Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                    </div>
                                    <div class="item-actions">
                                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="remove-item" onclick="return confirm('Remove this item from cart?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Order Summary</h3>
                            
                            <div class="summary-row">
                                <span>Subtotal (<?php echo $itemCount; ?> items)</span>
                                <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span>
                                    <?php if ($shipping == 0): ?>
                                        <span class="free-shipping">FREE</span>
                                    <?php else: ?>
                                        Rs. <?php echo number_format($shipping, 2); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Tax (12%)</span>
                                <span>Rs. <?php echo number_format($tax, 2); ?></span>
                            </div>
                            
                            <div class="summary-divider"></div>
                            
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>Rs. <?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <div class="shipping-notice">
                                <?php if ($subtotal < 10000): ?>
                                    <p><i class="fas fa-truck"></i> Add Rs. <?php echo number_format(10000 - $subtotal, 2); ?> more for free shipping!</p>
                                <?php else: ?>
                                    <p><i class="fas fa-check-circle"></i> You qualify for free shipping!</p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
                            <?php else: ?>
                                <a href="login.php?redirect=checkout.php" class="btn btn-checkout">Login to Checkout</a>
                            <?php endif; ?>
                            
                            <div class="continue-shopping">
                                <a href="products.php"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                            </div>
                        </div>
                        
                        <div class="security-badges">
                            <div class="badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>Secure Payment</span>
                            </div>
                            <div class="badge">
                                <i class="fas fa-undo"></i>
                                <span>Easy Returns</span>
                            </div>
                            <div class="badge">
                                <i class="fas fa-headset"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h2>Your cart is empty</h2>
                        <p>Looks like you haven't added any items to your cart yet.</p>
                        <a href="products.php" class="btn">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($_SESSION['cart']) > 0): ?>
                <!-- Recently Viewed Products -->
                <div class="related-products">
                    <h2 class="section-title">You Might Also Like</h2>
                    <div class="product-grid">
                        <div class="product-card">
                            <div class="product-badge">New</div>
                            <div class="product-wishlist"><i class="far fa-heart"></i></div>
                            <div class="product-thumb">
                                <a href="product-detail.php"><img src="images/products/The Basic Set of Tools Every Woman Should Own—and….jpeg" alt="Professional Hammer"></a>
                                <div class="product-actions">
                                    <button class="quick-view"><i class="fas fa-eye"></i></button>
                                    <button class="add-to-cart"><i class="fas fa-shopping-cart"></i></button>
                                </div>
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
                        
                        <div class="product-card">
                            <div class="product-badge">-15%</div>
                            <div class="product-wishlist"><i class="far fa-heart"></i></div>
                            <div class="product-thumb">
                                <a href="product-detail.php"><img src="images/products/SKIL PWR CORE 20 Brushless 20V 1_2 Inch Drill….jpeg" alt="Cordless Drill"></a>
                                <div class="product-actions">
                                    <button class="quick-view"><i class="fas fa-eye"></i></button>
                                    <button class="add-to-cart"><i class="fas fa-shopping-cart"></i></button>
                                </div>
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
                                    <span class="old-price">Rs. 14,700.00</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-wishlist"><i class="far fa-heart"></i></div>
                            <div class="product-thumb">
                                <a href="product-detail.php"><img src="images/products/THE CHALLENGE_After replacing one of the leading….jpeg" alt="Cement Bag"></a>
                                <div class="product-actions">
                                    <button class="quick-view"><i class="fas fa-eye"></i></button>
                                    <button class="add-to-cart"><i class="fas fa-shopping-cart"></i></button>
                                </div>
                            </div>
                            <div class="product-details">
                                <h3><a href="product-detail.php">Portland Cement 50kg</a></h3>
                                <div class="product-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <span>(32)</span>
                                </div>
                                <div class="product-price">
                                    <span class="price">Rs. 1,250.00</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-badge">Best Seller</div>
                            <div class="product-wishlist"><i class="far fa-heart"></i></div>
                            <div class="product-thumb">
                                <a href="product-detail.php"><img src="images/products/How to Choose the Right Paint Finish for Your Room.jpeg" alt="Paint Can"></a>
                                <div class="product-actions">
                                    <button class="quick-view"><i class="fas fa-eye"></i></button>
                                    <button class="add-to-cart"><i class="fas fa-shopping-cart"></i></button>
                                </div>
                            </div>
                            <div class="product-details">
                                <h3><a href="product-detail.php">Premium Interior Paint 4L</a></h3>
                                <div class="product-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                    <span>(41)</span>
                                </div>
                                <div class="product-price">
                                    <span class="price">Rs. 3,800.00</span>
                                    <span class="old-price">Rs. 4,200.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="footer-logo">
                        <img src="images/logo/logo.jpg" alt="Anuradha Hardware">
                    </div>
                    <p>Your trusted partner for quality hardware and building materials since 1995.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="offers.php">Special Offers</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Categories</h3>
                    <ul>
                        <li><a href="products.php?category=tools">Tools</a></li>
                        <li><a href="products.php?category=building">Building Materials</a></li>
                        <li><a href="products.php?category=paints">Paints & Decor</a></li>
                        <li><a href="products.php?category=plumbing">Plumbing</a></li>
                        <li><a href="products.php?category=electrical">Electrical</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Hardware Street, Colombo, Sri Lanka</li>
                        <li><i class="fas fa-phone"></i> +94 112 345 678</li>
                        <li><i class="fas fa-envelope"></i> info@anuradhahardware.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 8:00 AM - 6:00 PM</li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Newsletter</h3>
                    <p>Subscribe to our newsletter for updates on new products and special offers.</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Enter your email">
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Anuradha Hardware. All Rights Reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Returns Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Quantity buttons functionality
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const input = document.querySelector(`.quantity-input[data-id="${productId}"]`);
                let quantity = parseInt(input.value);
                
                if (this.classList.contains('minus')) {
                    if (quantity > 1) {
                        input.value = quantity - 1;
                        updateCartItem(productId, input.value);
                    }
                } else if (this.classList.contains('plus')) {
                    input.value = quantity + 1;
                    updateCartItem(productId, input.value);
                }
            });
        });
        
        // Update cart item quantity
        function updateCartItem(productId, quantity) {
            const form = document.querySelector(`form[action*="id=${productId}"]`);
            const input = form.querySelector('input[name="quantity"]');
            input.value = quantity;
            form.submit();
        }
        
        // Auto-update cart when quantity input changes
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.getAttribute('data-id');
                updateCartItem(productId, this.value);
            });
        });
        
        // Toast notification for cart actions
        <?php if (isset($_GET['message'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?php
                    switch ($_GET['message']) {
                        case 'added': echo 'Product added to cart!'; break;
                        case 'removed': echo 'Product removed from cart!'; break;
                        case 'updated': echo 'Cart updated successfully!'; break;
                        case 'cleared': echo 'Cart cleared successfully!'; break;
                    }
                ?>');
                
                // Update cart count with animation
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = <?php echo $itemCount; ?>;
                    cartCount.classList.add('pulse');
                    setTimeout(() => {
                        cartCount.classList.remove('pulse');
                    }, 300);
                }
            });
        <?php endif; ?>
        
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>