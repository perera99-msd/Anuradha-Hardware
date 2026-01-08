<?php
include('anuradha-admin/includes/db.php');
session_start();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$order_id = (int)$_GET['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success - Anuradha Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-container { text-align: center; padding: 80px 20px; }
        .success-icon { color: #4CAF50; font-size: 80px; margin-bottom: 20px; }
        .order-id { font-size: 24px; font-weight: bold; color: #333; margin: 10px 0; }
    </style>
</head>
<body>
    <?php include('includes/header.php'); // Assuming you have a header partial ?>
    
    <div class="container">
        <div class="success-container">
            <i class="fas fa-check-circle success-icon"></i>
            <h1>Thank You for Your Order!</h1>
            <p>Your order has been placed successfully.</p>
            <p class="order-id">Order ID: #<?php echo str_pad($order_id, 4, '0', STR_PAD_LEFT); ?></p>
            <p>You will receive an email confirmation shortly.</p>
            <br>
            <a href="account.php#orders" class="btn btn-primary">View Order Details</a>
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>

    <?php include('includes/footer.php'); // Assuming you have a footer partial ?>
</body>
</html>