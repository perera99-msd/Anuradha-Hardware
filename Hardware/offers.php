<?php
// Include database connection
include('anuradha-admin/includes/db.php');

// Fetch active offers from database
$offers = $conn->query("SELECT * FROM home_page_content WHERE section = 'offers' AND is_active = 1 ORDER BY order_num ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/offers.css">
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
                <li><a href="offers.php" class="active">Special Offers</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Special Offers</h1>
            <p>Exclusive deals and discounts on quality hardware products</p>
        </div>
    </section>

    <!-- Offers Banner -->
    <section class="offers-banner">
        <div class="container">
            <div class="banner-content">
                <h2>Limited Time Offers</h2>
                <p>Hurry up! These special discounts won't last long</p>
                <div class="countdown" id="offerCountdown">
                    <div class="countdown-item">
                        <span id="days">00</span>
                        <small>Days</small>
                    </div>
                    <div class="countdown-item">
                        <span id="hours">00</span>
                        <small>Hours</small>
                    </div>
                    <div class="countdown-item">
                        <span id="minutes">00</span>
                        <small>Minutes</small>
                    </div>
                    <div class="countdown-item">
                        <span id="seconds">00</span>
                        <small>Seconds</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Offers -->
    <section class="featured-offers">
        <div class="container">
            <h2 class="section-title">Featured Offers</h2>
            
            <div class="offers-grid">
                <?php if ($offers->num_rows > 0): ?>
                    <?php while ($offer = $offers->fetch_assoc()): 
                        $content = json_decode($offer['content_value'], true);
                    ?>
                        <div class="offer-card">
                            <?php if ($offer['image_path']): ?>
                                <div class="offer-image">
                                    <img src="<?= $offer['image_path'] ?>" alt="<?= htmlspecialchars($content['title'] ?? '') ?>">
                                    <div class="offer-badge"><?= htmlspecialchars($content['discount'] ?? 'Sale') ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="offer-content">
                                <h3><?= htmlspecialchars($content['title'] ?? '') ?></h3>
                                <p><?= htmlspecialchars($content['description'] ?? '') ?></p>
                                <div class="offer-price">
                                    <span class="current-price"><?= htmlspecialchars($content['current_price'] ?? '') ?></span>
                                    <?php if (!empty($content['original_price'])): ?>
                                        <span class="original-price"><?= htmlspecialchars($content['original_price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($content['button_text'])): ?>
                                    <a href="<?= htmlspecialchars($content['button_link'] ?? '#') ?>" class="btn"><?= htmlspecialchars($content['button_text']) ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Default offers if none in database -->
                    <div class="offer-card">
                        <div class="offer-image">
                            <img src="images/offers/1.jpeg" alt="Power Tools Sale">
                            <div class="offer-badge">25% OFF</div>
                        </div>
                        <div class="offer-content">
                            <h3>Power Tools Sale</h3>
                            <p>Get premium power tools at discounted prices. Limited stock available.</p>
                            <div class="offer-price">
                                <span class="current-price">From Rs. 8,500.00</span>
                                <span class="original-price">Rs. 11,300.00</span>
                            </div>
                            <a href="products.php?category=power-tools" class="btn">Shop Now</a>
                        </div>
                    </div>
                    
                    <div class="offer-card">
                        <div class="offer-image">
                            <img src="images/offers/2.jpeg" alt="Paint Discount">
                            <div class="offer-badge">15% OFF</div>
                        </div>
                        <div class="offer-content">
                            <h3>Paint Special</h3>
                            <p>Premium quality paints with extended warranty. All colors available.</p>
                            <div class="offer-price">
                                <span class="current-price">From Rs. 3,570.00</span>
                                <span class="original-price">Rs. 4,200.00</span>
                            </div>
                            <a href="products.php?category=paints" class="btn">Shop Now</a>
                        </div>
                    </div>
                    
                    <div class="offer-card">
                        <div class="offer-image">
                            <img src="images/offers/3.jpeg" alt="Plumbing Deal">
                            <div class="offer-badge">BUY 1 GET 1</div>
                        </div>
                        <div class="offer-content">
                            <h3>Plumbing Essentials</h3>
                            <p>Buy selected pipes and get fittings free. Perfect for your renovation projects.</p>
                            <div class="offer-price">
                                <span class="current-price">Special Package Deals</span>
                            </div>
                            <a href="products.php?category=plumbing" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Seasonal Sale -->
    <section class="seasonal-sale">
        <div class="container">
            <div class="sale-content">
                <h2>End of Season Clearance</h2>
                <p>Up to 50% off on selected items. Limited stock available.</p>
                <a href="products.php?filter=clearance" class="btn">View Clearance Items</a>
            </div>
        </div>
    </section>

    <!-- Discount Categories -->
    <section class="discount-categories">
        <div class="container">
            <h2 class="section-title">Categories on Sale</h2>
            
            <div class="category-discounts">
                <div class="discount-category">
                    <div class="discount-info">
                        <h3>Tools & Equipment</h3>
                        <p>Up to 30% off on professional tools</p>
                        <a href="products.php?category=tools&sale=1" class="btn-link">Shop Now <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="discount-image">
                        <img src="images/categories/toolls.jpeg" alt="Tools Discount">
                    </div>
                </div>
                
                <div class="discount-category">
                    <div class="discount-info">
                        <h3>Building Materials</h3>
                        <p>Bulk discounts available for contractors</p>
                        <a href="products.php?category=building&sale=1" class="btn-link">Shop Now <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="discount-image">
                        <img src="images/categories/building.jpeg" alt="Building Materials Discount">
                    </div>
                </div>
                
                <div class="discount-category">
                    <div class="discount-info">
                        <h3>Paints & Decor</h3>
                        <p>Special prices on premium brands</p>
                        <a href="products.php?category=paints&sale=1" class="btn-link">Shop Now <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="discount-image">
                        <img src="images/categories/paint.jpeg" alt="Paints Discount">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3>Never Miss a Deal</h3>
                    <p>Subscribe to get exclusive offers and updates</p>
                </div>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your email address">
                    <button type="submit">Subscribe</button>
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
    <script src="js/offers.js"></script>
</body>
</html>