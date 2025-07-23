<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Add item to cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_to_cart'])) {
  $userId = $_POST['user_id']; // Changed from customer_id to user_id
  $productId = $_POST['product_id'];
  $quantity = $_POST['quantity'];
  
  // First, get or create a cart for this user
  $cartStmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? LIMIT 1");
  $cartStmt->bind_param("i", $userId);
  $cartStmt->execute();
  $cartResult = $cartStmt->get_result();
  
  if ($cartResult->num_rows > 0) {
    $cartId = $cartResult->fetch_assoc()['id'];
  } else {
    // Create new cart
    $createCartStmt = $conn->prepare("INSERT INTO cart (user_id, created_at) VALUES (?, NOW())");
    $createCartStmt->bind_param("i", $userId);
    $createCartStmt->execute();
    $cartId = $conn->insert_id;
  }
  
  // Check if item already exists in cart
  $checkStmt = $conn->prepare("SELECT id, quantity FROM cart_item WHERE cart_id=? AND product_id=?");
  $checkStmt->bind_param("ii", $cartId, $productId);
  $checkStmt->execute();
  $existing = $checkStmt->get_result()->fetch_assoc();
  
  if ($existing) {
    // Update quantity
    $newQuantity = $existing['quantity'] + $quantity;
    $updateStmt = $conn->prepare("UPDATE cart_item SET quantity=? WHERE id=?");
    $updateStmt->bind_param("ii", $newQuantity, $existing['id']);
    $updateStmt->execute();
  } else {
    // Insert new item
    $insertStmt = $conn->prepare("INSERT INTO cart_item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
    $insertStmt->bind_param("iii", $cartId, $productId, $quantity);
    $insertStmt->execute();
  }
}

// Update cart item quantity
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_quantity'])) {
  $cartItemId = $_POST['cart_item_id'];
  $newQuantity = $_POST['new_quantity'];
  
  if ($newQuantity > 0) {
    $stmt = $conn->prepare("UPDATE cart_item SET quantity=? WHERE id=?");
    $stmt->bind_param("ii", $newQuantity, $cartItemId);
    $stmt->execute();
  } else {
    // Remove item if quantity is 0
    $stmt = $conn->prepare("DELETE FROM cart_item WHERE id=?");
    $stmt->bind_param("i", $cartItemId);
    $stmt->execute();
  }
}

// Remove item from cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_item'])) {
  $cartItemId = $_POST['cart_item_id'];
  $stmt = $conn->prepare("DELETE FROM cart_item WHERE id=?");
  $stmt->bind_param("i", $cartItemId);
  $stmt->execute();
}

// Clear entire cart for a user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['clear_cart'])) {
  $userId = $_POST['user_id']; // Changed from customer_id to user_id
  // Delete all cart items for this user's carts
  $stmt = $conn->prepare("DELETE ci FROM cart_item ci INNER JOIN cart c ON ci.cart_id = c.id WHERE c.user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  
  // Optionally, also delete the empty cart records
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
  <style>
    .cart-summary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    .cart-item-row {
      border-left: 4px solid #007bff;
    }
    .empty-cart-row {
      border-left: 4px solid #6c757d;
    }
    .customer-group {
      background: #f8f9fa;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    .quantity-input {
      width: 80px;
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="main-content p-4">
  <!-- Cart Statistics -->
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="cart-summary">
        <div class="row text-center">
          <?php
          // Get cart statistics with error handling
          $totalCarts = 0;
          $totalItems = 0;
          $avgItems = 0;

          try {
              // Get total active carts
              $totalCartsQuery = $conn->query("SELECT COUNT(DISTINCT c.id) as total_carts FROM cart c INNER JOIN cart_item ci ON c.id = ci.cart_id");
              if ($totalCartsQuery) {
                  $totalCarts = $totalCartsQuery->fetch_assoc()['total_carts'];
              }

              // Get total items across all carts
              $totalItemsQuery = $conn->query("SELECT COALESCE(SUM(ci.quantity), 0) as total_items FROM cart_item ci");
              if ($totalItemsQuery) {
                  $totalItems = $totalItemsQuery->fetch_assoc()['total_items'];
              }

              // Calculate average items per cart
              if ($totalCarts > 0) {
                  $avgItems = round($totalItems / $totalCarts, 1);
              }

          } catch (Exception $e) {
              error_log("Cart statistics error: " . $e->getMessage());
          }
          ?>
          <div class="col-md-4">
            <h3><i class="fas fa-shopping-cart me-2"></i><?= $totalCarts ?></h3>
            <p class="mb-0">Active Carts</p>
          </div>
          <div class="col-md-4">
            <h3><i class="fas fa-boxes me-2"></i><?= $totalItems ?></h3>
            <p class="mb-0">Total Items</p>
          </div>
          <div class="col-md-4">
            <h3><i class="fas fa-chart-line me-2"></i><?= $avgItems ?></h3>
            <p class="mb-0">Avg Items/Cart</p>
          </div>
        </div>
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
      <!-- Search and Filter -->
      <form method="get" class="mb-4">
        <div class="row g-2 align-items-end">
          <div class="col-md-6">
            <label class="form-label fw-bold mb-1">Search Customer</label>
            <input type="text" name="search" class="form-control" placeholder="Enter customer name..." value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold mb-1">Cart Status</label>
            <select name="status" class="form-select">
              <option value="">All Carts</option>
              <option value="active" <?= $statusFilter == 'active' ? 'selected' : '' ?>>Active Carts</option>
              <option value="empty" <?= $statusFilter == 'empty' ? 'selected' : '' ?>>Recently Cleared</option>
            </select>
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-light w-100"><i class="fas fa-search"></i> Filter</button>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
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
              <th>Added Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
          // Build query based on filters - corrected for actual schema
          $query = "
            SELECT 
              ci.id as cart_item_id,
              c.id as cart_id,
              c.user_id,
              ci.product_id,
              ci.quantity,
              c.created_at,
              u.name as customer_name,
              u.email as customer_email,
              p.name as product_name,
              p.price as product_price,
              (ci.quantity * p.price) as subtotal
            FROM cart c
            INNER JOIN cart_item ci ON c.id = ci.cart_id
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN products p ON ci.product_id = p.id
            WHERE 1=1
          ";
          
          $params = [];
          $types = '';
          
          if (!empty($search)) {
            $query .= " AND u.name LIKE ?";
            $params[] = "%$search%";
            $types .= 's';
          }
          
          if ($statusFilter == 'active') {
            $query .= " AND ci.quantity > 0";
          }
          
          $query .= " ORDER BY u.name, c.created_at DESC";
          
          $stmt = $conn->prepare($query);
          if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
          }
          $stmt->execute();
          $result = $stmt->get_result();
          
          $currentUser = '';
          $customerTotal = 0;
          $customerItemCount = 0;
          
          while ($row = $result->fetch_assoc()) {
            $cartItemId = $row['cart_item_id'];
            $userId = $row['user_id'];
            $customerName = htmlspecialchars($row['customer_name']);
            
            // Group by customer
            if ($currentUser != $userId && $currentUser != '') {
              // Show customer total row
              echo "<tr class='table-info fw-bold'>
                      <td colspan='4'>Customer Total</td>
                      <td>₱" . number_format($customerTotal, 2) . "</td>
                      <td>$customerItemCount items</td>
                      <td>
                        <form method='POST' class='d-inline' onsubmit=\"return confirm('Clear entire cart for this customer?');\">
                          <input type='hidden' name='user_id' value='$currentUser'>
                          <button type='submit' name='clear_cart' class='btn btn-outline-danger btn-sm'>
                            <i class='fas fa-trash'></i> Clear Cart
                          </button>
                        </form>
                      </td>
                    </tr>";
              echo "<tr><td colspan='7'><hr></td></tr>";
              $customerTotal = 0;
              $customerItemCount = 0;
            }
            
            if ($currentUser != $userId) {
              $currentUser = $userId;
            }
            
            $customerTotal += $row['subtotal'];
            $customerItemCount += $row['quantity'];
            
            echo "<tr class='cart-item-row'>";
            echo "<td>
                    <strong>$customerName</strong><br>
                    <small class='text-muted'>" . htmlspecialchars($row['customer_email']) . "</small>
                  </td>";
            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
            echo "<td>₱" . number_format($row['product_price'], 2) . "</td>";
            echo "<td>
                    <form method='POST' class='d-flex align-items-center gap-2'>
                      <input type='hidden' name='cart_item_id' value='$cartItemId'>
                      <input type='number' name='new_quantity' value='" . $row['quantity'] . "' 
                             class='form-control quantity-input' min='0' max='999'>
                      <button type='submit' name='update_quantity' class='btn btn-sm btn-outline-primary'>
                        <i class='fas fa-sync'></i>
                      </button>
                    </form>
                  </td>";
            echo "<td class='fw-bold text-primary'>₱" . number_format($row['subtotal'], 2) . "</td>";
            echo "<td>" . date("M j, Y", strtotime($row['created_at'])) . "</td>";
            echo "<td>
                    <form method='POST' class='d-inline' onsubmit=\"return confirm('Remove this item from cart?');\">
                      <input type='hidden' name='cart_item_id' value='$cartItemId'>
                      <button type='submit' name='remove_item' class='btn btn-sm btn-outline-danger'>
                        <i class='fas fa-times'></i> Remove
                      </button>
                    </form>
                  </td>";
            echo "</tr>";
          }
          
          // Show final customer total if there were results
          if ($currentUser != '') {
            echo "<tr class='table-info fw-bold'>
                    <td colspan='4'>Customer Total</td>
                    <td>₱" . number_format($customerTotal, 2) . "</td>
                    <td>$customerItemCount items</td>
                    <td>
                      <form method='POST' class='d-inline' onsubmit=\"return confirm('Clear entire cart for this customer?');\">
                        <input type='hidden' name='user_id' value='$currentUser'>
                        <button type='submit' name='clear_cart' class='btn btn-outline-danger btn-sm'>
                          <i class='fas fa-trash'></i> Clear Cart
                        </button>
                      </form>
                    </td>
                  </tr>";
          }
          
          // Show message if no results
          if ($result->num_rows == 0) {
            echo "<tr><td colspan='7' class='text-center text-muted py-4'>
                    <i class='fas fa-shopping-cart fa-3x mb-3'></i><br>
                    No cart items found matching your criteria.
                  </td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Item to Cart Modal -->
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
              // Removed role filter since users table doesn't have role field
              $customerQuery = $conn->query("SELECT id, name, email FROM users ORDER BY name");
              while ($customer = $customerQuery->fetch_assoc()) {
                echo "<option value='" . $customer['id'] . "'>" . 
                     htmlspecialchars($customer['name']) . " (" . htmlspecialchars($customer['email']) . ")</option>";
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select" required>
              <option value="">Select Product</option>
              <?php
              // Removed status filter since products table doesn't have status field
              $productQuery = $conn->query("SELECT id, name, price FROM products ORDER BY name");
              while ($product = $productQuery->fetch_assoc()) {
                echo "<option value='" . $product['id'] . "'>" . 
                     htmlspecialchars($product['name']) . " - ₱" . number_format($product['price'], 2) . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" max="999" value="1" required>
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
<?php $conn->close(); ?>
</body>
</html>