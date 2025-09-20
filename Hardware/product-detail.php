<?php
// Include database connection
include('anuradha-admin/includes/db.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    header('Location: products.php');
    exit;
}

// Fetch product details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Fetch related products
$related_stmt = $conn->prepare("SELECT * FROM products 
                               WHERE category_id = ? AND id != ? 
                               ORDER BY RAND() LIMIT 4");
$related_stmt->bind_param("ii", $product['category_id'], $product_id);
$related_stmt->execute();
$related_products = $related_stmt->get_result();

// Calculate cart item count
$itemCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $itemCount += (int)$item['quantity'];
}

// Categories for sidebar
$categories = [];
$cat_rs = $conn->query("SELECT name FROM categories ORDER BY name");
while ($row = $cat_rs->fetch_assoc()) {
    $categories[] = $row['name'];
}

// Image resolver function
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

// Resolve main product image
$main_image = resolve_product_image($product['image']);
$image2 = !empty($product['image2']) ? resolve_product_image($product['image2']) : '';
$image3 = !empty($product['image3']) ? resolve_product_image($product['image3']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/product-detail.css">
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
        
        /* Product detail specific styles */
        .product-detail-container {
            display: flex;
            gap: 30px;
            margin: 30px 0;
        }
        
        .product-images {
            flex: 1;
        }
        
        .product-info {
            flex: 1;
        }
        
        .main-image {
            width: 100%;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .main-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .image-thumbnails {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            padding: 3px;
        }
        
        .thumbnail.active {
            border-color: #f57224;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-badge {
            background: #f57224;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .product-meta {
            display: flex;
            gap: 20px;
            margin: 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .product-price {
            margin: 15px 0;
        }
        
        .current-price {
            font-size: 24px;
            font-weight: bold;
            color: #f57224;
        }
        
        .original-price {
            font-size: 18px;
            text-decoration: line-through;
            color: #999;
            margin-left: 10px;
        }
        
        .discount-percent {
            background: #4CAF50;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .product-stock {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 10px 0;
            font-weight: 500;
        }
        
        .in-stock {
            color: #4CAF50;
        }
        
        .out-of-stock {
            color: #ff4757;
        }
        
        .product-features {
            margin: 20px 0;
        }
        
        .product-features ul {
            list-style: none;
            padding: 0;
        }
        
        .product-features li {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .product-features i {
            color: #4CAF50;
        }
        
        .product-actions {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            align-items: center;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .quantity-btn {
            width: 36px;
            height: 36px;
            background: #f8f8f8;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #productQuantity {
            width: 50px;
            height: 36px;
            text-align: center;
            border: none;
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
        }
        
        .btn-add-to-cart {
            background: #f57224;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }
        
        .btn-add-to-cart:hover {
            background: #e5631d;
        }
        
        .btn-add-to-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .btn-wishlist {
            width: 38px;
            height: 38px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-wishlist:hover {
            border-color: #f57224;
            color: #f57224;
        }
        
        .btn-wishlist.active {
            border-color: #f57224;
            color: #f57224;
        }
        
        .product-share {
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .share-buttons {
            display: flex;
            gap: 8px;
        }
        
        .share-buttons a {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            text-decoration: none;
        }
        
        .share-buttons a:hover {
            background: #f57224;
            color: white;
        }
        
        /* Tabs */
        .product-tabs {
            margin: 40px 0;
        }
        
        .tabs-nav {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .tabs-nav li {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            font-weight: 500;
        }
        
        .tabs-nav li.active {
            border-color: #f57224;
            color: #f57224;
        }
        
        .tabs-content {
            padding: 20px 0;
        }
        
        .tab-panel {
            display: none;
        }
        
        .tab-panel.active {
            display: block;
        }
        
        .specs-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .specs-table th,
        .specs-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        
        .specs-table th {
            width: 30%;
            font-weight: 600;
            background: #f9f9f9;
        }
        
        /* Reviews */
        .reviews-summary {
            display: flex;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .average-rating {
            text-align: center;
        }
        
        .average-value {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .rating-bars {
            flex: 1;
        }
        
        .rating-bar {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            gap: 10px;
        }
        
        .star-count {
            width: 60px;
        }
        
        .bar {
            flex: 1;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .fill {
            height: 100%;
            background: #ffc107;
        }
        
        .percentage {
            width: 40px;
            text-align: right;
        }
        
        .review {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .reviewer {
            font-weight: bold;
        }
        
        .review-date {
            color: #888;
        }
        
        .review-rating {
            color: #ffc107;
        }
        
        .rating-input {
            color: #ddd;
            cursor: pointer;
        }
        
        .rating-input i:hover,
        .rating-input i.active {
            color: #ffc107;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        /* Related products */
        .related-products {
            margin: 40px 0;
        }
        
        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #f57224;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        
        .product-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
        }
        
        .product-wishlist {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
            width: 32px;
            height: 32px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .product-wishlist.in-wishlist {
            color: #f57224;
        }
        
        .product-thumb {
            position: relative;
            overflow: hidden;
        }
        
        .product-thumb img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .product-card:hover .product-thumb img {
            transform: scale(1.05);
        }
        
        .product-actions {
            position: absolute;
            bottom: -50px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 10px;
            background: rgba(255,255,255,0.9);
            transition: bottom 0.3s;
        }
        
        .product-card:hover .product-actions {
            bottom: 0;
        }
        
        .quick-view, .add-to-cart {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f8f8f8;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
        }
        
        .quick-view:hover, .add-to-cart:hover {
            background: #f57224;
            color: white;
        }
        
        .add-to-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .product-details {
            padding: 15px;
        }
        
        .product-details h3 {
            margin: 0 0 10px;
            font-size: 16px;
        }
        
        .product-details h3 a {
            color: #333;
            text-decoration: none;
        }
        
        .product-details h3 a:hover {
            color: #f57224;
        }
        
        .product-category {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .product-rating i {
            color: #ffc107;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .product-stock {
            font-size: 12px;
            font-weight: 500;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .product-detail-container {
                flex-direction: column;
            }
            
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .tabs-nav {
                flex-wrap: wrap;
            }
            
            .tabs-nav li {
                padding: 8px 12px;
            }
            
            .reviews-summary {
                flex-direction: column;
                gap: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .product-actions {
                position: static;
                background: transparent;
                padding: 10px 0 0;
            }
            
            .product-card:hover .product-actions {
                bottom: auto;
            }
            
            .image-thumbnails {
                flex-wrap: wrap;
            }
            
            .product-actions {
                flex-wrap: wrap;
            }
            
            .product-share {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
                    <a href="index.php"><img src="images/logo/logo.jpg" alt="Anuradha Hardware"></a>
                </div>
                <div class="search-bar">
                    <form action="products.php" method="GET">
                        <input type="text" name="search" placeholder="Search for products." value="" />
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
                    <a href="login.php"><i class="fas fa-user"></i></a>
                    <a href="wishlist.php"><i class="fas fa-heart"></i></a>
                    <a href="cart.php" class="cart-icon"><i class="fas fa-shopping-cart"></i><span class="cart-count"><?php echo (int)$itemCount; ?></span></a>
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
                            <a href="products.php?category=Hand Tools">Hand Tools</a>
                            <a href="products.php?category=Power Tools">Power Tools</a>
                            <a href="products.php?category=Gardening Tools">Gardening Tools</a>
                        </div>
                        <div class="dropdown-column">
                            <h4>Building Materials</h4>
                            <a href="products.php?category=Cement & Aggregates">Cement & Aggregates</a>
                            <a href="products.php?category=Bricks & Blocks">Bricks & Blocks</a>
                            <a href="products.php?category=Steel & Rods">Steel & Rods</a>
                        </div>
                        <div class="dropdown-column">
                            <h4>Paints & Decor</h4>
                            <a href="products.php?category=Paints">Paints</a>
                            <a href="products.php?category=Wallpapers">Wallpapers</a>
                            <a href="products.php?category=Tiles">Tiles</a>
                        </div>
                        <div class="dropdown-column">
                            <h4>Plumbing</h4>
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

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="products.php?category=<?php echo urlencode($product['category_name']); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <li><?php echo htmlspecialchars($product['name']); ?></li>
            </ul>
        </div>
    </div>

    <!-- Product Detail Section -->
    <section class="product-detail-section">
        <div class="container">
            <div class="product-detail-container">
                <!-- Product Images -->
                <div class="product-images">
                    <div class="main-image">
                        <img src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" id="mainProductImage">
                    </div>
                    <div class="image-thumbnails">
                        <div class="thumbnail active" data-image="<?php echo htmlspecialchars($main_image); ?>">
                            <img src="<?php echo htmlspecialchars($main_image); ?>" alt="Thumbnail 1">
                        </div>
                        <?php if (!empty($image2)): ?>
                        <div class="thumbnail" data-image="<?php echo htmlspecialchars($image2); ?>">
                            <img src="<?php echo htmlspecialchars($image2); ?>" alt="Thumbnail 2">
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($image3)): ?>
                        <div class="thumbnail" data-image="<?php echo htmlspecialchars($image3); ?>">
                            <img src="<?php echo htmlspecialchars($image3); ?>" alt="Thumbnail 3">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                        <div class="product-badge">-<?php echo $product['discount']; ?>%</div>
                    <?php endif; ?>
                    
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-meta">
                        <div class="product-sku">SKU: <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></div>
                        <div class="product-category">Category: <a href="products.php?category=<?php echo urlencode($product['category_name']); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></div>
                    </div>
                    
                    <div class="product-rating">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="rating-value">4.5</span>
                        <span class="review-count">(24 reviews)</span>
                        <a href="#reviews" class="review-link">Write a Review</a>
                    </div>
                    
                    <div class="product-price">
                        <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                            <span class="current-price">Rs. <?php echo number_format($product['price'] * (1 - $product['discount']/100), 2); ?></span>
                            <span class="original-price">Rs. <?php echo number_format($product['price'], 2); ?></span>
                            <span class="discount-percent">Save <?php echo $product['discount']; ?>%</span>
                        <?php else: ?>
                            <span class="current-price">Rs. <?php echo number_format($product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-stock <?php echo ($product['stock'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                        <i class="fas fa-<?php echo ($product['stock'] > 0) ? 'check' : 'times'; ?>-circle"></i>
                        <?php echo ($product['stock'] > 0) ? 'In Stock' : 'Out of Stock'; ?>
                    </div>
                    
                    <div class="product-description">
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                    
                    <div class="product-features">
                        <h3>Key Features:</h3>
                        <ul>
                            <li><i class="fas fa-check"></i> High-quality materials</li>
                            <li><i class="fas fa-check"></i> Durable construction</li>
                            <li><i class="fas fa-check"></i> Professional grade</li>
                            <li><i class="fas fa-check"></i> Warranty included</li>
                        </ul>
                    </div>
                    
                    <div class="product-actions">
                        <div class="quantity-selector">
                            <button class="quantity-btn minus"><i class="fas fa-minus"></i></button>
                            <input type="number" id="productQuantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button class="quantity-btn plus"><i class="fas fa-plus"></i></button>
                        </div>
                        
                        <button class="btn btn-add-to-cart" data-id="<?php echo $product['id']; ?>" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        
                        <button class="btn-wishlist" data-id="<?php echo $product['id']; ?>">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    
                    <div class="product-share">
                        <span>Share this product:</span>
                        <div class="share-buttons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-pinterest"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Tabs -->
            <div class="product-tabs">
                <ul class="tabs-nav">
                    <li class="active" data-tab="description">Description</li>
                    <li data-tab="specifications">Specifications</li>
                    <li data-tab="reviews">Reviews (24)</li>
                    <li data-tab="shipping">Shipping & Returns</li>
                </ul>
                
                <div class="tabs-content">
                    <div id="description" class="tab-panel active">
                        <h3>Product Description</h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam euismod, nisl eget ultricies ultricies, nunc nisl aliquam nunc, eget aliquam nisl nunc eget nisl. Nullam euismod, nisl eget ultricies ultricies, nunc nisl aliquam nunc, eget aliquam nisl nunc eget nisl.</p>
                        <p>Nullam euismod, nisl eget ultricies ultricies, nunc nisl aliquam nunc, eget aliquam nisl nunc eget nisl. Nullam euismod, nisl eget ultricies ultricies, nunc nisl aliquam nunc, eget aliquam nisl nunc eget nisl.</p>
                    </div>
                    
                    <div id="specifications" class="tab-panel">
                        <h3>Technical Specifications</h3>
                        <table class="specs-table">
                            <tr>
                                <th>Material</th>
                                <td>High-grade steel</td>
                            </tr>
                            <tr>
                                <th>Weight</th>
                                <td>1.2 kg</td>
                            </tr>
                            <tr>
                                <th>Dimensions</th>
                                <td>30 x 15 x 5 cm</td>
                            </tr>
                            <tr>
                                <th>Warranty</th>
                                <td>2 years</td>
                            </tr>
                            <tr>
                                <th>Brand</th>
                                <td>Professional Tools</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div id="reviews" class="tab-panel">
                        <h3>Customer Reviews</h3>
                        <div class="reviews-summary">
                            <div class="average-rating">
                                <div class="average-value">4.5</div>
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <div class="total-reviews">Based on 24 reviews</div>
                            </div>
                            <div class="rating-bars">
                                <div class="rating-bar">
                                    <span class="star-count">5 stars</span>
                                    <div class="bar">
                                        <div class="fill" style="width: 75%;"></div>
                                    </div>
                                    <span class="percentage">75%</span>
                                </div>
                                <div class="rating-bar">
                                    <span class="star-count">4 stars</span>
                                    <div class="bar">
                                        <div class="fill" style="width: 15%;"></div>
                                    </div>
                                    <span class="percentage">15%</span>
                                </div>
                                <div class="rating-bar">
                                    <span class="star-count">3 stars</span>
                                    <div class="bar">
                                        <div class="fill" style="width: 7%;"></div>
                                    </div>
                                    <span class="percentage">7%</span>
                                </div>
                                <div class="rating-bar">
                                    <span class="star-count">2 stars</span>
                                    <div class="bar">
                                        <div class="fill" style="width: 2%;"></div>
                                    </div>
                                    <span class="percentage">2%</span>
                                </div>
                                <div class="rating-bar">
                                    <span class="star-count">1 star</span>
                                    <div class="bar">
                                        <div class="fill" style="width: 1%;"></div>
                                    </div>
                                    <span class="percentage">1%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="reviews-list">
                            <div class="review">
                                <div class="review-header">
                                    <div class="reviewer">Rajith Perera</div>
                                    <div class="review-date">October 15, 2023</div>
                                    <div class="review-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p>Excellent product! Very durable and works exactly as described. I would definitely recommend this to anyone in need of quality tools.</p>
                                </div>
                            </div>
                            
                            <div class="review">
                                <div class="review-header">
                                    <div class="reviewer">Samantha Silva</div>
                                    <div class="review-date">September 28, 2023</div>
                                    <div class="review-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p>Good quality product, but the delivery took a bit longer than expected. The product itself is great value for money.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="add-review-form">
                            <h3>Add Your Review</h3>
                            <form>
                                <div class="form-group">
                                    <label for="reviewerName">Your Name</label>
                                    <input type="text" id="reviewerName" required>
                                </div>
                                <div class="form-group">
                                    <label for="reviewerEmail">Email Address</label>
                                    <input type="email" id="reviewerEmail" required>
                                </div>
                                <div class="form-group">
                                    <label>Your Rating</label>
                                    <div class="rating-input">
                                        <i class="far fa-star" data-rating="1"></i>
                                        <i class="far fa-star" data-rating="2"></i>
                                        <i class="far fa-star" data-rating="3"></i>
                                        <i class="far fa-star" data-rating="4"></i>
                                        <i class="far fa-star" data-rating="5"></i>
                                    </div>
                                    <input type="hidden" id="ratingValue" value="0">
                                </div>
                                <div class="form-group">
                                    <label for="reviewTitle">Review Title</label>
                                    <input type="text" id="reviewTitle" required>
                                </div>
                                <div class="form-group">
                                    <label for="reviewContent">Your Review</label>
                                    <textarea id="reviewContent" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </form>
                        </div>
                    </div>
                    
                    <div id="shipping" class="tab-panel">
                        <h3>Shipping Information</h3>
                        <p>We offer shipping across Sri Lanka. Delivery times and costs vary depending on your location:</p>
                        <ul>
                            <li><strong>Colombo:</strong> 1-2 business days - Rs. 250</li>
                            <li><strong>Other Major Cities:</strong> 2-3 business days - Rs. 350</li>
                            <li><strong>Rural Areas:</strong> 3-5 business days - Rs. 500</li>
                        </ul>
                        <p>Free shipping on orders over Rs. 5000.</p>
                        
                        <h3>Return Policy</h3>
                        <p>We offer a 14-day return policy on all unused items in their original packaging. To initiate a return, please contact our customer service team with your order number and reason for return.</p>
                        <p>Items must be in original condition with all tags and packaging intact. Shipping costs for returns are the responsibility of the customer unless the item arrived damaged or defective.</p>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div class="related-products">
                <h2 class="section-title">Related Products</h2>
                <div class="product-grid">
                    <?php while ($related = $related_products->fetch_assoc()): 
                        $related_image = resolve_product_image($related['image']);
                        $in_stock = $related['stock'] > 0;
                    ?>
                    <div class="product-card">
                        <?php if (isset($related['discount']) && $related['discount'] > 0): ?>
                            <div class="product-badge">-<?php echo $related['discount']; ?>%</div>
                        <?php endif; ?>
                        
                        <div class="product-wishlist" data-id="<?php echo $related['id']; ?>">
                            <i class="far fa-heart"></i>
                        </div>
                        
                        <div class="product-thumb">
                            <a href="product-details.php?id=<?php echo $related['id']; ?>">
                                <img src="<?php echo htmlspecialchars($related_image); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                            </a>
                            <div class="product-actions">
                                <button class="quick-view" data-id="<?php echo $related['id']; ?>"><i class="fas fa-eye"></i></button>
                                <button class="add-to-cart" data-id="<?php echo $related['id']; ?>" <?php echo !$in_stock ? 'disabled' : ''; ?>><i class="fas fa-shopping-cart"></i></button>
                            </div>
                        </div>
                        
                        <div class="product-details">
                            <h3><a href="product-details.php?id=<?php echo $related['id']; ?>"><?php echo htmlspecialchars($related['name']); ?></a></h3>
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <div class="product-rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="rating-value">4.5</span>
                            </div>
                            <div class="product-price">
                                <?php if (isset($related['discount']) && $related['discount'] > 0): ?>
                                    <span class="current-price">Rs. <?php echo number_format($related['price'] * (1 - $related['discount']/100), 2); ?></span>
                                    <span class="original-price">Rs. <?php echo number_format($related['price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="current-price">Rs. <?php echo number_format($related['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-stock <?php echo $in_stock ? 'in-stock' : 'out-of-stock'; ?>">
                                <?php echo $in_stock ? 'In Stock' : 'Out of Stock'; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <h2>Subscribe to Our Newsletter</h2>
                <p>Get the latest updates on new products and upcoming sales</p>
                <form>
                    <input type="email" placeholder="Your email address">
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Anuradha Hardware</h3>
                    <p>Your trusted partner for quality hardware and building materials since 1995.</p>
                    <div class="footer-contact">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Galle Road, Colombo, Sri Lanka</p>
                        <p><i class="fas fa-phone"></i> +94 112 345 678</p>
                        <p><i class="fas fa-envelope"></i> info@anuradhahardware.com</p>
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
                        <li><a href="#">Order History</a></li>
                        <li><a href="#">Wishlist</a></li>
                        <li><a href="#">Shipping Policy</a></li>
                        <li><a href="#">Returns & Refunds</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Follow Us</h3>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                    <h3>Payment Methods</h3>
                    <div class="payment-methods">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-amex"></i>
                        <i class="fab fa-cc-paypal"></i>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 Anuradha Hardware. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script>
        // Product Image Gallery
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnails = document.querySelectorAll('.thumbnail');
            const mainImage = document.getElementById('mainProductImage');
            
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    const imageSrc = this.getAttribute('data-image');
                    mainImage.src = imageSrc;
                    
                    // Update active thumbnail
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Quantity Selector
            const quantityInput = document.getElementById('productQuantity');
            const minusBtn = document.querySelector('.quantity-btn.minus');
            const plusBtn = document.querySelector('.quantity-btn.plus');
            
            minusBtn.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                if (value > 1) {
                    quantityInput.value = value - 1;
                }
            });
            
            plusBtn.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                let max = parseInt(quantityInput.getAttribute('max'));
                if (value < max) {
                    quantityInput.value = value + 1;
                }
            });
            
            // Tabs
            const tabNavs = document.querySelectorAll('.tabs-nav li');
            const tabPanels = document.querySelectorAll('.tab-panel');
            
            tabNavs.forEach(nav => {
                nav.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Update active tab
                    tabNavs.forEach(n => n.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show active panel
                    tabPanels.forEach(p => p.classList.remove('active'));
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Rating Stars
            const ratingStars = document.querySelectorAll('.rating-input i');
            const ratingValue = document.getElementById('ratingValue');
            
            ratingStars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.getAttribute('data-rating'));
                    ratingValue.value = rating;
                    
                    // Update star display
                    ratingStars.forEach(s => {
                        const starRating = parseInt(s.getAttribute('data-rating'));
                        if (starRating <= rating) {
                            s.classList.remove('far');
                            s.classList.add('fas', 'active');
                        } else {
                            s.classList.remove('fas', 'active');
                            s.classList.add('far');
                        }
                    });
                });
            });
            
            // Add to Cart
            const addToCartBtn = document.querySelector('.btn-add-to-cart');
            const cartCount = document.querySelector('.cart-count');
            
            addToCartBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const quantity = parseInt(document.getElementById('productQuantity').value);
                
                // Send AJAX request to add to cart
                fetch('add-to-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count
                        cartCount.textContent = data.itemCount;
                        cartCount.classList.add('pulse');
                        setTimeout(() => {
                            cartCount.classList.remove('pulse');
                        }, 300);
                        
                        // Show success toast
                        showToast('Product added to cart successfully!');
                    } else {
                        showToast('Failed to add product to cart.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                });
            });
            
            // Wishlist Toggle
            const wishlistBtn = document.querySelector('.btn-wishlist');
            
            wishlistBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                // Send AJAX request to toggle wishlist
                fetch('toggle-wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = this.querySelector('i');
                        if (data.inWishlist) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            this.classList.add('active');
                            showToast('Product added to wishlist!');
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            this.classList.remove('active');
                            showToast('Product removed from wishlist.');
                        }
                    } else {
                        showToast('Please login to manage your wishlist.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                });
            });
            
            // Toast notification function
            function showToast(message, type = 'success') {
                const toast = document.getElementById('toast');
                toast.textContent = message;
                toast.style.background = type === 'success' ? '#4CAF50' : '#ff4757';
                toast.classList.add('show');
                
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
            
            // Quick view and add to cart for related products
            document.querySelectorAll('.quick-view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    // In a real implementation, this would open a modal with product details
                    window.location.href = `product-details.php?id=${productId}`;
                });
            });
            
            document.querySelectorAll('.add-to-cart').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    
                    // Send AJAX request to add to cart
                    fetch('add-to-cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=1`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update cart count
                            cartCount.textContent = data.itemCount;
                            cartCount.classList.add('pulse');
                            setTimeout(() => {
                                cartCount.classList.remove('pulse');
                            }, 300);
                            
                            // Show success toast
                            showToast('Product added to cart successfully!');
                        } else {
                            showToast('Failed to add product to cart.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred. Please try again.', 'error');
                    });
                });
            });
            
            // Wishlist for related products
            document.querySelectorAll('.product-wishlist').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    
                    // Send AJAX request to toggle wishlist
                    fetch('toggle-wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const icon = this.querySelector('i');
                            if (data.inWishlist) {
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                                this.classList.add('in-wishlist');
                                showToast('Product added to wishlist!');
                            } else {
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                                this.classList.remove('in-wishlist');
                                showToast('Product removed from wishlist.');
                            }
                        } else {
                            showToast('Please login to manage your wishlist.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred. Please try again.', 'error');
                    });
                });
            });
        });
    </script>
</body>
</html>