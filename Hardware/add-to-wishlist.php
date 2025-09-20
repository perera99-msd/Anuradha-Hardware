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

// Get product ID from request
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Initialize response
$response = ['success' => false, 'message' => 'Invalid request', 'wishlistCount' => count($_SESSION['wishlist'])];

if ($productId > 0) {
    try {
        // Check if product is already in the session wishlist
        if (isset($_SESSION['wishlist'][$productId])) {
            $response['message'] = 'Product is already in your wishlist.';
        } else {
            // Fetch product details from the database
            $stmt = $conn->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $product = $result->fetch_assoc();
                
                // 1. Add to PHP Session
                $_SESSION['wishlist'][$productId] = [
                    'id'    => $productId,
                    'name'  => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'stock' => $product['stock']
                ];
                
                // 2. Update Database
                $is_logged_in = isset($_SESSION['user_id']);
                
                if ($is_logged_in) {
                    $userId = $_SESSION['user_id'];
                    // Use INSERT IGNORE to prevent errors if the item already exists in DB
                    $insertStmt = $conn->prepare("INSERT IGNORE INTO wishlist_items (user_id, product_id) VALUES (?, ?)");
                    $insertStmt->bind_param("ii", $userId, $productId);
                    $insertStmt->execute();
                } else {
                    $sessionId = session_id();
                    $insertStmt = $conn->prepare("INSERT IGNORE INTO wishlist_items (session_id, product_id) VALUES (?, ?)");
                    $insertStmt->bind_param("si", $sessionId, $productId);
                    $insertStmt->execute();
                }
                
                // 3. Send successful response
                $response = [
                    'success'       => true, 
                    'message'       => 'Product added to wishlist', 
                    'wishlistCount' => count($_SESSION['wishlist'])
                ];
            } else {
                $response['message'] = 'Product not found';
            }
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

// Final check on count
$response['wishlistCount'] = count($_SESSION['wishlist']);

header('Content-Type: application/json');
echo json_encode($response);
exit;