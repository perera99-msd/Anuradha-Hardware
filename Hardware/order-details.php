<?php
include('anuradha-admin/includes/db.php');
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: login.php');
    exit;
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch Order Info (Security check: ensure order belongs to user)
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found or access denied.");
}

// Fetch Order Items
$items_stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?php echo $order_id; ?> - Anuradha Hardware</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .order-detail-page { padding: 40px 0; }
        .order-header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .order-items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .order-items-table th, .order-items-table td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        .order-items-table th { background-color: #f9f9f9; }
        .total-row { font-size: 18px; font-weight: bold; }
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 14px; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container">
            <a href="account.php" style="color: white;"><i class="fas fa-arrow-left"></i> Back to Account</a>
        </div>
    </div>

    <div class="container order-detail-page">
        <div class="order-header">
            <h1>Order #<?php echo str_pad($order_id, 4, '0', STR_PAD_LEFT); ?></h1>
            <p>Placed on: <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
            <p>Status: <span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></p>
        </div>

        <h3>Items Ordered</h3>
        <table class="order-items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                while($item = $items->fetch_assoc()): 
                    $item_total = $item['price'] * $item['quantity'];
                    $subtotal += $item_total;
                ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="<?php echo $item['image']; ?>" width="50" style="border-radius:4px;">
                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                        </div>
                    </td>
                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>Rs. <?php echo number_format($item_total, 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">Grand Total:</td>
                    <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>