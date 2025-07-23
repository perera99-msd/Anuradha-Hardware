<?php
include('includes/db.php');
include('includes/header.php');

// Revenue Chart Data
$labels = [];
$data = [];
$result = mysqli_query($conn, "
    SELECT DATE_FORMAT(created_at, '%M') AS month, SUM(total_amount) AS total 
    FROM orders 
    WHERE YEAR(created_at) = YEAR(CURDATE()) 
    GROUP BY MONTH(created_at)
");

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['month'];
    $data[] = $row['total'];
}
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>

    <div class="p-4 w-100">
        <h2 class="mb-3">Dashboard</h2>
        <p class="mb-4">Welcome to the admin dashboard.</p>

        <!-- Row 1: Summary Cards  -->
        <div class="row g-4 mb-4">
            <?php include('widgets/summary-cards.php'); ?>
        </div>

        <!--Row 2: Revenue Overview + Recent Orders -->
        <div class="row g-4 mb-4">
            <!--Revenue Overview  -->
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        Revenue Overview
                    </div>
                    <div class="card-body" style="height: 245px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Orders  -->
            <div class="col-lg-4" style="height: 300px;">
                <?php include('widgets/recent-orders.php'); ?>
            </div>
        </div>

        <!--Row 3: Low Stock, Top Products, Recent Customers -->
        <div class="row g-4">
            <div class="col-lg-4">
                <?php include('widgets/low-stock-products.php'); ?>
            </div>
            <div class="col-lg-4">
                <?php include('widgets/top-products.php'); ?>
            </div>
            <div class="col-lg-4">
                <?php include('widgets/recent-customers.php'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    //  Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($data) ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
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

    //  Low Stock Chart
    const lowStockCtx = document.getElementById('lowStockChart')?.getContext('2d');
    if (lowStockCtx) {
        new Chart(lowStockCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($lowStockLabels); ?>,
                datasets: [{
                    label: 'Stock Left',
                    data: <?= json_encode($lowStockData); ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.7)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            callback: function(value, index, ticks) {
                                const label = this.getLabelForValue(value);
                                return label.length > 10 ? label.substring(0, 10) + '…' : label;
                            },
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                return tooltipItems[0].label;
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
</script>

<?php include('includes/footer.php'); ?>