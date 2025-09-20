<?php
// Include database connection
include('anuradha-admin/includes/db.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize wishlist if it doesn't exist
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// If user is logged in, sync wishlist with database
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    // Fetch user's wishlist items from database
    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.price, p.image, p.stock 
        FROM wishlist_items wi 
        JOIN products p ON wi.product_id = p.id 
        WHERE wi.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Update session wishlist with database items
    $_SESSION['wishlist'] = [];
    while ($item = $result->fetch_assoc()) {
        $productId = $item['id'];
        $_SESSION['wishlist'][$productId] = [
            'id' => $productId,
            'name' => $item['name'],
            'price' => $item['price'],
            'image' => $item['image'],
            'stock' => $item['stock']
        ];
    }
} else {
    // For guest users, sync with session-based wishlist items
    $sessionId = session_id();
    
    // Fetch session wishlist items from database
    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.price, p.image, p.stock 
        FROM wishlist_items wi 
        JOIN products p ON wi.product_id = p.id 
        WHERE wi.session_id = ?
    ");
    $stmt->bind_param("s", $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Update session wishlist with database items
    $_SESSION['wishlist'] = [];
    while ($item = $result->fetch_assoc()) {
        $productId = $item['id'];
        $_SESSION['wishlist'][$productId] = [
            'id' => $productId,
            'name' => $item['name'],
            'price' => $item['price'],
            'image' => $item['image'],
            'stock' => $item['stock']
        ];
    }
}

// Handle wishlist actions via AJAX
if (isset($_POST['action'])) {
    $response = ['success' => false, 'message' => 'Invalid action'];
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    switch ($_POST['action']) {
        case 'add':
            if ($productId > 0) {
                // Check if product already in wishlist
                if (isset($_SESSION['wishlist'][$productId])) {
                    $response = ['success' => false, 'message' => 'Product already in wishlist'];
                } else {
                    // Fetch product details from database
                    $stmt = $conn->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ?");
                    $stmt->bind_param("i", $productId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $product = $result->fetch_assoc();
                        
                        $_SESSION['wishlist'][$productId] = [
                            'id' => $productId,
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'image' => $product['image'],
                            'stock' => $product['stock']
                        ];
                        
                        // Save to database if user is logged in
                        if (isset($_SESSION['user_id'])) {
                            $userId = $_SESSION['user_id'];
                            $insertStmt = $conn->prepare("INSERT INTO wishlist_items (user_id, product_id) VALUES (?, ?)");
                            $insertStmt->bind_param("ii", $userId, $productId);
                            $insertStmt->execute();
                        } else {
                            // For guest users
                            $sessionId = session_id();
                            $insertStmt = $conn->prepare("INSERT INTO wishlist_items (session_id, product_id) VALUES (?, ?)");
                            $insertStmt->bind_param("si", $sessionId, $productId);
                            $insertStmt->execute();
                        }
                        
                        $response = [
                            'success' => true, 
                            'message' => 'Product added to wishlist',
                            'wishlistCount' => count($_SESSION['wishlist'])
                        ];
                    } else {
                        $response = ['success' => false, 'message' => 'Product not found'];
                    }
                }
            }
            break;
            
        case 'remove':
            if ($productId > 0 && isset($_SESSION['wishlist'][$productId])) {
                unset($_SESSION['wishlist'][$productId]);
                
                // Remove from database if user is logged in
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                    $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?");
                    $deleteStmt->bind_param("ii", $userId, $productId);
                    $deleteStmt->execute();
                } else {
                    // For guest users
                    $sessionId = session_id();
                    $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE session_id = ? AND product_id = ?");
                    $deleteStmt->bind_param("si", $sessionId, $productId);
                    $deleteStmt->execute();
                }
                
                $response = [
                    'success' => true, 
                    'message' => 'Product removed from wishlist',
                    'wishlistCount' => count($_SESSION['wishlist'])
                ];
            } else {
                $response = ['success' => false, 'message' => 'Product not in wishlist'];
            }
            break;
            
        case 'move_to_cart':
            if ($productId > 0 && isset($_SESSION['wishlist'][$productId])) {
                // Initialize cart if it doesn't exist
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                
                // Add to cart
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId]['quantity']++;
                } else {
                    $_SESSION['cart'][$productId] = [
                        'id' => $productId,
                        'name' => $_SESSION['wishlist'][$productId]['name'],
                        'price' => $_SESSION['wishlist'][$productId]['price'],
                        'quantity' => 1,
                        'image' => $_SESSION['wishlist'][$productId]['image']
                    ];
                }
                
                // Update cart in database if user is logged in
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
                
                // Remove from wishlist
                unset($_SESSION['wishlist'][$productId]);
                
                // Remove from database if user is logged in
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                    $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?");
                    $deleteStmt->bind_param("ii", $userId, $productId);
                    $deleteStmt->execute();
                } else {
                    // For guest users
                    $sessionId = session_id();
                    $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE session_id = ? AND product_id = ?");
                    $deleteStmt->bind_param("si", $sessionId, $productId);
                    $deleteStmt->execute();
                }
                
                // Calculate cart count
                $cartCount = 0;
                foreach ($_SESSION['cart'] as $item) {
                    $cartCount += $item['quantity'];
                }
                
                $response = [
                    'success' => true, 
                    'message' => 'Product moved to cart',
                    'wishlistCount' => count($_SESSION['wishlist']),
                    'cartCount' => $cartCount
                ];
            } else {
                $response = ['success' => false, 'message' => 'Product not in wishlist'];
            }
            break;
            
        case 'clear':
            $_SESSION['wishlist'] = [];
            
            // Clear database if user is logged in
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ?");
                $deleteStmt->bind_param("i", $userId);
                $deleteStmt->execute();
            } else {
                // For guest users
                $sessionId = session_id();
                $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE session_id = ?");
                $deleteStmt->bind_param("s", $sessionId);
                $deleteStmt->execute();
            }
            
            $response = [
                'success' => true, 
                'message' => 'Wishlist cleared successfully',
                'wishlistCount' => 0
            ];
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Calculate cart totals for header
$itemCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $itemCount += $item['quantity'];
    }
}

// Function to resolve product image path
function resolve_product_image($raw) {
    $raw = trim((string)$raw);
    
    // If empty, return default image
    if ($raw === '') return 'images/products/default-product.jpg';
    
    // If it's already a full URL, return it
    if (preg_match('#^https?://#i', $raw)) return $raw;
    
    // If it starts with images/ or img/, return as is
    if (strpos($raw, 'images/') === 0 || strpos($raw, 'img/') === 0) return $raw;
    
    // If it starts with uploads/, check if it exists in anuradha-admin/uploads
    if (strpos($raw, 'uploads/') === 0) {
        $candidate = 'anuradha-admin/' . $raw;
        if (file_exists($candidate)) {
            return $candidate;
        }
    }
    
    // If it contains a path separator, try it directly
    if (strpos($raw, '/') !== false) {
        // Check if file exists
        if (file_exists($raw)) {
            return $raw;
        }
        // If not, try the anuradha-admin/uploads directory
        $candidate = 'anuradha-admin/uploads/' . basename($raw);
        if (file_exists($candidate)) {
            return $candidate;
        }
    }
    
    // For plain filenames, check common locations with priority to anuradha-admin/uploads
    $candidates = [
        'anuradha-admin/uploads/' . $raw, // Primary location
        'uploads/' . $raw,
        'images/products/' . $raw,
        'uploads/products/' . $raw,
        'img/products/' . $raw,
    ];
    
    // Return the first candidate that exists, or the default image
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            return $candidate;
        }
    }
    
    // If nothing found, return the default image
    return 'images/products/default-product.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/wishlist.css">
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
        
        .toast.error {
            background: #ff4757;
        }
        
        .wishlist-count.pulse, .cart-count.pulse {
            animation: pulse 0.3s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        /* Button styles */
        .btn-move-to-cart {
            background: #f57224;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .btn-move-to-cart:hover {
            background: #e5631d;
        }
        
        .btn-remove {
            color: #ff4757;
            padding: 8px 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        
        .btn-remove:hover {
            color: #ff2e43;
        }
        
        .clear-wishlist {
            background: #ff4757;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .clear-wishlist:hover {
            background: #ff2e43;
        }
        
        .wishlist-loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .wishlist-loading i {
            font-size: 24px;
            color: #f57224;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .item-sku {
            display: none; /* Hide SKU since we don't have it in the database */
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
                    <a href="wishlist.php" class="wishlist-icon active">
                        <i class="fas fa-heart"></i>
                        <span class="wishlist-count"><?php echo count($_SESSION['wishlist']); ?></span>
                    </a>
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
            <h1>My Wishlist</h1>
            <p>Your favorite items saved for later</p>
        </div>
    </section>

    <!-- Wishlist Section -->
    <section class="wishlist-section">
        <div class="container">
            <div class="wishlist-container">
                <?php if (count($_SESSION['wishlist']) > 0): ?>
                    <div class="wishlist-header">
                        <h2>Your Wishlist (<span id="wishlist-count"><?php echo count($_SESSION['wishlist']); ?></span> items)</h2>
                        <a href="#" id="clear-wishlist" class="clear-wishlist">
                            <i class="fas fa-trash"></i> Clear Wishlist
                        </a>
                    </div>
                    
                    <div class="wishlist-items" id="wishlist-items">
                        <?php foreach ($_SESSION['wishlist'] as $item): 
                            $image_path = resolve_product_image($item['image']);
                        ?>
                            <div class="wishlist-item" data-id="<?php echo $item['id']; ?>">
                                <div class="item-image">
                                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-details">
                                    <h3><a href="product-detail.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                                    <div class="item-price">
                                        Rs. <?php echo number_format($item['price'], 2); ?>
                                    </div>
                                    <div class="item-stock <?php echo $item['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                        <?php echo $item['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button class="btn-move-to-cart" data-id="<?php echo $item['id']; ?>">
                                        <i class="fas fa-shopping-cart"></i> Move to Cart
                                    </button>
                                    <button class="btn-remove" data-id="<?php echo $item['id']; ?>">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="wishlist-loading" id="wishlist-loading">
                        <i class="fas fa-spinner"></i>
                        <p>Processing...</p>
                    </div>
                <?php else: ?>
                    <div class="empty-wishlist">
                        <div class="empty-wishlist-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h2>Your wishlist is empty</h2>
                        <p>You haven't added any items to your wishlist yet.</p>
                        <a href="products.php" class="btn">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($_SESSION['wishlist']) > 0): ?>
                <!-- Recently Viewed Products -->
                <div class="related-products">
                    <h2 class="section-title">You Might Also Like</h2>
                    <div class="product-grid">
                        <!-- Related products would be loaded here -->
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
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="#">My Account</a></li>
                        <li><a href="#">Order Tracking</a></li>
                        <li><a href="wishlist.php">Wishlist</a></li>
                        <li><a href="#">Returns & Exchanges</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Galle Road, Colombo, Sri Lanka</li>
                        <li><i class="fas fa-phone"></i> +94 112 345 678</li>
                        <li><i class="fas fa-envelope"></i> info@anuradhahardware.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 8:00 AM - 6:00 PM</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 Anuradha Hardware. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show toast notification
        function showToast(message, isError = false) {
            const toast = document.createElement('div');
            toast.className = isError ? 'toast error' : 'toast';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }
        
        // Update wishlist count
        function updateWishlistCount(count) {
            const wishlistCount = document.querySelector('.wishlist-count');
            const wishlistCountText = document.getElementById('wishlist-count');
            
            if (wishlistCount) {
                wishlistCount.textContent = count;
                wishlistCount.classList.add('pulse');
                setTimeout(() => {
                    wishlistCount.classList.remove('pulse');
                }, 300);
            }
            
            if (wishlistCountText) {
                wishlistCountText.textContent = count;
            }
        }
        
        // Update cart count
        function updateCartCount(count) {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = count;
                cartCount.classList.add('pulse');
                setTimeout(() => {
                    cartCount.classList.remove('pulse');
                }, 300);
            }
        }
        
        // Show/hide loading
        function setLoading(loading) {
            const loadingElement = document.getElementById('wishlist-loading');
            if (loadingElement) {
                loadingElement.style.display = loading ? 'block' : 'none';
            }
        }
        
        // Handle wishlist actions
        function handleWishlistAction(action, productId, productName = '') {
            setLoading(true);
            
            const formData = new FormData();
            formData.append('action', action);
            if (productId) {
                formData.append('product_id', productId);
            }
            
            fetch('wishlist.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                setLoading(false);
                
                if (data.success) {
                    showToast(data.message);
                    
                    // Update counts
                    if (data.wishlistCount !== undefined) {
                        updateWishlistCount(data.wishlistCount);
                    }
                    
                    if (data.cartCount !== undefined) {
                        updateCartCount(data.cartCount);
                    }
                    
                    // Handle different actions
                    if (action === 'remove') {
                        // Remove the item from the DOM
                        const itemElement = document.querySelector(`.wishlist-item[data-id="${productId}"]`);
                        if (itemElement) {
                            itemElement.remove();
                        }
                        
                        // If no items left, show empty state
                        if (data.wishlistCount === 0) {
                            document.querySelector('.wishlist-container').innerHTML = `
                                <div class="empty-wishlist">
                                    <div class="empty-wishlist-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <h2>Your wishlist is empty</h2>
                                    <p>You haven't added any items to your wishlist yet.</p>
                                    <a href="products.php" class="btn">Start Shopping</a>
                                </div>
                            `;
                        }
                    } else if (action === 'move_to_cart') {
                        // Remove the item from the DOM
                        const itemElement = document.querySelector(`.wishlist-item[data-id="${productId}"]`);
                        if (itemElement) {
                            itemElement.remove();
                        }
                        
                        // If no items left, show empty state
                        if (data.wishlistCount === 0) {
                            document.querySelector('.wishlist-container').innerHTML = `
                                <div class="empty-wishlist">
                                    <div class="empty-wishlist-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <h2>Your wishlist is empty</h2>
                                    <p>You haven't added any items to your wishlist yet.</p>
                                    <a href="products.php" class="btn">Start Shopping</a>
                                </div>
                            `;
                        }
                    } else if (action === 'clear') {
                        // Clear all items from the DOM
                        document.querySelector('.wishlist-container').innerHTML = `
                            <div class="empty-wishlist">
                                <div class="empty-wishlist-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h2>Your wishlist is empty</h2>
                                <p>You haven't added any items to your wishlist yet.</p>
                                <a href="products.php" class="btn">Start Shopping</a>
                            </div>
                        `;
                    }
                } else {
                    showToast(data.message, true);
                }
            })
            .catch(error => {
                setLoading(false);
                showToast('An error occurred. Please try again.', true);
                console.error('Error:', error);
            });
        }
        
        // Add event listeners for remove buttons
        document.querySelectorAll('.btn-remove').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.closest('.wishlist-item').querySelector('h3 a').textContent;
                
                if (confirm(`Are you sure you want to remove "${productName}" from your wishlist?`)) {
                    handleWishlistAction('remove', productId, productName);
                }
            });
        });
        
        // Add event listeners for move to cart buttons
        document.querySelectorAll('.btn-move-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.closest('.wishlist-item').querySelector('h3 a').textContent;
                
                handleWishlistAction('move_to_cart', productId, productName);
            });
        });
        
        // Add event listener for clear wishlist button
        const clearWishlistBtn = document.getElementById('clear-wishlist');
        if (clearWishlistBtn) {
            clearWishlistBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to clear your entire wishlist?')) {
                    handleWishlistAction('clear');
                }
            });
        }
    });
    </script>
</body>
</html>