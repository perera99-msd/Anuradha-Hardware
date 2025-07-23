<?php
include('includes/db.php');
include('includes/header.php');

// --- Handle Add Category ---
if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    mysqli_query($conn, "INSERT INTO categories (name, description) VALUES ('$name', '$description')");
    header("Location: categories.php");
    exit();
}

// --- Handle Update Category ---
if (isset($_POST['update_category'])) {
    $id = $_POST['category_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    mysqli_query($conn, "UPDATE categories SET name='$name', description='$description' WHERE id=$id");
    header("Location: categories.php");
    exit();
}

// --- Handle Delete Category ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
    header("Location: categories.php");
    exit();
}

// --- Handle Search ---
$search = '';
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $categories = mysqli_query($conn, "SELECT * FROM categories WHERE name LIKE '%$search%' ORDER BY id DESC");
} else {
    $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
}
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>
    <div class="p-4 w-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Categories</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add Category</button>
        </div>

        <!-- Search Bar -->
        <form method="GET" class="input-group mb-3" style="max-width: 400px;">
            <input type="text" name="search" class="form-control" placeholder="Search by category name..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
            <?php if ($search): ?>
                <a href="categories.php" class="btn btn-outline-danger">Reset</a>
            <?php endif; ?>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($categories) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td>
                                    <button
                                        class="btn btn-sm btn-warning editBtn"
                                        data-id="<?= $row['id'] ?>"
                                        data-name="<?= htmlspecialchars($row['name']) ?>"
                                        data-description="<?= htmlspecialchars($row['description']) ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editCategoryModal">Edit</button>
                                    <a href="categories.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this category?');" class="btn btn-sm btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" name="name" class="form-control mb-2" placeholder="Category Name" required>
                <textarea name="description" class="form-control mb-2" placeholder="Description" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <input type="hidden" name="category_id" id="editCategoryId">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" name="name" id="editCategoryName" class="form-control mb-2" required>
                <textarea name="description" id="editCategoryDescription" class="form-control mb-2" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_category" class="btn btn-warning">Update Category</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editCategoryId').value = btn.dataset.id;
            document.getElementById('editCategoryName').value = btn.dataset.name;
            document.getElementById('editCategoryDescription').value = btn.dataset.description;
        });
    });
</script>

<?php include('includes/footer.php'); ?>