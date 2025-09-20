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

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// If user is logged in, sync cart with database
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

// Inputs
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$sort     = isset($_GET['sort'])     ? trim($_GET['sort'])     : 'newest';

// Pagination
$products_per_page = 12;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $products_per_page;

// Build filters (prepared)
$where = ["p.stock > 0"]; // Changed from p.status = 'active' to p.stock > 0
$params = [];
$types  = '';

if ($category !== 'all') {
    $where[] = "c.name = ?";
    $params[] = $category;
    $types   .= 's';
}

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$count_sql = "
    SELECT COUNT(*) AS total
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    {$where_sql}
";
$count_stmt = $conn->prepare($count_sql);
if ($types !== '' && !empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_res = $count_stmt->get_result();
$total_products = (int)($count_res->fetch_assoc()['total'] ?? 0);
$total_pages = max(1, (int)ceil($total_products / $products_per_page));

// Sorting
switch ($sort) {
    case 'price-low':  $order_by = 'p.price ASC'; break;
    case 'price-high': $order_by = 'p.price DESC'; break;
    case 'name':       $order_by = 'p.name ASC'; break;
    case 'newest':
    default:           $order_by = 'p.id DESC'; $sort = 'newest'; break;
}

// Fetch products
$list_sql = "
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    {$where_sql}
    ORDER BY {$order_by}
    LIMIT ? OFFSET ?
";
$list_params = $params;
$list_types  = $types . 'ii';
$list_params[] = $products_per_page;
$list_params[] = $offset;

$list_stmt = $conn->prepare($list_sql);
if ($list_types !== '') {
    $list_stmt->bind_param($list_types, ...$list_params);
}
$list_stmt->execute();
$products = $list_stmt->get_result();

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

// Cart count
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Products - Anuradha Hardware</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="stylesheet" href="css/responsive.css"/>
  <link rel="stylesheet" href="css/products.css"/>
  <link rel="stylesheet" href="css/custom.css"/>
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
            <input type="text" name="search" placeholder="Search for products..." value="<?php echo htmlspecialchars($search); ?>" />
            <select name="category">
              <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($cat); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit"><i class="fas fa-search"></i></button>
          </form>
        </div>
        <div class="user-actions">
          <?php if ($is_logged_in): ?>
            <a href="account.php"><i class="fas fa-user"></i></a>
          <?php else: ?>
            <a href="login.php"><i class="fas fa-user"></i></a>
          <?php endif; ?>
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

  <!-- Page Header -->
  <section class="page-header">
    <div class="container">
      <h1>Our Products</h1>
      <p>Discover our wide range of quality hardware products</p>
      <?php if ($is_logged_in): ?>
        <p class="welcome-user">Welcome back, <?php echo htmlspecialchars($_SESSION['user_first_name'] ?? 'User'); ?>!</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Products -->
  <section class="products-section">
    <div class="container">
      <div class="products-container">
        <!-- Filters -->
        <aside class="products-filters">
          <div class="filter-card">
            <h3>Categories</h3>
            <ul class="filter-list">
              <li><a href="products.php" class="<?php echo $category === 'all' ? 'active' : ''; ?>">All Categories</a></li>
              <?php foreach ($categories as $cat): ?>
                <li><a href="products.php?category=<?php echo urlencode($cat); ?>" class="<?php echo $category === $cat ? 'active' : ''; ?>"><?php echo htmlspecialchars($cat); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="filter-card">
            <h3>Price Range</h3>
            <div class="price-range">
              <input type="range" min="0" max="100000" value="100000" class="slider" id="priceRange">
              <div class="price-values"><span>Rs. 0</span><span>Rs. 100,000</span></div>
            </div>
          </div>
          <div class="filter-card">
            <h3>Brand</h3>
            <div class="filter-checkbox">
              <label><input type="checkbox" name="brand" value="bosch"> Bosch</label>
              <label><input type="checkbox" name="brand" value="stanley"> Stanley</label>
              <label><input type="checkbox" name='brand' value="dewalt"> DeWalt</label>
              <label><input type="checkbox" name='brand' value="makita"> Makita</label>
            </div>
          </div>
          <div class="filter-card">
            <h3>Availability</h3>
            <div class="filter-checkbox">
              <label><input type="checkbox" name="stock" value="in-stock" checked> In Stock</label>
              <label><input type="checkbox" name="stock" value="out-of-stock"> Out of Stock</label>
            </div>
          </div>
          <button class="btn btn-filter" id="applyFilters">Apply Filters</button>
        </aside>

        <!-- Grid -->
        <div class="products-grid-container">
          <div class="products-header">
            <p>Showing <?php echo (int)$products->num_rows; ?> of <?php echo (int)$total_products; ?> products</p>
            <div class="sort-options">
              <label for="sort">Sort by:</label>
              <select id="sort">
                <option value="newest"     <?php echo $sort==='newest'?'selected':''; ?>>Newest First</option>
                <option value="price-low"  <?php echo $sort==='price-low'?'selected':''; ?>>Price: Low to High</option>
                <option value="price-high" <?php echo $sort==='price-high'?'selected':''; ?>>Price: High to Low</option>
                <option value="name"       <?php echo $sort==='name'?'selected':''; ?>>Name A-Z</option>
              </select>
            </div>
          </div>

          <?php if ($products->num_rows > 0): ?>
            <div class="product-grid">
              <?php while ($product = $products->fetch_assoc()):
                $image_path = resolve_product_image($product['image']);
                $is_in_wishlist = $is_logged_in && isset($user_wishlist[$product['id']]);
              ?>
                <div class="product-card">
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
                      <button class="add-to-cart" data-id="<?php echo (int)$product['id']; ?>" <?php echo $product['stock']<=0?'disabled':''; ?>><i class="fas fa-shopping-cart"></i></button>
                    </div>
                  </div>
                  <div class="product-details">
                    <h3><a href="product-detail.php?id=<?php echo (int)$product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                    <div class="product-category"><?php echo htmlspecialchars($product['category_name']??'Uncategorized'); ?></div>
                    <div class="product-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><span>(<?php echo rand(5,50); ?>)</span></div>
                    <div class="product-price">Rs. <?php echo number_format((float)$product['price'],2); ?></div>
                    <div class="product-stock <?php echo $product['stock']>0?'in-stock':'out-of-stock'; ?>">
                      <?php echo $product['stock']>0?'In Stock':'Out of Stock'; ?>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1): ?>
              <div class="pagination">
                <?php if ($current_page > 1): ?>
                  <a class="page-link prev" href="products.php?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&page=<?php echo $current_page - 1; ?>"><i class="fas fa-chevron-left"></i> Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                  <a class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>" href="products.php?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($current_page < $total_pages): ?>
                  <a class="page-link next" href="products.php?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&page=<?php echo $current_page + 1; ?>">Next <i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
                </div>
            <?php endif; ?>

          <?php else: ?>
            <div class="no-products">
              <div class="no-products-icon"><i class="fas fa-search"></i></div>
              <h3>No products found</h3>
              <p>Try adjusting your search or filter criteria</p>
              <a href="products.php" class="btn">View All Products</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Newsletter -->
  <section class="newsletter">
    <div class="container">
      <div class="newsletter-content">
        <div class="newsletter-text">
          <h3>Subscribe to Our Newsletter</h3>
          <p>Get updates on special offers and new products</p>
        </div>
        <form class="newsletter-form" onsubmit="event.preventDefault();">
          <input type="email" placeholder="Your email address" required>
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
          <div class="footer-logo"><img src="images/logo/logo.jpg" alt="Anuradha Hardware"><h3>Anuradha Hardware</h3></div>
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
            <li><a href="products.php">Products</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="offers.php">Special Offers</a></li>
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
            <li><i class="fas fa-map-marker-alt"></i> 123 Main Street, Colombo, Sri Lanka</li>
            <li><i class="fas fa-phone"></i> +94 112 345 678</li>
            <li><i class="fas fa-envelope"></i> info@anuradhahardware.com</li>
            <li><i class="fas fa-clock"></i> Mon-Sat: 8:00 AM - 6:00 PM</li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Anuradha Hardware. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <script src="js/products.js"></script>
</body>
</html>