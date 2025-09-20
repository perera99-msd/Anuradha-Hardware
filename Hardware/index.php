<?php
// Include database connection
include('anuradha-admin/includes/db.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart and wishlist if they don't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// If user is logged in, sync cart and wishlist with database
if ($is_logged_in) {
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
    
    // Fetch user's wishlist items from database
    $wish_stmt = $conn->prepare("
        SELECT p.id, p.name, p.price, p.image, p.stock 
        FROM wishlist_items wi 
        JOIN products p ON wi.product_id = p.id 
        WHERE wi.user_id = ?
    ");
    $wish_stmt->bind_param("i", $userId);
    $wish_stmt->execute();
    $wish_result = $wish_stmt->get_result();
    
    // Update session wishlist with database items
    $_SESSION['wishlist'] = [];
    while ($item = $wish_result->fetch_assoc()) {
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
    // For guest users, sync with session-based cart and wishlist items
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
            $_SESSION['cart'][$productId]['quantity'] = max(
                $_SESSION['cart'][$productId]['quantity'],
                $item['quantity']
            );
        } else {
            $_SESSION['cart'][$productId] = [
                'id' => $productId,
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'image' => $item['image']
            ];
        }
    }
    
    // Fetch session wishlist items from database
    $wish_stmt = $conn->prepare("
        SELECT p.id, p.name, p.price, p.image, p.stock 
        FROM wishlist_items wi 
        JOIN products p ON wi.product_id = p.id 
        WHERE wi.session_id = ?
    ");
    $wish_stmt->bind_param("s", $sessionId);
    $wish_stmt->execute();
    $wish_result = $wish_stmt->get_result();
    
    // Update session wishlist with database items
    $_SESSION['wishlist'] = [];
    while ($item = $wish_result->fetch_assoc()) {
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

// Calculate cart item count
$itemCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $itemCount += (int)$item['quantity'];
}

// Calculate wishlist count
$wishlistCount = count($_SESSION['wishlist']);

// Categories for search dropdown
$categories = [];
$cat_rs = $conn->query("SELECT name FROM categories ORDER BY name");
while ($row = $cat_rs->fetch_assoc()) {
    $categories[] = $row['name'];
}

// Fetch active sliders ordered by their order number
$sliders = $conn->query("SELECT * FROM home_page_content WHERE section = 'slider' AND is_active = 1 ORDER BY order_num ASC");

// Fetch featured products
$featured_products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_featured = 1 AND p.stock > 0
    ORDER BY p.id DESC 
    LIMIT 8
");

// Get user's wishlist if logged in
$user_wishlist = [];
if ($is_logged_in) {
    $wish_stmt = $conn->prepare("SELECT product_id FROM wishlist_items WHERE user_id = ?");
    $wish_stmt->bind_param("i", $user_id);
    $wish_stmt->execute();
    $wish_result = $wish_stmt->get_result();
    
    while ($wish_item = $wish_result->fetch_assoc()) {
        $user_wishlist[$wish_item['product_id']] = true;
    }
}

// Image resolver function
function resolve_product_image($raw) {
    $raw = trim((string)$raw);
    
    if ($raw === '') return 'images/products/default-product.jpg';
    if (preg_match('#^https?://#i', $raw)) return $raw;
    if (strpos($raw, 'images/') === 0 || strpos($raw, 'img/') === 0) return $raw;
    
    if (strpos($raw, 'uploads/') === 0) {
        $candidate = 'anuradha-admin/' . $raw;
        if (file_exists($candidate)) return $candidate;
    }
    
    if (strpos($raw, '/') !== false) {
        if (file_exists($raw)) return $raw;
        $candidate = 'anuradha-admin/uploads/' . basename($raw);
        if (file_exists($candidate)) return $candidate;
    }
    
    $candidates = [
        'anuradha-admin/uploads/' . $raw,
        'uploads/' . $raw,
        'images/products/' . $raw,
        'uploads/products/' . $raw,
        'img/products/' . $raw,
    ];
    
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) return $candidate;
    }
    
    return 'images/products/default-product.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anuradha Hardware - Your One-Stop Hardware Solution</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/custom.css"> 
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
                        <input type="text" name="search" placeholder="Search for products...">
                        <select name="category">
                            <option value="all">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="user-actions">
                    <?php if ($is_logged_in): ?>
                        <a href="account.php" title="My Account"><i class="fas fa-user"></i></a>
                    <?php else: ?>
                        <a href="login.php" title="Login/Register"><i class="fas fa-user"></i></a>
                    <?php endif; ?>
                    <a href="wishlist.php" class="wishlist-icon" title="My Wishlist">
                        <i class="fas fa-heart"></i>
                        <span class="wishlist-count"><?php echo $wishlistCount; ?></span>
                    </a>
                    <a href="cart.php" class="cart-icon" title="My Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $itemCount; ?></span>
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
                            <a href="products.php?category=Hand Tools">Hand Tools</a>
                            <a href="products.php?category=Power Tools">Power Tools</a>
                            <a href="products.php?category=Gardening Tools">Gardening Tools</a>
                        </div>
                        <div class="dropdown-column">
                            <h4>Building Materials</h4>
                            <a href="products.php?category=Building Materials">Building Materials</a>
                            <a href="products.php?category=Cement & Aggregates">Cement & Aggregates</a>
                            <a href="products.php?category=Bricks & Blocks">Bricks & Blocks</a>
                            <a href="products.php?category=Steel & Rods">Steel & Rods</a>
                        </div>
                        <div class="dropdown-column">
                            <h4>Paints & Decor</h4>
                            <a href="products.php?category=Paint & Decorating">Paints</a>
                            <a href="products.php?category=Wallpapers">Wallpapers</a>
                            <a href="products.php?category=Tiles">Tiles</a>
                        </div>
                        <div class="dropdown-column">
                            <h4>Plumbing</h4>
                            <a href="products.php?category=Plumbing Supplies">Plumbing Supplies</a>
                            <a href="products.php?category=Pipes & Fittings">Pipes & Fittings</a>
                            <a href="products.php?category=Bathroom Fixtures">Bathroom Fixtures</a>
                            <a href="products.php?category=Taps & Faucets">Taps & Faucets</a>
                        </div>
                    </div>
                </li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="offers.php">Special Offers</a></li>
            </ul>
        </div>
    </nav>

    <section class="hero-slider">
        <div class="slider-container">
            <?php if ($sliders->num_rows > 0): ?>
                <?php $active = 'active'; $is_first_slide = true; ?>
                <?php while ($slider = $sliders->fetch_assoc()): 
                    $content = json_decode($slider['content_value'], true);
                ?>
                    <div class="slide <?php echo $active; ?>">
                        <?php if ($slider['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($slider['image_path']); ?>" alt="<?php echo htmlspecialchars($content['title'] ?? ''); ?>">
                        <?php endif; ?>
                        <div class="slide-content">
                            <?php if ($is_logged_in && $is_first_slide): ?>
                                <p class="welcome-user">Welcome back, <?php echo htmlspecialchars($_SESSION['user_first_name'] ?? 'User'); ?>!</p>
                            <?php endif; ?>
                            <h2><?php echo htmlspecialchars($content['title'] ?? ''); ?></h2>
                            <p><?php echo htmlspecialchars($content['description'] ?? ''); ?></p>
                            <?php if (!empty($content['button_text'])): ?>
                                <a href="<?php echo htmlspecialchars($content['button_link'] ?? '#'); ?>" class="btn"><?php echo htmlspecialchars($content['button_text']); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php $active = ''; $is_first_slide = false; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="slide active">
                    <img src="images/slider/1.jpeg" alt="Hardware Products">
                    <div class="slide-content">
                        <?php if ($is_logged_in): ?>
                            <p class="welcome-user">Welcome back, <?php echo htmlspecialchars($_SESSION['user_first_name'] ?? 'User'); ?>!</p>
                        <?php endif; ?>
                        <h2>Quality Hardware Solutions</h2>
                        <p>Everything you need for your construction and home improvement projects</p>
                        <a href="products.php" class="btn">Shop Now</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="slider-controls">
                <button class="prev-slide"><i class="fas fa-chevron-left"></i></button>
                <button class="next-slide"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="slider-dots"></div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="feature-box">
                <div class="feature-icon"><i class="fas fa-truck"></i></div>
                <div class="feature-content"><h3>Free Delivery</h3><p>For orders over Rs. 10,000</p></div>
            </div>
            <div class="feature-box">
                <div class="feature-icon"><i class="fas fa-undo"></i></div>
                <div class="feature-content"><h3>Easy Returns</h3><p>30-day return policy</p></div>
            </div>
            <div class="feature-box">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="feature-content"><h3>Quality Guarantee</h3><p>We stand behind our products</p></div>
            </div>
            <div class="feature-box">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <div class="feature-content"><h3>24/7 Support</h3><p>Dedicated customer service</p></div>
            </div>
        </div>
    </section>

    <section class="categories">
        <div class="container">
            <h2 class="section-title">Shop by Category</h2>
            <div class="category-grid">
                <a href="products.php?category=Hand Tools" class="category-card"><img src="images/categories/toolls.jpeg" alt="Tools"><div class="category-overlay"><h3>Tools</h3><p>Shop Now <i class="fas fa-arrow-right"></i></p></div></a>
                <a href="products.php?category=Building Materials" class="category-card"><img src="images/categories/building.jpeg" alt="Building Materials"><div class="category-overlay"><h3>Building Materials</h3><p>Shop Now <i class="fas fa-arrow-right"></i></p></div></a>
                <a href="products.php?category=Paint & Decorating" class="category-card"><img src="images/categories/paint.jpeg" alt="Paints"><div class="category-overlay"><h3>Paints & Decor</h3><p>Shop Now <i class="fas fa-arrow-right"></i></p></div></a>
                <a href="products.php?category=Plumbing Supplies" class="category-card"><img src="images/categories/plumb.jpeg" alt="Plumbing"><div class="category-overlay"><h3>Plumbing</h3><p>Shop Now <i class="fas fa-arrow-right"></i></p></div></a>
                <a href="products.php?category=Electrical Supplies" class="category-card"><img src="images/categories/elec.jpeg" alt="Electrical"><div class="category-overlay"><h3>Electrical</h3><p>Shop Now <i class="fas fa-arrow-right"></i></p></div></a>
                <a href="products.php?category=Hardware & Fasteners" class="category-card"><img src="images/categories/hd.jpeg" alt="Hardware"><div class="category-overlay"><h3>Hardware</h3><p>Shop Now <i class="fas fa-arrow-right"></i></p></div></a>
            </div>
        </div>
    </section>

    <section class="featured-products">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Products</h2>
                <a href="products.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="product-grid">
                <?php if ($featured_products->num_rows > 0): ?>
                    <?php while ($product = $featured_products->fetch_assoc()): 
                        $image_path = resolve_product_image($product['image']);
                        $is_in_wishlist = $is_logged_in && isset($user_wishlist[$product['id']]);
                    ?>
                        <div class="product-card">
                            <?php if (isset($product['tag']) && !empty($product['tag'])): ?>
                                <div class="product-badge"><?php echo htmlspecialchars($product['tag']); ?></div>
                            <?php endif; ?>
                            <div class="product-wishlist <?php echo $is_in_wishlist ? 'in-wishlist' : ''; ?>" 
                                 data-id="<?php echo (int)$product['id']; ?>" 
                                 data-in-wishlist="<?php echo $is_in_wishlist ? 'true' : 'false'; ?>">
                                <i class="<?php echo $is_in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                            </div>
                            <div class="product-thumb">
                                <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null; this.src='images/products/default-product.jpg'">
                                </a>
                                <div class="product-actions">
                                    <button class="quick-view" data-id="<?php echo (int)$product['id']; ?>"><i class="fas fa-eye"></i></button>
                                    <button class="add-to-cart" data-id="<?php echo (int)$product['id']; ?>" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>><i class="fas fa-shopping-cart"></i></button>
                                </div>
                            </div>
                            <div class="product-details">
                                <h3><a href="product-detail.php?id=<?php echo (int)$product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                <div class="product-rating">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                    <span>(<?php echo rand(5,50); ?>)</span>
                                </div>
                                <div class="product-price">
                                    <span class="price">Rs. <?php echo number_format((float)$product['price'], 2); ?></span>
                                    <?php if (isset($product['old_price']) && (float)$product['old_price'] > (float)$product['price']): ?>
                                        <span class="old-price">Rs. <?php echo number_format((float)$product['old_price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No featured products available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="promo-banner">
        <div class="container">
            <div class="banner-content">
                <h2>Professional Contractor Discounts</h2>
                <p>Register your business and get special pricing on bulk orders</p>
                <a href="register.php" class="btn btn-outline">Register Now</a>
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
                        <li><a href="#">My Account</a></li>
                        <li><a href="#">Order Tracking</a></li>
                        <li><a href="wishlist.php">Wishlist</a></li>
                        <li><a href="#">Returns & Exchanges</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact Info</h4>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Galle Road, Colombo 04, Sri Lanka</li>
                        <li><i class="fas fa-phone"></i> +94 112 345 678</li>
                        <li><i class="fas fa-envelope"></i> info@anuradhahardware.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 8:30AM - 6:00PM</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Anuradha Hardware. All Rights Reserved.</p>
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
</body>
</html>