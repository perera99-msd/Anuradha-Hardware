<?php
include('includes/db.php');
include('includes/header.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Slider Management
    if (isset($_POST['save_slider'])) {
        foreach ($_POST['slider'] as $id => $slider) {
            $title = $conn->real_escape_string($slider['title']);
            $description = $conn->real_escape_string($slider['description']);
            $button_text = $conn->real_escape_string($slider['button_text']);
            $button_link = $conn->real_escape_string($slider['button_link']);
            $is_active = isset($slider['is_active']) ? 1 : 0;
            $order_num = intval($slider['order_num']);
            
            if (!empty($_FILES['slider']['name'][$id]['image'])) {
                $image_name = time() . '_' . $_FILES['slider']['name'][$id]['image'];
                $image_tmp = $_FILES['slider']['tmp_name'][$id]['image'];
                move_uploaded_file($image_tmp, '../images/slider/' . $image_name);
                $image_path = 'images/slider/' . $image_name;
                
                $conn->query("UPDATE home_page_content SET 
                    content_value = JSON_OBJECT('title', '$title', 'description', '$description', 'button_text', '$button_text', 'button_link', '$button_link'),
                    image_path = '$image_path',
                    order_num = $order_num,
                    is_active = $is_active
                    WHERE id = $id");
            } else {
                $conn->query("UPDATE home_page_content SET 
                    content_value = JSON_OBJECT('title', '$title', 'description', '$description', 'button_text', '$button_text', 'button_link', '$button_link'),
                    order_num = $order_num,
                    is_active = $is_active
                    WHERE id = $id");
            }
        }
        $_SESSION['message'] = 'Slider updated successfully';
    } 
    elseif (isset($_POST['add_slider'])) {
        $title = $conn->real_escape_string($_POST['new_slider']['title']);
        $description = $conn->real_escape_string($_POST['new_slider']['description']);
        $button_text = $conn->real_escape_string($_POST['new_slider']['button_text']);
        $button_link = $conn->real_escape_string($_POST['new_slider']['button_link']);
        
        $image_path = '';
        if (!empty($_FILES['new_slider']['name']['image'])) {
            $image_name = time() . '_' . $_FILES['new_slider']['name']['image'];
            $image_tmp = $_FILES['new_slider']['tmp_name']['image'];
            move_uploaded_file($image_tmp, '../images/slider/' . $image_name);
            $image_path = 'images/slider/' . $image_name;
        }
        
        $max_order = $conn->query("SELECT MAX(order_num) as max_order FROM home_page_content WHERE section = 'slider'")->fetch_assoc()['max_order'];
        $order_num = $max_order ? $max_order + 1 : 1;
        
        $conn->query("INSERT INTO home_page_content (section, content_key, content_value, image_path, order_num, is_active)
                      VALUES ('slider', 'slide_" . time() . "', 
                      JSON_OBJECT('title', '$title', 'description', '$description', 'button_text', '$button_text', 'button_link', '$button_link'),
                      '$image_path', $order_num, 1)");
        
        $_SESSION['message'] = 'Slider added successfully';
    } 
    elseif (isset($_POST['delete_slider'])) {
        $id = intval($_POST['delete_slider']);
        $conn->query("DELETE FROM home_page_content WHERE id = $id");
        $_SESSION['message'] = 'Slider deleted successfully';
    }
    
    // Featured Products Management
    elseif (isset($_POST['save_featured_products'])) {
        // Clear all current featured products
        $conn->query("UPDATE products SET is_featured = 0");
        
        // Set new featured products (limit to 8)
        if (isset($_POST['featured_products'])) {
            $count = 0;
            foreach ($_POST['featured_products'] as $product_id) {
                if ($count >= 8) break;
                $product_id = intval($product_id);
                $conn->query("UPDATE products SET is_featured = 1 WHERE id = $product_id");
                $count++;
            }
        }
        $_SESSION['message'] = 'Featured products updated successfully';
    }
    
    // Reviews Management
    elseif (isset($_POST['save_reviews'])) {
        foreach ($_POST['reviews'] as $id => $review) {
            $name = $conn->real_escape_string($review['name']);
            $role = $conn->real_escape_string($review['role']);
            $comment = $conn->real_escape_string($review['comment']);
            $rating = intval($review['rating']);
            $is_active = isset($review['is_active']) ? 1 : 0;
            $order_num = intval($review['order_num']);
            
            if (!empty($_FILES['reviews']['name'][$id]['image'])) {
                $image_name = time() . '_' . $_FILES['reviews']['name'][$id]['image'];
                $image_tmp = $_FILES['reviews']['tmp_name'][$id]['image'];
                move_uploaded_file($image_tmp, '../images/testimonials/' . $image_name);
                $image_path = 'images/testimonials/' . $image_name;
                
                $conn->query("UPDATE testimonials SET 
                    name = '$name',
                    role = '$role',
                    comment = '$comment',
                    rating = $rating,
                    image_path = '$image_path',
                    order_num = $order_num,
                    is_active = $is_active
                    WHERE id = $id");
            } else {
                $conn->query("UPDATE testimonials SET 
                    name = '$name',
                    role = '$role',
                    comment = '$comment',
                    rating = $rating,
                    order_num = $order_num,
                    is_active = $is_active
                    WHERE id = $id");
            }
        }
        $_SESSION['message'] = 'Reviews updated successfully';
    } 
    elseif (isset($_POST['add_review'])) {
        $name = $conn->real_escape_string($_POST['new_review']['name']);
        $role = $conn->real_escape_string($_POST['new_review']['role']);
        $comment = $conn->real_escape_string($_POST['new_review']['comment']);
        $rating = intval($_POST['new_review']['rating']);
        
        $image_path = '';
        if (!empty($_FILES['new_review']['name']['image'])) {
            $image_name = time() . '_' . $_FILES['new_review']['name']['image'];
            $image_tmp = $_FILES['new_review']['tmp_name']['image'];
            move_uploaded_file($image_tmp, '../images/testimonials/' . $image_name);
            $image_path = 'images/testimonials/' . $image_name;
        }
        
        $max_order = $conn->query("SELECT MAX(order_num) as max_order FROM testimonials")->fetch_assoc()['max_order'];
        $order_num = $max_order ? $max_order + 1 : 1;
        
        $conn->query("INSERT INTO testimonials (name, role, comment, rating, image_path, order_num, is_active)
                      VALUES ('$name', '$role', '$comment', $rating, '$image_path', $order_num, 1)");
        
        $_SESSION['message'] = 'Review added successfully';
    } 
    elseif (isset($_POST['delete_review'])) {
        $id = intval($_POST['delete_review']);
        $conn->query("DELETE FROM testimonials WHERE id = $id");
        $_SESSION['message'] = 'Review deleted successfully';
    }
    
    // Redirect to prevent form resubmission
    header("Location: homepage_management.php");
    exit();
}

// Fetch all sliders
$sliders = $conn->query("SELECT * FROM home_page_content WHERE section = 'slider' ORDER BY order_num ASC");

// Fetch all products for featured selection
$products = $conn->query("SELECT id, name FROM products ORDER BY name ASC");

// Fetch current featured products with names
$featured_products = $conn->query("SELECT id, name FROM products WHERE is_featured = 1 ORDER BY name ASC");
$featured_ids = [];
$featured_names = [];
while ($row = $featured_products->fetch_assoc()) {
    $featured_ids[] = $row['id'];
    $featured_names[] = $row['name'];
}

// Fetch all reviews
$reviews = $conn->query("SELECT * FROM testimonials ORDER BY order_num ASC");
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>
    <div class="p-4 w-100">
        <h2 class="mb-3">Home Page Management</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <!-- Slider Management -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Hero Slider Management</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Image</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Button Text</th>
                                    <th>Button Link</th>
                                    <th width="8%">Order</th>
                                    <th width="8%">Active</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($sliders->num_rows > 0): ?>
                                    <?php while ($slider = $sliders->fetch_assoc()): 
                                        $content = json_decode($slider['content_value'], true);
                                    ?>
                                        <tr>
                                            <td><?= $slider['id'] ?></td>
                                            <td>
                                                <?php if ($slider['image_path']): ?>
                                                    <img src="../<?= $slider['image_path'] ?>" class="img-thumbnail" style="max-height: 80px;">
                                                <?php endif; ?>
                                                <input type="file" name="slider[<?= $slider['id'] ?>][image]" class="form-control mt-2">
                                            </td>
                                            <td>
                                                <input type="text" name="slider[<?= $slider['id'] ?>][title]" 
                                                    value="<?= htmlspecialchars($content['title'] ?? '') ?>" class="form-control" required>
                                            </td>
                                            <td>
                                                <textarea name="slider[<?= $slider['id'] ?>][description]" class="form-control" rows="2"><?= htmlspecialchars($content['description'] ?? '') ?></textarea>
                                            </td>
                                            <td>
                                                <input type="text" name="slider[<?= $slider['id'] ?>][button_text]" 
                                                    value="<?= htmlspecialchars($content['button_text'] ?? '') ?>" class="form-control">
                                            </td>
                                            <td>
                                                <input type="text" name="slider[<?= $slider['id'] ?>][button_link]" 
                                                    value="<?= htmlspecialchars($content['button_link'] ?? '') ?>" class="form-control">
                                            </td>
                                            <td>
                                                <input type="number" name="slider[<?= $slider['id'] ?>][order_num]" 
                                                    value="<?= $slider['order_num'] ?>" class="form-control">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="slider[<?= $slider['id'] ?>][is_active]" 
                                                    <?= $slider['is_active'] ? 'checked' : '' ?>>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="if(confirm('Delete this slider?')) { 
                                                        document.getElementById('delete_slider_<?= $slider['id'] ?>').submit(); 
                                                    }">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No sliders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-end mt-3">
                        <button type="submit" name="save_slider" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
                
                <!-- Hidden forms for delete actions -->
                <?php if ($sliders->num_rows > 0): 
                    $sliders->data_seek(0); // Reset pointer
                    while ($slider = $sliders->fetch_assoc()): ?>
                        <form id="delete_slider_<?= $slider['id'] ?>" method="POST">
                            <input type="hidden" name="delete_slider" value="<?= $slider['id'] ?>">
                        </form>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Add New Slider Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Add New Slider</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Image</label>
                                <input type="file" name="new_slider[image]" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Title</label>
                                        <input type="text" name="new_slider[title]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Button Text</label>
                                        <input type="text" name="new_slider[button_text]" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Description</label>
                                        <textarea name="new_slider[description]" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Button Link</label>
                                        <input type="text" name="new_slider[button_link]" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="add_slider" class="btn btn-success">Add Slider</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Featured Products Management -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Featured Products Management (Max 8)</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Currently Featured Products (<?= count($featured_ids) ?>/8)</label>
                        
                        <?php if (!empty($featured_names)): ?>
                            <div class="alert alert-info mb-3">
                                <strong>Selected Products:</strong>
                                <ul class="mb-0">
                                    <?php foreach ($featured_names as $name): ?>
                                        <li><?= htmlspecialchars($name) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">No products are currently featured</div>
                        <?php endif; ?>
                        
                        <label class="form-label">Select Featured Products</label>
                        <select name="featured_products[]" class="form-select" multiple size="10" style="height: auto;">
                            <?php 
                            $products->data_seek(0); // Reset pointer
                            while ($product = $products->fetch_assoc()): ?>
                                <option value="<?= $product['id'] ?>" <?= in_array($product['id'], $featured_ids) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($product['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple products (max 8)</small>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="save_featured_products" class="btn btn-info">Save Featured Products</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Reviews Management -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Customer & Contractor Reviews</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Image</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Comment</th>
                                    <th width="8%">Rating</th>
                                    <th width="8%">Order</th>
                                    <th width="8%">Active</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($reviews->num_rows > 0): ?>
                                    <?php while ($review = $reviews->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $review['id'] ?></td>
                                            <td>
                                                <?php if ($review['image_path']): ?>
                                                    <img src="../<?= $review['image_path'] ?>" class="img-thumbnail" style="max-height: 80px;">
                                                <?php endif; ?>
                                                <input type="file" name="reviews[<?= $review['id'] ?>][image]" class="form-control mt-2">
                                            </td>
                                            <td>
                                                <input type="text" name="reviews[<?= $review['id'] ?>][name]" 
                                                    value="<?= htmlspecialchars($review['name']) ?>" class="form-control" required>
                                            </td>
                                            <td>
                                                <input type="text" name="reviews[<?= $review['id'] ?>][role]" 
                                                    value="<?= htmlspecialchars($review['role']) ?>" class="form-control" required>
                                            </td>
                                            <td>
                                                <textarea name="reviews[<?= $review['id'] ?>][comment]" class="form-control" rows="2" required><?= htmlspecialchars($review['comment']) ?></textarea>
                                            </td>
                                            <td>
                                                <select name="reviews[<?= $review['id'] ?>][rating]" class="form-control" required>
                                                    <option value="1" <?= $review['rating'] == 1 ? 'selected' : '' ?>>1</option>
                                                    <option value="2" <?= $review['rating'] == 2 ? 'selected' : '' ?>>2</option>
                                                    <option value="3" <?= $review['rating'] == 3 ? 'selected' : '' ?>>3</option>
                                                    <option value="4" <?= $review['rating'] == 4 ? 'selected' : '' ?>>4</option>
                                                    <option value="5" <?= $review['rating'] == 5 ? 'selected' : '' ?>>5</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="reviews[<?= $review['id'] ?>][order_num]" 
                                                    value="<?= $review['order_num'] ?>" class="form-control">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="reviews[<?= $review['id'] ?>][is_active]" 
                                                    <?= $review['is_active'] ? 'checked' : '' ?>>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="if(confirm('Delete this review?')) { 
                                                        document.getElementById('delete_review_<?= $review['id'] ?>').submit(); 
                                                    }">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No reviews found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-end mt-3">
                        <button type="submit" name="save_reviews" class="btn btn-warning">Save Changes</button>
                    </div>
                </form>
                
                <!-- Hidden forms for delete actions -->
                <?php if ($reviews->num_rows > 0): 
                    $reviews->data_seek(0); // Reset pointer
                    while ($review = $reviews->fetch_assoc()): ?>
                        <form id="delete_review_<?= $review['id'] ?>" method="POST">
                            <input type="hidden" name="delete_review" value="<?= $review['id'] ?>">
                        </form>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Add New Review Form -->
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Add New Review</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Image</label>
                                <input type="file" name="new_review[image]" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Name</label>
                                        <input type="text" name="new_review[name]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Role (e.g., Customer, Contractor)</label>
                                        <input type="text" name="new_review[role]" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Rating</label>
                                        <select name="new_review[rating]" class="form-control" required>
                                            <option value="5">5 Stars</option>
                                            <option value="4">4 Stars</option>
                                            <option value="3">3 Stars</option>
                                            <option value="2">2 Stars</option>
                                            <option value="1">1 Star</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Comment</label>
                                <textarea name="new_review[comment]" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="add_review" class="btn btn-secondary">Add Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>