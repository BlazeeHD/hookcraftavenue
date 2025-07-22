<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
  $name = $_POST['name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];
  $imageName = $_FILES['image']['name'];
  $imagePath = __DIR__ . '/../uploads/' . $imageName;

  if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
    $stmt = $conn->prepare("INSERT INTO products (name, category, price, stock, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $name, $category, $price, $stock, $imageName);
    $stmt->execute();
  }
}

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
  $id = $_POST['product_id'];
  $name = $_POST['name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];
  $imageSql = "";
  $params = [];

  if (!empty($_FILES['image']['name'])) {
    $imageName = $_FILES['image']['name'];
    $imagePath = __DIR__ . '/../uploads/' . $imageName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
      $imageSql = ", image=?";
    }
  }

  $query = "UPDATE products SET name=?, category=?, price=?, stock=? $imageSql WHERE id=?";
  if ($imageSql) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdisi", $name, $category, $price, $stock, $imageName, $id);
  } else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdis", $name, $category, $price, $stock, $id);
  }
  $stmt->execute();
}

// Handle Delete Product
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
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
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Product List</h5>
      <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Product</button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Category</th>
              <th>Price (₱)</th>
              <th>Stock</th>
              <th>Image</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
          while ($row = $result->fetch_assoc()):
            $id = $row['id'];
            $editModalId = "editModal$id";
          ?>
            <tr>
              <td><?= $id ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['category']) ?></td>
              <td>₱<?= number_format($row['price'], 2) ?></td>
              <td><?= $row['stock'] ?></td>
              <td>
                <?php if ($row['image']): ?>
                  <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" width="50" class="rounded">
                <?php else: ?>
                  <span class="text-muted">No Image</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex flex-column gap-2">
                  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#<?= $editModalId ?>"><i class="fas fa-edit"></i> Edit</button>
                  <a href="?delete=<?= $id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?');"><i class="fas fa-trash"></i> Delete</a>
                </div>
              </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="<?= $editModalId ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $id ?>" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= $id ?>">
                    <input type="hidden" name="edit_product" value="1">
                    <div class="modal-header bg-warning text-dark">
                      <h5 class="modal-title" id="editModalLabel<?= $id ?>">Edit Product</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" placeholder="Product Name" required>
                      </div>
                      <div class="mb-3">
                        <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($row['category']) ?>" placeholder="Category" required>
                      </div>
                      <div class="mb-3">
                        <input type="number" step="0.01" name="price" class="form-control" value="<?= $row['price'] ?>" placeholder="Price" required>
                      </div>
                      <div class="mb-3">
                        <input type="number" name="stock" class="form-control" value="<?= $row['stock'] ?>" placeholder="Stock" required>
                      </div>
                      <div class="mb-3">
                        <input type="file" name="image" class="form-control" accept="image/*" placeholder="Image (optional)">
                        <?php if ($row['image']): ?>
                          <div class="mt-2">
                            <small>Current Image:</small><br>
                            <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" width="70" class="rounded">
                          </div>
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
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_product" value="1">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="addModalLabel">Add Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <input type="text" name="name" class="form-control" placeholder="Product Name" required>
          </div>
          <div class="mb-3">
            <input type="text" name="category" class="form-control" placeholder="Category" required>
          </div>
          <div class="mb-3">
            <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
          </div>
          <div class="mb-3">
            <input type="number" name="stock" class="form-control" placeholder="Stock" required>
          </div>
          <div class="mb-3">
            <input type="file" name="image" class="form-control" accept="image/*" placeholder="Image" required>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
