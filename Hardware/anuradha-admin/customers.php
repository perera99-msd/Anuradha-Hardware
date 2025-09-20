<?php
include('includes/db.php');
include('includes/header.php');

// ✅ Add customer
if (isset($_POST['add_customer'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $address);
    $stmt->execute();
}

// ✅ Update customer
if (isset($_POST['update_customer'])) {
    $id = $_POST['customer_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $email, $phone, $address, $id);
    $stmt->execute();
}

// ✅ Delete customer
if (isset($_POST['delete_customer'])) {
    $id = $_POST['customer_id'];
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// ✅ Search and pagination
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM customers WHERE 1=1";
if (!empty($search)) {
    $safeSearch = $conn->real_escape_string($search);
    $query .= " AND (name LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%' OR phone LIKE '%$safeSearch%')";
}

$total = mysqli_num_rows(mysqli_query($conn, $query));
$totalPages = ceil($total / $limit);

$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>
    <div class="p-4 w-100">
        <h2 class="mb-3">Customers</h2>

        <!-- ✅ Search + Add -->
        <div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" placeholder="Search name/email/phone" class="form-control form-control-sm" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-sm btn-primary">Search</button>
                <a href="customers.php" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerModal">+ Add Customer</button>
        </div>

        <!-- ✅ Table -->
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email / Phone</th>
                            <th>Address</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($row['email']) ?><br>
                                        <small><?= htmlspecialchars($row['phone']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['address']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>
                                    </td>
                                </tr>

                                <!-- ✅ Edit Modal -->
                                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Customer</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="customer_id" value="<?= $row['id'] ?>">
                                                    <div class="mb-3">
                                                        <label>Name</label>
                                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Email</label>
                                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Phone</label>
                                                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($row['phone']) ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Address</label>
                                                        <textarea name="address" class="form-control"><?= htmlspecialchars($row['address']) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="update_customer" class="btn btn-success">Update</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- ✅ Delete Modal -->
                                <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <input type="hidden" name="customer_id" value="<?= $row['id'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete Customer</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete "<?= htmlspecialchars($row['name']) ?>"?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="delete_customer" class="btn btn-danger">Delete</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No customers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ✅ Pagination -->
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

        <!-- ✅ Add Customer Modal -->
        <div class="modal fade" id="addCustomerModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="add_customer" class="btn btn-success">Add</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include('includes/footer.php'); ?>