<?php
// Include session management
include('includes/session.php');

// Calculate cart item count
$itemCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $itemCount += (int)$item['quantity'];
}

// Calculate wishlist count
$wishlistCount = count($_SESSION['wishlist']);

// Get categories for navigation
include('includes/db.php');
$categories = [];
$cat_rs = $conn->query("SELECT name FROM categories ORDER BY name");
while ($row = $cat_rs->fetch_assoc()) {
    $categories[] = $row['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Anuradha Hardware'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <?php if (isset($additionalCSS)): ?>
        <link rel="stylesheet" href="css/<?php echo $additionalCSS; ?>">
    <?php endif; ?>
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
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="user-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="account.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'account.php' ? 'active' : ''; ?>"><i class="fas fa-user"></i></a>
                    <?php else: ?>
                        <a href="login.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>"><i class="fas fa-user"></i></a>
                    <?php endif; ?>
                    <a href="wishlist.php" class="wishlist-icon <?php echo basename($_SERVER['PHP_SELF']) == 'wishlist.php' ? 'active' : ''; ?>">
                        <i class="fas fa-heart"></i>
                        <span class="wishlist-count"><?php echo $wishlistCount; ?></span>
                    </a>
                    <a href="cart.php" class="cart-icon <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
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
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li class="dropdown">
                    <a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Products <i class="fas fa-chevron-down"></i></a>
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
                <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About Us</a></li>
                <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                <li><a href="offers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'offers.php' ? 'active' : ''; ?>">Special Offers</a></li>
            </ul>
        </div>
    </nav>