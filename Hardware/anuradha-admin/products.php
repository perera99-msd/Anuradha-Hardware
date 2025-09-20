<?php
include('includes/db.php');
include('includes/header.php');

// --- Fetch categories first ---
$categories_result = mysqli_query($conn, "SELECT * FROM categories");
$categories = [];
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[$row['id']] = $row['name'];
}

// Handle Add Product
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = $_POST['category_id'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = '';

    // Validate category
    if (!array_key_exists($category_id, $categories)) {
        die("Invalid category selected.");
    }

    // Validate price/stock
    if ($price < 0 || $stock < 0) {
        die("Price and stock must be non-negative.");
    }

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        // Create uploads directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_filename = time() . '_' . basename($_FILES['image']['name']);
        $image = 'uploads/' . $image_filename;
        $target_file = $image;
        
        // Check if file is an actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // File uploaded successfully
            } else {
                die("Sorry, there was an error uploading your file.");
            }
        } else {
            die("File is not an image.");
        }
    }

    mysqli_query($conn, "INSERT INTO products (name, category_id, description, price, stock, image) 
                         VALUES ('$name', '$category_id', '$description', '$price', '$stock', '$image')");
    header("Location: products.php?msg=added");
    exit();
}

// Handle Update Product
if (isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = $_POST['category_id'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    if (!array_key_exists($category_id, $categories)) {
        die("Invalid category selected.");
    }

    if ($price < 0 || $stock < 0) {
        die("Price and stock must be non-negative.");
    }

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        // Create uploads directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_filename = time() . '_' . basename($_FILES['image']['name']);
        $image = 'uploads/' . $image_filename;
        $target_file = $image;
        
        // Check if file is an actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Delete old image if it exists
                $getOldImg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM products WHERE id=$id"));
                if ($getOldImg && $getOldImg['image'] && file_exists($getOldImg['image'])) {
                    unlink($getOldImg['image']);
                }
                
                mysqli_query($conn, "UPDATE products SET name='$name', category_id='$category_id', description='$description', price='$price', stock='$stock', image='$image' WHERE id=$id");
            } else {
                die("Sorry, there was an error uploading your file.");
            }
        } else {
            die("File is not an image.");
        }
    } else {
        mysqli_query($conn, "UPDATE products SET name='$name', category_id='$category_id', description='$description', price='$price', stock='$stock' WHERE id=$id");
    }
    header("Location: products.php?msg=updated");
    exit();
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $getImg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM products WHERE id=$id"));
    if ($getImg && $getImg['image'] && file_exists($getImg['image'])) {
        unlink($getImg['image']);
    }
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    header("Location: products.php?msg=deleted");
    exit();
}

// Search
$search = '';
$where = '';
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = "WHERE p.name LIKE '%$search%' OR c.name LIKE '%$search%' OR p.description LIKE '%$search%'";
}

// Fetch products
$products = mysqli_query($conn, "
    SELECT p.*, c.name AS category 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY p.id DESC
");
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>

    <div class="p-4 w-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Products</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">+ Add Product</button>
        </div>

        <!-- Message Alert -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= ucfirst($_GET['msg']) ?> successfully.</div>
        <?php endif; ?>

        <!-- Search -->
        <form method="get" class="d-flex mb-3" role="search">
            <input class="form-control me-2" type="search" name="search" placeholder="Search by name, category or description" value="<?= htmlspecialchars($search) ?>">
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
                                    <?php if ($row['image'] && file_exists($row['image'])): ?>
                                        <img src="<?= $row['image'] ?>" width="50" height="50" style="object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['name']) ?></strong>
                                    <?php if (!empty($row['description'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($row['description'], 0, 50)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td>Rs. <?= number_format($row['price'], 2) ?></td>
                                <td><?= $row['stock'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning editBtn"
                                        data-id="<?= $row['id'] ?>"
                                        data-name="<?= htmlspecialchars($row['name']) ?>"
                                        data-description="<?= htmlspecialchars($row['description']) ?>"
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
    <div class="modal-dialog modal-lg">
        <form method="post" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Product Name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $id => $name): ?>
                                    <option value="<?= $id ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Product description"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Price (Rs.)</label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" placeholder="Stock" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" id="editProductId">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="editCategory" class="form-control" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $id => $name): ?>
                                    <option value="<?= $id ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Price (Rs.)</label>
                            <input type="number" step="0.01" name="price" id="editPrice" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="editStock" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">Leave empty to keep current image</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
            document.getElementById('editDescription').value = btn.dataset.description;
            document.getElementById('editCategory').value = btn.dataset.category;
            document.getElementById('editPrice').value = btn.dataset.price;
            document.getElementById('editStock').value = btn.dataset.stock;
        });
    });
</script>

<?php include('includes/footer.php'); ?>