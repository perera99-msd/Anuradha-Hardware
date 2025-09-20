<?php
session_start();
include('anuradha-admin/includes/db.php');

header('Content-Type: application/json');

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$productId = (int)$_POST['product_id'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

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

// Check stock
if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart or update quantity
if (isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$productId] = [
        'id' => $productId,
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity,
        'image' => $product['image']
    ];
}

// If user is logged in, sync with database
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $sessionId = session_id();
    
    // Check if item already exists in user's cart
    $checkStmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $checkStmt->bind_param("ii", $userId, $productId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $existingItem = $checkResult->fetch_assoc();
        $newQuantity = $existingItem['quantity'] + $quantity;
        
        $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newQuantity, $existingItem['id']);
        $updateStmt->execute();
    } else {
        $insertStmt = $conn->prepare("INSERT INTO cart_items (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("isii", $userId, $sessionId, $productId, $quantity);
        $insertStmt->execute();
    }
} else {
    // For guest users, store in database with session ID
    $sessionId = session_id();
    
    $checkStmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE session_id = ? AND product_id = ? AND user_id IS NULL");
    $checkStmt->bind_param("si", $sessionId, $productId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $existingItem = $checkResult->fetch_assoc();
        $newQuantity = $existingItem['quantity'] + $quantity;
        
        $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newQuantity, $existingItem['id']);
        $updateStmt->execute();
    } else {
        $insertStmt = $conn->prepare("INSERT INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, ?)");
        $insertStmt->bind_param("sii", $sessionId, $productId, $quantity);
        $insertStmt->execute();
    }
}

// Calculate total item count
$itemCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $itemCount += (int)$item['quantity'];
}

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'itemCount' => $itemCount
]);
?>