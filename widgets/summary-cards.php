<?php
// Today's Orders
$today = date('Y-m-d');
$ordersToday = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = '$today'"))['count'];

// Today's Revenue
$revenueToday = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = '$today'
"))['total'] ?? 0;

// Total Customers
$totalCustomers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM customers"))['count'];

// Total Products
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
?>

<!-- Cards -->
<div class="col-md-3">
    <div class="card text-white bg-info shadow">
        <div class="card-body">
            <h5 class="card-title">Today's Orders</h5>
            <p class="fs-3"><?= $ordersToday ?></p>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card text-white bg-success shadow">
        <div class="card-body">
            <h5 class="card-title">Today's Revenue</h5>
            <p class="fs-3">Rs. <?= number_format($revenueToday) ?></p>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card text-dark bg-warning shadow">
        <div class="card-body">
            <h5 class="card-title">Total Customers</h5>
            <p class="fs-3"><?= $totalCustomers ?></p>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card text-white bg-danger shadow">
        <div class="card-body">
            <h5 class="card-title">Total Products</h5>
            <p class="fs-3"><?= $totalProducts ?></p>
        </div>
    </div>
</div>