<?php
session_start();
include('anuradha-admin/includes/db.php');

header('Content-Type: application/json');

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$productId = (int)$_POST['product_id'];
$action = isset($_POST['action']) ? $_POST['action'] : 'toggle';

// Check if user is logged in or has a session
if (!isset($_SESSION['user_id']) && !isset($_SESSION['wishlist'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage your wishlist']);
    exit;
}

// Get product details
$stmt = $conn->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = $result->fetch_assoc();

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

$isInWishlist = isset($_SESSION['wishlist'][$productId]);
$responseAction = '';

if ($action === 'add' || ($action === 'toggle' && !$isInWishlist)) {
    // Add to wishlist
    $_SESSION['wishlist'][$productId] = [
        'id' => $productId,
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image'],
        'stock' => $product['stock']
    ];
    $responseAction = 'added';
    
    // Sync with database if user is logged in
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $sessionId = session_id();
        
        // Check if already in wishlist
        $checkStmt = $conn->prepare("SELECT id FROM wishlist_items WHERE user_id = ? AND product_id = ?");
        $checkStmt->bind_param("ii", $userId, $productId);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows === 0) {
            $insertStmt = $conn->prepare("INSERT INTO wishlist_items (user_id, session_id, product_id) VALUES (?, ?, ?)");
            $insertStmt->bind_param("isi", $userId, $sessionId, $productId);
            $insertStmt->execute();
        }
    } else {
        // For guest users, store in database with session ID
        $sessionId = session_id();
        
        $checkStmt = $conn->prepare("SELECT id FROM wishlist_items WHERE session_id = ? AND product_id = ? AND user_id IS NULL");
        $checkStmt->bind_param("si", $sessionId, $productId);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows === 0) {
            $insertStmt = $conn->prepare("INSERT INTO wishlist_items (session_id, product_id) VALUES (?, ?)");
            $insertStmt->bind_param("si", $sessionId, $productId);
            $insertStmt->execute();
        }
    }
    
} elseif ($action === 'remove' || ($action === 'toggle' && $isInWishlist)) {
    // Remove from wishlist
    unset($_SESSION['wishlist'][$productId]);
    $responseAction = 'removed';
    
    // Remove from database
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?");
        $deleteStmt->bind_param("ii", $userId, $productId);
        $deleteStmt->execute();
    } else {
        $sessionId = session_id();
        $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE session_id = ? AND product_id = ? AND user_id IS NULL");
        $deleteStmt->bind_param("si", $sessionId, $productId);
        $deleteStmt->execute();
    }
}

// Calculate wishlist count
$wishlistCount = count($_SESSION['wishlist']);

echo json_encode([
    'success' => true,
    'action' => $responseAction,
    'wishlistCount' => $wishlistCount
]);
?>