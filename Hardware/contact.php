<?php
// Include database connection
include('anuradha-admin/includes/db.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle contact form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Save to database (in a real application)
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        
        if ($stmt->execute()) {
            $success = 'Thank you for your message! We will get back to you within 24 hours.';
            
            // Clear form
            $name = $email = $phone = $subject = $message = '';
            
            // In a real application, you would also send an email notification
        } else {
            $error = 'Sorry, there was an error sending your message. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/contact.css">
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
                <li><a href="contact.php" class="active">Contact</a></li>
                <li><a href="offers.php">Special Offers</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We're here to help with all your hardware needs</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <h2>Get In Touch</h2>
                    <p class="contact-subtitle">Have questions about our products or services? We're here to help.</p>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Visit Our Store</h3>
                                <p>123 Galle Road, Colombo 04, Sri Lanka</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Call Us</h3>
                                <p>+94 112 345 678</p>
                                <p>+94 773 456 789</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Email Us</h3>
                                <p>info@anuradhahardware.com</p>
                                <p>support@anuradhahardware.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Opening Hours</h3>
                                <p>Monday - Saturday: 8:30 AM - 6:00 PM</p>
                                <p>Sunday: 9:00 AM - 4:00 PM</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-contact">
                        <h3>Follow Us</h3>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-container">
                    <div class="contact-form-card">
                        <h2>Send Us a Message</h2>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="contact.php" id="contactForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user"></i>
                                        <input type="text" id="name" name="name" placeholder="Your full name" 
                                               value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" id="email" name="email" placeholder="Your email address" 
                                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="phone" name="phone" placeholder="Your phone number" 
                                           value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-tag"></i>
                                    <select id="subject" name="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="General Inquiry" <?php echo (isset($subject) && $subject == 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                        <option value="Product Information" <?php echo (isset($subject) && $subject == 'Product Information') ? 'selected' : ''; ?>>Product Information</option>
                                        <option value="Quote Request" <?php echo (isset($subject) && $subject == 'Quote Request') ? 'selected' : ''; ?>>Quote Request</option>
                                        <option value="Order Support" <?php echo (isset($subject) && $subject == 'Order Support') ? 'selected' : ''; ?>>Order Support</option>
                                        <option value="Technical Support" <?php echo (isset($subject) && $subject == 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                                        <option value="Returns & Refunds" <?php echo (isset($subject) && $subject == 'Returns & Refunds') ? 'selected' : ''; ?>>Returns & Refunds</option>
                                        <option value="Other" <?php echo (isset($subject) && $subject == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-comment"></i>
                                    <textarea id="message" name="message" placeholder="How can we help you?" rows="5" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                </div>
                            </div>
                            
                            <button type="submit" name="contact" class="btn btn-primary btn-full">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What are your delivery options?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We offer free delivery for orders over Rs. 10,000 within Colombo. For orders below this amount, delivery charges start at Rs. 500 depending on location. We also provide island-wide delivery with charges calculated based on distance and order size.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Do you offer professional contractor discounts?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we offer special pricing for registered contractors and construction companies. Please visit our store with your business registration documents to set up a contractor account and enjoy exclusive discounts and benefits.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What is your return policy?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We offer a 30-day return policy for most items in original condition with proof of purchase. Special order items and certain products may have different return conditions. Please contact our customer service for specific return information.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Can I order products online for store pickup?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, you can order products through our website and choose store pickup at checkout. We'll notify you when your order is ready for collection. Most orders are ready within 2-4 hours during business hours.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-cta">
                <p>Can't find the answer you're looking for? <a href="#contactForm">Contact us directly</a> and we'll be happy to help.</p>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2 class="section-title">Our Location</h2>
            <div class="map-container">
                <div class="map-wrapper">
                    <!-- In a real application, you would embed a Google Maps iframe here -->
                    <div class="map-placeholder">
                        <i class="fas fa-map-marked-alt"></i>
                        <h3>Interactive Map</h3>
                        <p>123 Galle Road, Colombo 04, Sri Lanka</p>
                        <a href="https://maps.google.com/?q=123+Galle+Road,+Colombo+04,+Sri+Lanka" target="_blank" class="btn">Open in Google Maps</a>
                    </div>
                </div>
                
                <div class="location-details">
                    <h3>Store Directions</h3>
                    <p>We're conveniently located on Galle Road, just 5 minutes from Colombo City Center. Ample parking is available behind the building.</p>
                    
                    <div class="transport-options">
                        <div class="transport-option">
                            <i class="fas fa-bus"></i>
                            <div>
                                <h4>By Bus</h4>
                                <p>Bus routes 100, 101, 102, and 155 stop directly in front of our store.</p>
                            </div>
                        </div>
                        
                        <div class="transport-option">
                            <i class="fas fa-train"></i>
                            <div>
                                <h4>By Train</h4>
                                <p>The nearest railway station is Colombo Fort, which is a 10-minute taxi ride away.</p>
                            </div>
                        </div>
                        
                        <div class="transport-option">
                            <i class="fas fa-parking"></i>
                            <div>
                                <h4>Parking</h4>
                                <p>Free customer parking available behind the building with capacity for 50+ vehicles.</p>
                            </div>
                        </div>
                    </div>
                </div>
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
    <script src="js/contact.js"></script>
</body>
</html>