<?php
$lowStockLabels = [];
$lowStockData = [];

$query = "SELECT name, stock FROM products ORDER BY stock ASC LIMIT 5";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $lowStockLabels[] = $row['name'];
    $lowStockData[] = (int)$row['stock'];
}
?>

<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        Low Stock Products
    </div>
    <div class="card-body" style="height: 250px;">
        <canvas id="lowStockChart"></canvas>
    </div>
</div>