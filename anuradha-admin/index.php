<?php
include('includes/db.php');
include('includes/header.php');

// Revenue Chart Data
$labels = [];
$data = [];
$result = mysqli_query($conn, "
    SELECT DATE_FORMAT(created_at, '%b') AS month, SUM(total_amount) AS total 
    FROM orders 
    WHERE YEAR(created_at) = YEAR(CURDATE()) 
    GROUP BY MONTH(created_at)
");
while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['month'];
    $data[] = $row['total'];
}

// Order Status Count for Pie Chart
$statusCounts = ['Pending' => 0, 'Processing' => 0, 'Completed' => 0, 'Cancelled' => 0];
$result = mysqli_query($conn, "SELECT status, COUNT(*) AS count FROM orders GROUP BY status");
while ($row = mysqli_fetch_assoc($result)) {
    $statusCounts[$row['status']] = $row['count'];
}
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>

    <div class="p-4 w-100">
        <h2 class="mb-4">Dashboard</h2>
        <div class="row g-4 mb-4">
            <?php include('widgets/summary-cards.php'); ?>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        Monthly Revenue
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        Orders by Status
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-md-6">
                <?php include('widgets/recent-orders.php'); ?>
            </div>
            <div class="col-md-6">
                <?php include('widgets/low-stock-products.php'); ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Revenue (Rs)',
                data: <?= json_encode($data) ?>,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
            datasets: [{
                label: 'Orders',
                data: <?= json_encode(array_values($statusCounts)) ?>,
                backgroundColor: [
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

<?php include('includes/footer.php'); ?>