<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize user-specific sessions if they don't exist
if (!isset($_SESSION['user_id'])) {
    $_SESSION['cart'] = [];
    $_SESSION['wishlist'] = [];
} else {
    // Load user-specific data from database
    include('db.php');
    
    $user_id = $_SESSION['user_id'];
    
    // Load cart from database
    $cart_stmt = $conn->prepare("
        SELECT ci.product_id, ci.quantity, p.name, p.price, p.image 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        WHERE ci.user_id = ?
    ");
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    
    $_SESSION['cart'] = [];
    while ($item = $cart_result->fetch_assoc()) {
        $_SESSION['cart'][$item['product_id']] = $item;
    }
    $cart_stmt->close();
    
    // Load wishlist from database
    $wishlist_stmt = $conn->prepare("
        SELECT wi.product_id, p.name, p.price, p.image, p.stock 
        FROM wishlist_items wi 
        JOIN products p ON wi.product_id = p.id 
        WHERE wi.user_id = ?
    ");
    $wishlist_stmt->bind_param("i", $user_id);
    $wishlist_stmt->execute();
    $wishlist_result = $wishlist_stmt->get_result();
    
    $_SESSION['wishlist'] = [];
    while ($item = $wishlist_result->fetch_assoc()) {
        $_SESSION['wishlist'][$item['product_id']] = $item;
    }
    $wishlist_stmt->close();
}
?>