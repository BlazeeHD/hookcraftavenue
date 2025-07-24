<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $imageName = '';

    // Use image from ../asset/images/
    if (!empty($_FILES['image']['name'])) {
        $filename = basename($_FILES['image']['name']);
        $imagePath = '../asset/images/' . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/' . $imagePath)) {
            $imageName = $imagePath; // Save relative path to asset folder
        }
    }

    $stmt = $conn->prepare("INSERT INTO products (name, category, price, stock, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $name, $category, $price, $stock, $imageName);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    $imageSql = "";
    $params = [$name, $category, $price, $stock];
    $types = "ssdi";

    // Handle image update from ../asset/images/
    if (!empty($_FILES['image']['name'])) {
        $filename = basename($_FILES['image']['name']);
        $imagePath = '../asset/images/' . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/' . $imagePath)) {
            $imageName = $imagePath; // Save relative path
            $imageSql = ", image = ?";
            $params[] = $imageName;
            $types .= "s";
        }
    }

    $params[] = $id;
    $types .= "i";

    $query = "UPDATE products SET name = ?, category = ?, price = ?, stock = ? $imageSql WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch products
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Hookcraft Avenue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../asset/dashboard.css">
    <style>
        .main-content {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .product-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px 15px 0 0;
            border: none;
            color: white;
            padding: 1.5rem;
        }
        
        .table-modern {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .table-modern thead {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }
        
        .table-modern tbody tr {
            transition: all 0.3s ease;
        }
        
        .table-modern tbody tr:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: scale(1.02);
        }
        
        .btn-modern {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-add {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
            color: white;
        }
        
        .product-image {
            border-radius: 8px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-image:hover {
            transform: scale(1.1);
        }
        
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            border-radius: 15px 15px 0 0;
            border-bottom: 1px solid #eee;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .badge-stock {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .stock-low { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); }
        .stock-medium { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
        .stock-high { background: linear-gradient(135deg, #d299c2 0%, #fef9d7 100%); }
        
        .price-display {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="main-content p-4">
    <div class="product-card card">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fas fa-box-open me-2"></i>Product Management</h4>
                <small class="opacity-75">Manage your inventory with ease</small>
            </div>
            <button class="btn btn-add btn-modern" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-2"></i>Add Product
            </button>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover align-middle text-center mb-0">
                    <thead>
                        <tr>
                            <th class="py-3">ID</th>
                            <th class="py-3">Product</th>
                            <th class="py-3">Category</th>
                            <th class="py-3">Price</th>
                            <th class="py-3">Stock</th>
                            <th class="py-3">Image</th>
                            <th class="py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?= $row['id'] ?></td>
                                <td class="text-start">
                                    <div class="fw-bold text-primary"><?= htmlspecialchars($row['name']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill"><?= htmlspecialchars($row['category']) ?></span>
                                </td>
                                <td class="price-display">₱<?= number_format($row['price'], 2) ?></td>
                                <td>
                                    <?php 
                                    $stockClass = $row['stock'] < 10 ? 'stock-low' : ($row['stock'] < 50 ? 'stock-medium' : 'stock-high');
                                    ?>
                                    <span class="badge badge-stock <?= $stockClass ?>"><?= $row['stock'] ?></span>
                                </td>
                                <td>
                                    <?php if ($row['image']): ?>
                                        <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" 
                                             width="60" height="60" class="product-image" 
                                             alt="Product Image">
                                    <?php else: ?>
                                        <div class="text-muted">
                                            <i class="fas fa-image fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-edit btn-modern btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= $row['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?= $row['id'] ?>" 
                                           class="btn btn-delete btn-modern btn-sm" 
                                           onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                                No products found. Add your first product!
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_product" value="1">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" required placeholder="Product Name">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="category" class="form-control" required placeholder="Category">
                    </div>
                    <div class="mb-3">
                        <input type="number" step="0.01" name="price" class="form-control" required placeholder="Price (₱)">
                    </div>
                    <div class="mb-3">
                        <input type="number" name="stock" class="form-control" required placeholder="Stock Quantity">
                    </div>
                    <div class="mb-3">
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modals -->
<?php 
if ($result) {
    $result->data_seek(0); // Reset result pointer
    while ($row = $result->fetch_assoc()): 
?>
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="edit_product" value="1">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= htmlspecialchars($row['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" 
                               value="<?= htmlspecialchars($row['category']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" step="0.01" name="price" class="form-control" 
                               value="<?= $row['price'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" 
                               value="<?= $row['stock'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($row['image']): ?>
                            <div class="mt-2">
                                <small class="text-muted">Current image:</small><br>
                                <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" 
                                     width="100" class="rounded">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; } ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-hide alerts after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 3000);
    });
});

// Form validation
document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        const inputs = form.querySelectorAll('input[required]');
        let isValid = true;
        
        inputs.forEach(function(input) {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>