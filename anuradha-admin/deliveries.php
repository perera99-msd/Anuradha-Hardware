<?php
include('includes/db.php');
include('includes/header.php');

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Base query
$query = "SELECT o.*, c.name AS customer_name 
          FROM orders o 
          JOIN customers c ON o.customer_id = c.id 
          WHERE 1=1";

// Filters
if (!empty($search)) {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $query .= " AND c.name LIKE '%$search_esc%'";
}
if (!empty($status)) {
    $status_esc = mysqli_real_escape_string($conn, $status);
    $query .= " AND o.status = '$status_esc'";
}
if (!empty($from)) {
    $query .= " AND o.created_at >= '$from'";
}
if (!empty($to)) {
    $query .= " AND o.created_at <= '$to'";
}

// Pagination logic
$total = mysqli_num_rows(mysqli_query($conn, $query));
$totalPages = ceil($total / $limit);
$query .= " ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";
$orders = mysqli_query($conn, $query);

// ✅ Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
    mysqli_stmt_execute($stmt);
    header("Location: orders.php?updated=1");
    exit;
}
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>
    <div class="p-4 w-100">
        <h2 class="mb-3">Delivery</h2>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Order status updated successfully.</div>
        <?php endif; ?>

        <!-- Filters -->
        <form method="GET" class="row g-2 align-items-end mb-4">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by customer" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php
                    $statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
                    foreach ($statuses as $s) {
                        $sel = ($status === $s) ? 'selected' : '';
                        echo "<option value='$s' $sel>$s</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">Search</button>
                <a href="orders.php" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>

        <!-- Orders Table -->
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Total (Rs)</th>
                            <th>Status</th>
                            <th>Ordered On</th>
                            <th>Delivery</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($orders) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($orders)): ?>
                                <?php
                                $badgeClass = match ($row['status']) {
                                    'Pending' => 'warning',
                                    'Processing' => 'info',
                                    'Completed' => 'success',
                                    'Cancelled' => 'danger',
                                    default => 'secondary',
                                };
                                $orderedDate = strtotime($row['created_at']);
                                $deliveryDate = strtotime('+7 days', $orderedDate);
                                $showDelivery = time() >= $deliveryDate;
                                ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                    <td><?= number_format($row['total_amount'], 2) ?></td>
                                    <td><span class="badge bg-<?= $badgeClass ?>"><?= $row['status'] ?></span></td>
                                    <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <?= $showDelivery ? date('Y-m-d', $deliveryDate) : "<span class='text-muted'>Not yet</span>" ?>
                                    </td>
                                    <td>
                                        <!-- View -->
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['id'] ?>">View</button>
                                        <!-- Change -->
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Change</button>
                                    </td>
                                </tr>

                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content bg-white">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Order #<?= $row['id'] ?> Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Status:</strong> <?= $row['status'] ?></p>
                                                <p><strong>Total Amount:</strong> Rs <?= number_format($row['total_amount'], 2) ?></p>
                                                <p><strong>Customer:</strong> <?= htmlspecialchars($row['customer_name']) ?></p>
                                                <p><strong>Ordered On:</strong> <?= date('Y-m-d', strtotime($row['created_at'])) ?></p>
                                                <p><strong>Delivery Date:</strong> <?= $showDelivery ? date('Y-m-d', $deliveryDate) : "Not yet available" ?></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content bg-white">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Change Order #<?= $row['id'] ?> Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">New Status</label>
                                                        <select name="new_status" class="form-select" required>
                                                            <?php
                                                            foreach ($statuses as $s) {
                                                                $sel = ($row['status'] === $s) ? 'selected' : '';
                                                                echo "<option value='$s' $sel>$s</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="update_status" class="btn btn-success">Update</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer text-center">
                    <ul class="pagination justify-content-center m-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>