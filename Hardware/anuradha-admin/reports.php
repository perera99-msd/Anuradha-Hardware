<?php
include('includes/db.php');
include('includes/header.php');

// Handle filters
$period = $_GET['period'] ?? 'month';
$type = $_GET['type'] ?? 'orders';

// Generate date filter
$filterCondition = "";
switch ($period) {
    case 'today':
        $filterCondition = "DATE(o.created_at) = CURDATE()";
        break;
    case 'week':
        $filterCondition = "YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'year':
        $filterCondition = "YEAR(o.created_at) = YEAR(CURDATE())";
        break;
    case 'month':
    default:
        $filterCondition = "MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
        break;
}

// Fetch data based on report type
$data = [];
$title = '';
switch ($type) {
    case 'revenue':
        $title = "Revenue Summary";
        $sql = "SELECT DATE(o.created_at) as date, SUM(o.total_amount) as total_revenue
                FROM orders o
                WHERE $filterCondition
                GROUP BY DATE(o.created_at)
                ORDER BY o.created_at DESC";
        break;

    case 'products':
        $title = "Products Sold";
        $sql = "SELECT p.name, SUM(oi.quantity) as total_sold
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                JOIN products p ON p.id = oi.product_id
                WHERE $filterCondition
                GROUP BY p.name
                ORDER BY total_sold DESC";
        break;

    case 'customers':
        $title = "Top Customers";
        $sql = "SELECT c.name, c.email, SUM(o.total_amount) as total_spent
                FROM orders o
                JOIN customers c ON c.id = o.customer_id
                WHERE $filterCondition
                GROUP BY c.id
                ORDER BY total_spent DESC";
        break;

    case 'orders':
    default:
        $title = "Orders Report";
        $sql = "SELECT o.id, c.name as customer_name, o.total_amount, o.created_at
                FROM orders o
                JOIN customers c ON c.id = o.customer_id
                WHERE $filterCondition
                ORDER BY o.created_at DESC";
        break;
}

$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>
    <div class="container py-4">

        <h3 class="mb-4">Business Reports</h3>

        <form method="GET" class="row g-2 align-items-end mb-4">
            <div class="col-md-3">
                <label for="period" class="form-label">Select Time Period</label>
                <select name="period" id="period" class="form-select">
                    <option value="today" <?= $period == 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="week" <?= $period == 'week' ? 'selected' : '' ?>>This Week</option>
                    <option value="month" <?= $period == 'month' ? 'selected' : '' ?>>This Month</option>
                    <option value="year" <?= $period == 'year' ? 'selected' : '' ?>>This Year</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">Report Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="orders" <?= $type == 'orders' ? 'selected' : '' ?>>Orders Report</option>
                    <option value="products" <?= $type == 'products' ? 'selected' : '' ?>>Products Sold</option>
                    <option value="revenue" <?= $type == 'revenue' ? 'selected' : '' ?>>Revenue Summary</option>
                    <option value="customers" <?= $type == 'customers' ? 'selected' : '' ?>>Top Customers</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100">Apply Filters</button>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="button" class="btn btn-success w-50" onclick="exportCSV()">Export CSV</button>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><?= htmlspecialchars($title) ?></h5>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="reportTable">
                    <thead>
                        <tr>
                            <?php if (!empty($data)): ?>
                                <?php foreach (array_keys($data[0]) as $key): ?>
                                    <th><?= ucwords(str_replace('_', ' ', $key)) ?></th>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <th>No data available</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?= htmlspecialchars($cell) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SheetJS + jsPDF export logic -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        function exportCSV() {
            const wb = XLSX.utils.table_to_book(document.getElementById('reportTable'));
            XLSX.writeFile(wb, 'report.csv');
        }

        function exportPDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();
            doc.autoTable({
                html: '#reportTable'
            });
            doc.save('report.pdf');
        }
    </script>

    <?php include('includes/footer.php'); ?>