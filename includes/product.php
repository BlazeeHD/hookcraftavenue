<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Load all categories
$categories = [];
$catQuery = $conn->query("SELECT id, name FROM categories ORDER BY name");
while ($cat = $catQuery->fetch_assoc()) {
    $categories[$cat['id']] = $cat['name'];
}

// Function to get table and id field from category name
function getCategoryTableInfo($categoryName) {
    $safeName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $categoryName));
    $tableName = $safeName . "_products";
    $idField = $safeName . "_id";
    return [$tableName, $idField];
}

// Function to create category product table if missing (with description field)
function createCategoryTableIfNotExists($conn, $tableName, $idField, $categoryId) {
    $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
        `$idField` INT AUTO_INCREMENT PRIMARY KEY,
        `category_id` INT NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `image` VARCHAR(255) NOT NULL,
        `stock` INT NOT NULL DEFAULT 0,
        `description` TEXT DEFAULT NULL,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    return $conn->query($sql);
}

/** ADD CATEGORY **/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $categoryName = trim($_POST['category_name']);
    
    if (!empty($categoryName)) {
        // Check if category already exists
        $checkStmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $checkStmt->bind_param("s", $categoryName);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows == 0) {
            // Insert new category
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $categoryName);
            
            if ($stmt->execute()) {
                $newCategoryId = $conn->insert_id;
                
                // Create corresponding product table
                list($tableName, $idField) = getCategoryTableInfo($categoryName);
                createCategoryTableIfNotExists($conn, $tableName, $idField, $newCategoryId);
                
                $_SESSION['success'] = "Category '$categoryName' added successfully!";
            } else {
                $_SESSION['error'] = "Failed to add category.";
            }
        } else {
            $_SESSION['error'] = "Category '$categoryName' already exists.";
        }
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/** DELETE CATEGORY **/
if (isset($_GET['delete_category'])) {
    $categoryId = intval($_GET['delete_category']);
    
    if (isset($categories[$categoryId]) && !in_array($categoryId, [1, 2, 3])) { // Protect default categories
        $categoryName = $categories[$categoryId];
        list($tableName, $idField) = getCategoryTableInfo($categoryName);
        
        // Drop the product table first
        $conn->query("DROP TABLE IF EXISTS `$tableName`");
        
        // Delete the category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        
        $_SESSION['success'] = "Category '$categoryName' deleted successfully!";
    } else {
        $_SESSION['error'] = "Cannot delete this category.";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/** ADD PRODUCT **/
/** ADD PRODUCT **/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $categoryId = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $description = trim($_POST['description']);
    $imageName = '';

    // Upload image
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../asset/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filename = time() . "_" . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imageName = $filename;
        } else {
            $_SESSION['error'] = "Failed to upload image.";
        }
    }

    if (isset($categories[$categoryId]) && !empty($name) && $price > 0) {
        $categoryName = $categories[$categoryId];
        list($tableName, $idField) = getCategoryTableInfo($categoryName);

        // Create table if not exists
        createCategoryTableIfNotExists($conn, $tableName, $idField, $categoryId);

        $stmt = $conn->prepare("INSERT INTO `$tableName` (category_id, name, price, image, stock, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsis", $categoryId, $name, $price, $imageName, $stock, $description);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Product added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add product: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Please fill in all required fields correctly.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/** EDIT PRODUCT **/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $categoryId = intval($_POST['category_id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $description = trim($_POST['description']);

    $imageSql = "";
    $params = [$name, $price, $stock, $description];
    $types = "sdis";

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../asset/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filename = time() . "_" . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imageName = $filename;
            $imageSql = ", image = ?";
            $params[] = $imageName;
            $types .= "s";
        }
    }

    $params[] = $id;
    $types .= "i";

    if (isset($categories[$categoryId])) {
        $categoryName = $categories[$categoryId];
        list($tableName, $idField) = getCategoryTableInfo($categoryName);
        createCategoryTableIfNotExists($conn, $tableName, $idField, $categoryId);

        $query = "UPDATE `$tableName` SET name = ?, price = ?, stock = ?, description = ? $imageSql WHERE `$idField` = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Product updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update product.";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/** DELETE PRODUCT **/
if (isset($_GET['delete']) && isset($_GET['category_id'])) {
    $id = intval($_GET['delete']);
    $categoryId = intval($_GET['category_id']);
    
    if (isset($categories[$categoryId])) {
        $categoryName = $categories[$categoryId];
        list($tableName, $idField) = getCategoryTableInfo($categoryName);
        
        // Get image name before deletion
        $imageQuery = $conn->prepare("SELECT image FROM `$tableName` WHERE `$idField` = ?");
        $imageQuery->bind_param("i", $id);
        $imageQuery->execute();
        $imageResult = $imageQuery->get_result();
        $imageRow = $imageResult->fetch_assoc();
        
        $stmt = $conn->prepare("DELETE FROM `$tableName` WHERE `$idField` = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete image file if exists
            if ($imageRow && !empty($imageRow['image'])) {
                $imagePath = __DIR__ . '/../asset/images/' . $imageRow['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $_SESSION['success'] = "Product deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete product.";
        }
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/** FETCH ALL PRODUCTS **/
$productResults = [];
$unionQueries = [];
foreach ($categories as $catId => $catName) {
    list($tableName, $idField) = getCategoryTableInfo($catName);
    // Create table if not exists for safety
    createCategoryTableIfNotExists($conn, $tableName, $idField, $catId);
    $unionQueries[] = "
        SELECT '$catId' AS category_id, '$catName' AS category_name, $idField AS id, name, price, stock, image, description
        FROM `$tableName`
    ";
}
if ($unionQueries) {
    $sql = implode(" UNION ALL ", $unionQueries) . " ORDER BY category_id, id DESC";
    $productResults = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - Hookcraft Avenue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../asset/dashboard.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="main-content p-4">
    <!-- Display Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Category Management -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between">
            <h5 class="mb-0">Category Management</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($categories as $catId => $catName): ?>
                    <div class="col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary"><?= htmlspecialchars($catName) ?></span>
                                <?php if (!in_array($catId, [1, 2, 3])): // Don't allow deletion of default categories ?>
                                    <a href="?delete_category=<?= $catId ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete category \'<?= htmlspecialchars($catName) ?>\'? This will also delete all products in this category.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Product Management -->
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h4 class="mb-0">Product Management</h4>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($productResults && $productResults->num_rows > 0): ?>
                        <?php while ($row = $productResults->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['category_name']) ?></span></td>
                                <td>₱<?= number_format($row['price'], 2) ?></td>
                                <td><?= $row['stock'] ?></td>
                                <td>
                                    <?php if (!empty($row['description'])): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= htmlspecialchars($row['description']) ?>">
                                            <?= htmlspecialchars(substr($row['description'], 0, 50)) ?>...
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No description</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['image']): ?>
                                        <img src="../asset/images/<?= htmlspecialchars($row['image']) ?>" width="50" class="img-thumbnail">
                                    <?php else: ?>
                                        <i class="fas fa-image text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['category_id'] ?>_<?= $row['id'] ?>"><i class="fas fa-edit"></i></a>
                                    <a href="?delete=<?= $row['id'] ?>&category_id=<?= $row['category_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?= $row['category_id'] ?>_<?= $row['id'] ?>">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="edit_product" value="1">
                                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="category_id" value="<?= $row['category_id'] ?>">
                                            <div class="modal-header bg-warning">
                                                <h5>Edit Product</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Product Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Price</label>
                                                    <input type="number" step="0.01" name="price" class="form-control" value="<?= $row['price'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Stock</label>
                                                    <input type="number" name="stock" class="form-control" value="<?= $row['stock'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="4" placeholder="Enter product description"><?= htmlspecialchars($row['description']) ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Image</label>
                                                    <input type="file" name="image" class="form-control" accept="image/*">
                                                    <?php if ($row['image']): ?>
                                                        <small class="text-muted">Current: <?= htmlspecialchars($row['image']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No products found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="add_category" value="1">
                <div class="modal-header bg-success text-white">
                    <h5>Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name" class="form-control" placeholder="Enter category name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_product" value="1">
                <div class="modal-header bg-primary text-white">
                    <h5>Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Product Name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $catId => $catName): ?>
                                <option value="<?= $catId ?>"><?= htmlspecialchars($catName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Enter product description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>