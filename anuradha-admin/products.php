<?php
include('includes/db.php');
include('includes/header.php');

// Handle Add Product
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
    }

    mysqli_query($conn, "INSERT INTO products (name, category_id, price, stock, image) 
                         VALUES ('$name', '$category_id', '$price', '$stock', '$image')");
    header("Location: products.php");
    exit();
}

// Handle Update Product
if (isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    if (!empty($_FILES['image']['name'])) {
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
        mysqli_query($conn, "UPDATE products SET name='$name', category_id='$category_id', price='$price', stock='$stock', image='$image' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE products SET name='$name', category_id='$category_id', price='$price', stock='$stock' WHERE id=$id");
    }
    header("Location: products.php");
    exit();
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    header("Location: products.php");
    exit();
}

// Check if categories table exists
$categories_result = mysqli_query($conn, "SHOW TABLES LIKE 'categories'");
if (mysqli_num_rows($categories_result) === 0) {
    echo "<div class='alert alert-danger m-4'>⚠️ The <strong>categories</strong> table does not exist. Please create it first in your database.</div>";
    include('includes/footer.php');
    exit();
}

// Fetch categories for forms
$categories = mysqli_query($conn, "SELECT * FROM categories");

// Search logic
$search = '';
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $products = mysqli_query($conn, "
        SELECT p.*, c.name AS category 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.name LIKE '%$search%' OR c.name LIKE '%$search%'
        ORDER BY p.id DESC
    ");
} else {
    $products = mysqli_query($conn, "
        SELECT p.*, c.name AS category 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
    ");
}
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>

    <div class="p-4 w-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Products</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">+ Add Product</button>
        </div>

        <!-- Search -->
        <form method="get" class="d-flex mb-3" role="search">
            <input class="form-control me-2" type="search" name="search" placeholder="Search by name or category" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-primary me-2" type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="products.php" class="btn btn-outline-secondary">Clear Search</a>
            <?php endif; ?>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price (Rs.)</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($products) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <?php if ($row['image']): ?>
                                        <img src="uploads/<?= $row['image'] ?>" width="50" height="50" style="object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td>Rs. <?= number_format($row['price'], 2) ?></td>
                                <td><?= $row['stock'] ?></td>
                                <td>
                                    <button
                                        class="btn btn-sm btn-warning editBtn"
                                        data-id="<?= $row['id'] ?>"
                                        data-name="<?= htmlspecialchars($row['name']) ?>"
                                        data-category="<?= $row['category_id'] ?>"
                                        data-price="<?= $row['price'] ?>"
                                        data-stock="<?= $row['stock'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editProductModal">Edit</button>
                                    <a href="products.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this product?');" class="btn btn-sm btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" name="name" class="form-control mb-2" placeholder="Product Name" required>
                <select name="category_id" class="form-control mb-2" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price" required>
                <input type="number" name="stock" class="form-control mb-2" placeholder="Stock" required>
                <input type="file" name="image" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" id="editProductId">
                <input type="text" name="name" id="editName" class="form-control mb-2" required>
                <select name="category_id" id="editCategory" class="form-control mb-2" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" step="0.01" name="price" id="editPrice" class="form-control mb-2" required>
                <input type="number" name="stock" id="editStock" class="form-control mb-2" required>
                <input type="file" name="image" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_product" class="btn btn-warning">Update Product</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editProductId').value = btn.dataset.id;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editCategory').value = btn.dataset.category;
            document.getElementById('editPrice').value = btn.dataset.price;
            document.getElementById('editStock').value = btn.dataset.stock;
        });
    });
</script>

<?php include('includes/footer.php'); ?>