<?php
include __DIR__ . '/sidebar.php';
include __DIR__ . '/header.php';
include __DIR__ . '/../includes/db.php';
session_start();

// Handle product deletion
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $_SESSION['message'] = "Product deleted successfully.";
  header("Location: product.php");
  exit();
}

// Handle product addition
if (isset($_POST['add_product'])) {
  $name = $_POST['name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $image = $_POST['image'];
  $stock = $_POST['stock'];
  $stmt = $conn->prepare("INSERT INTO products (name, category, price, image, stock) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("ssdsi", $name, $category, $price, $image, $stock);
  $stmt->execute();
  $_SESSION['message'] = "Product added successfully.";
  header("Location: product.php");
  exit();
}

// Handle product update
if (isset($_POST['update_product'])) {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $image = $_POST['image'];
  $stock = $_POST['stock'];
  $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, image=?, stock=? WHERE id=?");
  $stmt->bind_param("ssdsii", $name, $category, $price, $image, $stock, $id);
  $stmt->execute();
  $_SESSION['message'] = "Product updated successfully.";
  header("Location: product.php");
  exit();
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../asset/css/admin.css">
  <style>
    .product-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
    .table-danger { background-color: #f8d7da !important; }
    .form-section { background: #f1f1f1; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
  </style>
</head>
<body>

<main class="main-content">
  <div class="container-fluid py-4">
    <h4 class="mb-4">Product Management</h4>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
          echo $_SESSION['message']; 
          unset($_SESSION['message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form method="get" class="mb-4">
      <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search product name..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">Search</button>
      </div>
    </form>

    <div class="form-section">
      <form method="post">
        <h5>Add New Product</h5>
        <div class="row g-2">
          <div class="col-md-3">
            <input type="text" name="name" class="form-control" placeholder="Product Name" required>
          </div>
          <div class="col-md-2">
            <input type="text" name="category" class="form-control" placeholder="Category" required>
          </div>
          <div class="col-md-2">
            <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
          </div>
          <div class="col-md-3">
            <input type="text" name="image" class="form-control" placeholder="Image path (../asset/images/...)" required>
          </div>
          <div class="col-md-1">
            <input type="number" name="stock" class="form-control" placeholder="Stock" required>
          </div>
          <div class="col-md-1">
            <button type="submit" name="add_product" class="btn btn-success w-100">Add</button>
          </div>
        </div>
      </form>
    </div>

    <table class="table table-bordered table-hover">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Image</th>
          <th>Stock</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr class="text-center <?php echo ($row['stock'] <= 0) ? 'table-danger' : ''; ?>">
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
            <td>₱<?php echo number_format($row['price'], 2); ?></td>
            <td><img src="<?php echo htmlspecialchars($row['image']); ?>" class="product-img"></td>
            <td><?php echo $row['stock']; ?></td>
            <td>
              <form method="post" class="d-flex gap-1">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="update_product" value="1">
                <button class="btn btn-sm btn-primary" onclick="fillForm('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['category']); ?>', '<?php echo $row['price']; ?>', '<?php echo htmlspecialchars($row['image']); ?>', '<?php echo $row['stock']; ?>'); return false;">Edit</button>
                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</main>

<script>
  function fillForm(id, name, category, price, image, stock) {
    const form = document.querySelector('form[method="post"]');
    form.innerHTML = `
      <h5>Update Product</h5>
      <input type="hidden" name="id" value="${id}">
      <div class="row g-2">
        <div class="col-md-3">
          <input type="text" name="name" class="form-control" value="${name}" required>
        </div>
        <div class="col-md-2">
          <input type="text" name="category" class="form-control" value="${category}" required>
        </div>
        <div class="col-md-2">
          <input type="number" step="0.01" name="price" class="form-control" value="${price}" required>
        </div>
        <div class="col-md-3">
          <input type="text" name="image" class="form-control" value="${image}" required>
        </div>
        <div class="col-md-1">
          <input type="number" name="stock" class="form-control" value="${stock}" required>
        </div>
        <div class="col-md-1">
          <button type="submit" name="update_product" class="btn btn-warning w-100">Update</button>
        </div>
      </div>`;
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
