<?php
include __DIR__ . '/../includes/db.php';
session_start();

/**
 * Add item to cart
 */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_to_cart'])) {
  $userId = intval($_POST['user_id']);
  $productId = intval($_POST['product_id']);
  $categoryId = intval($_POST['category_id']);
  $quantity = intval($_POST['quantity']);

  // Get or create a cart for this user
  $cartStmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? LIMIT 1");
  $cartStmt->bind_param("i", $userId);
  $cartStmt->execute();
  $cartResult = $cartStmt->get_result();

  if ($cartResult->num_rows > 0) {
    $cartId = $cartResult->fetch_assoc()['id'];
  } else {
    $createCartStmt = $conn->prepare("INSERT INTO cart (user_id, created_at) VALUES (?, NOW())");
    $createCartStmt->bind_param("i", $userId);
    $createCartStmt->execute();
    $cartId = $conn->insert_id;
  }

  // Check if item already exists in cart
  $checkStmt = $conn->prepare("SELECT id, quantity FROM cart_item WHERE cart_id=? AND product_id=? AND category_id=?");
  $checkStmt->bind_param("iii", $cartId, $productId, $categoryId);
  $checkStmt->execute();
  $existing = $checkStmt->get_result()->fetch_assoc();

  if ($existing) {
    $newQuantity = $existing['quantity'] + $quantity;
    $updateStmt = $conn->prepare("UPDATE cart_item SET quantity=? WHERE id=?");
    $updateStmt->bind_param("ii", $newQuantity, $existing['id']);
    $updateStmt->execute();
  } else {
    $insertStmt = $conn->prepare("INSERT INTO cart_item (cart_id, product_id, category_id, quantity) VALUES (?, ?, ?, ?)");
    $insertStmt->bind_param("iiii", $cartId, $productId, $categoryId, $quantity);
    $insertStmt->execute();
  }
}

/**
 * Update cart item quantity
 */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_quantity'])) {
  $cartItemId = intval($_POST['cart_item_id']);
  $newQuantity = intval($_POST['new_quantity']);

  if ($newQuantity > 0) {
    $stmt = $conn->prepare("UPDATE cart_item SET quantity=? WHERE id=?");
    $stmt->bind_param("ii", $newQuantity, $cartItemId);
    $stmt->execute();
  } else {
    $stmt = $conn->prepare("DELETE FROM cart_item WHERE id=?");
    $stmt->bind_param("i", $cartItemId);
    $stmt->execute();
  }
}

/**
 * Remove item from cart
 */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_item'])) {
  $cartItemId = intval($_POST['cart_item_id']);
  $stmt = $conn->prepare("DELETE FROM cart_item WHERE id=?");
  $stmt->bind_param("i", $cartItemId);
  $stmt->execute();
}

/**
 * Clear entire cart for a user
 */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['clear_cart'])) {
  $userId = intval($_POST['user_id']);
  $stmt = $conn->prepare("DELETE ci FROM cart_item ci INNER JOIN cart c ON ci.cart_id = c.id WHERE c.user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();

  $deleteCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
  $deleteCartStmt->bind_param("i", $userId);
  $deleteCartStmt->execute();
}

$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shopping Carts - Hookcraft Avenue</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../asset/dashboard.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="main-content p-4">

<!-- Cart Statistics -->
<div class="row mb-4">
  <div class="col-md-12">
    <div class="p-3 bg-primary text-white rounded">
      <?php
      $totalCarts = $conn->query("SELECT COUNT(DISTINCT c.id) as total_carts FROM cart c INNER JOIN cart_item ci ON c.id = ci.cart_id")->fetch_assoc()['total_carts'] ?? 0;
      $totalItems = $conn->query("SELECT COALESCE(SUM(quantity), 0) as total_items FROM cart_item")->fetch_assoc()['total_items'] ?? 0;
      $avgItems = $totalCarts > 0 ? round($totalItems / $totalCarts, 1) : 0;
      ?>
      <strong>Active Carts:</strong> <?= $totalCarts ?> |
      <strong>Total Items:</strong> <?= $totalItems ?> |
      <strong>Avg Items/Cart:</strong> <?= $avgItems ?>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Shopping Cart Management</h5>
    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addItemModal">
      <i class="fas fa-plus"></i> Add Item to Cart
    </button>
  </div>
  <div class="card-body">

    <!-- Search & Filter -->
    <form method="get" class="mb-4">
      <div class="row g-2 align-items-end">
        <div class="col-md-6">
          <label class="form-label fw-bold mb-1">Search Customer</label>
          <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold mb-1">Cart Status</label>
          <select name="status" class="form-select">
            <option value="">All Carts</option>
            <option value="active" <?= $statusFilter == 'active' ? 'selected' : '' ?>>Active Carts</option>
            <option value="empty" <?= $statusFilter == 'empty' ? 'selected' : '' ?>>Recently Cleared</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-light w-100"><i class="fas fa-search"></i> Filter</button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Customer</th>
            <th>Product</th>
            <th>Price (₱)</th>
            <th>Quantity</th>
            <th>Subtotal (₱)</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $query = "
        SELECT ci.id as cart_item_id, c.user_id, u.name as customer_name, u.email as customer_email,
               ci.quantity, ci.category_id, ci.product_id, cat.name as category_name,
               CASE ci.category_id
                   WHEN 1 THEN sp.name
                   WHEN 2 THEN fp.name
                   WHEN 3 THEN cp.name
               END AS product_name,
               CASE ci.category_id
                   WHEN 1 THEN sp.price
                   WHEN 2 THEN fp.price
                   WHEN 3 THEN cp.price
               END AS product_price
        FROM cart_item ci
        INNER JOIN cart c ON ci.cart_id = c.id
        LEFT JOIN users u ON c.user_id = u.id
        LEFT JOIN categories cat ON ci.category_id = cat.id
        LEFT JOIN satin_products sp ON (ci.category_id = 1 AND ci.product_id = sp.satin_id)
        LEFT JOIN fizzywire_products fp ON (ci.category_id = 2 AND ci.product_id = fp.fizzywire_id)
        LEFT JOIN customize_products cp ON (ci.category_id = 3 AND ci.product_id = cp.customize_id)
        WHERE 1=1
        ";

        if (!empty($search)) {
          $query .= " AND u.name LIKE '%" . $conn->real_escape_string($search) . "%'";
        }
        if ($statusFilter == 'active') {
          $query .= " AND ci.quantity > 0";
        }
        $query .= " ORDER BY u.name";

        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $subtotal = $row['product_price'] * $row['quantity'];
            echo "<tr>
              <td><strong>{$row['customer_name']}</strong><br><small>{$row['customer_email']}</small></td>
              <td>[{$row['category_name']}] {$row['product_name']}</td>
              <td>₱" . number_format($row['product_price'], 2) . "</td>
              <td>
                <form method='POST' class='d-flex'>
                  <input type='hidden' name='cart_item_id' value='{$row['cart_item_id']}'>
                  <input type='number' name='new_quantity' value='{$row['quantity']}' class='form-control me-2' style='width:80px;'>
                  <button name='update_quantity' class='btn btn-sm btn-outline-primary'>Update</button>
                </form>
              </td>
              <td>₱" . number_format($subtotal, 2) . "</td>
              <td>
                <form method='POST'>
                  <input type='hidden' name='cart_item_id' value='{$row['cart_item_id']}'>
                  <button name='remove_item' class='btn btn-sm btn-outline-danger'>Remove</button>
                </form>
              </td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='6' class='text-center'>No items found</td></tr>";
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add Item to Cart</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Customer</label>
            <select name="user_id" class="form-select" required>
              <option value="">Select Customer</option>
              <?php
              $customerQuery = $conn->query("SELECT id, name, email FROM users ORDER BY name");
              while ($customer = $customerQuery->fetch_assoc()) {
                echo "<option value='{$customer['id']}'>" . htmlspecialchars($customer['name']) . " ({$customer['email']})</option>";
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select" required>
              <option value="">Select Product</option>
              <?php
              $catQuery = $conn->query("SELECT * FROM categories ORDER BY name");
              while ($cat = $catQuery->fetch_assoc()) {
                $tableName = strtolower($cat['name']) . "_products";
                $idField = rtrim(strtolower($cat['name']), 's') . "_id";
                $prodQuery = $conn->query("SELECT * FROM `$tableName` ORDER BY name");
                while ($prod = $prodQuery->fetch_assoc()) {
                  echo "<option value='{$prod[$idField]}' data-category='{$cat['id']}'>[{$cat['name']}] {$prod['name']} - ₱" . number_format($prod['price'], 2) . "</option>";
                }
              }
              ?>
            </select>
          </div>
          <input type="hidden" name="category_id" id="category_id">
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_to_cart" class="btn btn-primary">
            <i class="fas fa-cart-plus"></i> Add to Cart
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelector('select[name="product_id"]').addEventListener('change', function(){
  const selected = this.options[this.selectedIndex];
  document.getElementById('category_id').value = selected.getAttribute('data-category');
});
</script>

<?php $conn->close(); ?>
</body>
</html>
