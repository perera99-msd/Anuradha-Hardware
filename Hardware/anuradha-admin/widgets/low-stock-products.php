<?php
include_once(__DIR__ . '/../includes/db.php'); // Ensure DB is connected

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
    <div class="card-header bg-warning text-dark fw-bold">
        Low Stock Products
    </div>
    <div class="card-body" style="height: 250px;">
        <canvas id="lowStockChart"></canvas>
    </div>
</div>

<!-- Chart.js rendering -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('lowStockChart')?.getContext('2d');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($lowStockLabels); ?>,
                    datasets: [{
                        label: 'Stock Remaining',
                        data: <?= json_encode($lowStockData); ?>,
                        backgroundColor: 'rgba(255, 159, 64, 0.7)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        borderRadius: 8,
                        maxBarThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>