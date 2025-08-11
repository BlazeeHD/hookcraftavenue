<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Enhanced error handling and validation
function validateFile($file, $maxSize = 5242880, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error: ' . $file['error']];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Max size: 5MB'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only images allowed.'];
    }
    
    return ['success' => true];
}

// Function to get product name and price from the products table
function getProductInfo($conn, $productId) {
    $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    
    return ['name' => 'Product #' . $productId, 'price' => 0];
}

// Function to get category name
function getCategoryName($conn, $categoryId) {
    if (empty($categoryId)) return 'N/A';
    
    $categoryQuery = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    if ($categoryQuery) {
        $categoryQuery->bind_param("i", $categoryId);
        $categoryQuery->execute();
        $categoryResult = $categoryQuery->get_result();
        
        if ($categoryResult->num_rows > 0) {
            $categoryRow = $categoryResult->fetch_assoc();
            return $categoryRow['name'];
        }
    }
    
    return 'N/A';
}

// Get table columns for dynamic queries
function getTableColumns($conn, $tableName) {
    $result = $conn->query("SHOW COLUMNS FROM `$tableName`");
    $columns = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }
    return $columns;
}

// Check if tables exist
$tablesExist = [
    'users' => $conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0,
    'products' => $conn->query("SHOW TABLES LIKE 'products'")->num_rows > 0,
    'categories' => $conn->query("SHOW TABLES LIKE 'categories'")->num_rows > 0,
    'cart' => $conn->query("SHOW TABLES LIKE 'cart'")->num_rows > 0,
    'cart_item' => $conn->query("SHOW TABLES LIKE 'cart_item'")->num_rows > 0
];

// Get table structures if they exist
$userColumns = $tablesExist['users'] ? getTableColumns($conn, 'users') : [];
$productColumns = $tablesExist['products'] ? getTableColumns($conn, 'products') : [];
$categoryColumns = $tablesExist['categories'] ? getTableColumns($conn, 'categories') : [];

// Determine user name field
$userNameField = 'id'; // default fallback
if (in_array('name', $userColumns)) {
    $userNameField = 'name';
} elseif (in_array('username', $userColumns)) {
    $userNameField = 'username';
} elseif (in_array('full_name', $userColumns)) {
    $userNameField = 'full_name';
} elseif (in_array('first_name', $userColumns)) {
    $userNameField = 'first_name';
}

// Determine user email field
$userEmailField = null;
if (in_array('email', $userColumns)) {
    $userEmailField = 'email';
} elseif (in_array('email_address', $userColumns)) {
    $userEmailField = 'email_address';
}

$message = '';
$messageType = '';

// Add item to cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_to_cart'])) {
    $userId = $_POST['user_id'];
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $categoryId = $_POST['category_id'] ?? null;
    
    if (!$tablesExist['cart'] || !$tablesExist['cart_item']) {
        $message = 'Cart tables do not exist. Please create the required tables first.';
        $messageType = 'danger';
    } else {
        // Get product info for price calculation
        $productInfo = getProductInfo($conn, $productId);
        $price = $productInfo['price'];
        $subtotal = $price * $quantity;
        
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
        $checkStmt = $conn->prepare("SELECT id, quantity FROM cart_item WHERE cart_id=? AND product_id=?" . ($categoryId ? " AND category_id=?" : ""));
        if ($categoryId) {
            $checkStmt->bind_param("iii", $cartId, $productId, $categoryId);
        } else {
            $checkStmt->bind_param("ii", $cartId, $productId);
        }
        $checkStmt->execute();
        $existing = $checkStmt->get_result()->fetch_assoc();
        
        if ($existing) {
            // Update quantity and recalculate subtotal
            $newQuantity = $existing['quantity'] + $quantity;
            $newSubtotal = $price * $newQuantity;
            $updateStmt = $conn->prepare("UPDATE cart_item SET quantity=?, price=?, subtotal=? WHERE id=?");
            $updateStmt->bind_param("iddi", $newQuantity, $price, $newSubtotal, $existing['id']);
            $updateStmt->execute();
        } else {
            // Insert new item
            if ($categoryId) {
                $insertStmt = $conn->prepare("INSERT INTO cart_item (cart_id, product_id, category_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                $insertStmt->bind_param("iiiidi", $cartId, $productId, $categoryId, $quantity, $price, $subtotal);
            } else {
                $insertStmt = $conn->prepare("INSERT INTO cart_item (cart_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $insertStmt->bind_param("iiidd", $cartId, $productId, $quantity, $price, $subtotal);
            }
            $insertStmt->execute();
        }
        
        $message = 'Item added to cart successfully!';
        $messageType = 'success';
    }
}

// Update cart item quantity
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_quantity'])) {
    $cartItemId = $_POST['cart_item_id'];
    $newQuantity = $_POST['new_quantity'];
    
    if ($newQuantity > 0) {
        // Get current item info to recalculate subtotal
        $getItemStmt = $conn->prepare("SELECT product_id, price FROM cart_item WHERE id = ?");
        $getItemStmt->bind_param("i", $cartItemId);
        $getItemStmt->execute();
        $itemResult = $getItemStmt->get_result();
        
        if ($itemResult->num_rows > 0) {
            $itemData = $itemResult->fetch_assoc();
            $price = $itemData['price'];
            $newSubtotal = $price * $newQuantity;
            
            $stmt = $conn->prepare("UPDATE cart_item SET quantity=?, subtotal=? WHERE id=?");
            $stmt->bind_param("idi", $newQuantity, $newSubtotal, $cartItemId);
            $stmt->execute();
        }
    } else {
        // Remove item if quantity is 0
        $stmt = $conn->prepare("DELETE FROM cart_item WHERE id=?");
        $stmt->bind_param("i", $cartItemId);
        $stmt->execute();
    }
    
    $message = 'Cart updated successfully!';
    $messageType = 'success';
}

// Remove item from cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_item'])) {
    $cartItemId = $_POST['cart_item_id'];
    $stmt = $conn->prepare("DELETE FROM cart_item WHERE id=?");
    $stmt->bind_param("i", $cartItemId);
    $stmt->execute();
    
    $message = 'Item removed from cart!';
    $messageType = 'success';
}

// Clear entire cart for a user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['clear_cart'])) {
    $userId = $_POST['user_id'];
    // Delete all cart items for this user's carts
    $stmt = $conn->prepare("DELETE ci FROM cart_item ci INNER JOIN cart c ON ci.cart_id = c.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Optionally, also delete the empty cart records
    $deleteCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $deleteCartStmt->bind_param("i", $userId);
    $deleteCartStmt->execute();
    
    $message = 'Cart cleared successfully!';
    $messageType = 'success';
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
    .debug-info {
      background: #f8f9fa;
      padding: 10px;
      border-radius: 5px;
      font-size: 0.8em;
      color: #666;
    }
    .table-error {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="main-content p-4">
  <!-- Messages -->
  <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Check if required tables exist -->
  <?php if (!$tablesExist['cart'] || !$tablesExist['cart_item']): ?>
    <div class="table-error mb-4">
      <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
      <h4>Missing Cart Tables</h4>
      <p>The required cart tables (cart, cart_item) do not exist in your database.</p>
      <p>Please create these tables first before using the cart management system.</p>
      <small class="text-muted">
        Required tables: 
        <?php if (!$tablesExist['cart']): ?><code>cart</code> <?php endif; ?>
        <?php if (!$tablesExist['cart_item']): ?><code>cart_item</code> <?php endif; ?>
      </small>
    </div>
  <?php else: ?>

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
              <th>Category</th>
              <th>Price (₱)</th>
              <th>Quantity</th>
              <th>Subtotal (₱)</th>
              <th>Added Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
          // Build query for cart items - simplified to use the actual database structure
          $query = "
            SELECT 
              ci.id as cart_item_id,
              c.id as cart_id,
              c.user_id,
              ci.product_id,
              ci.category_id,
              ci.quantity,
              ci.price,
              ci.subtotal,
              c.created_at,
              p.name as product_name,
              p.price as product_price,
              cat.name as category_name";
          
          // Add user fields if users table exists
          if ($tablesExist['users'] && !empty($userColumns)) {
              $query .= ", u.{$userNameField} as customer_name";
              if ($userEmailField) {
                  $query .= ", u.{$userEmailField} as customer_email";
              }
          }
          
          $query .= "
            FROM cart c
            INNER JOIN cart_item ci ON c.id = ci.cart_id
            LEFT JOIN products p ON ci.product_id = p.id
            LEFT JOIN categories cat ON ci.category_id = cat.id";
          
          // Join users table if it exists
          if ($tablesExist['users']) {
              $query .= " LEFT JOIN users u ON c.user_id = u.id";
          }
          
          $query .= " WHERE 1=1";
          
          $params = [];
          $types = '';
          
          if (!empty($search) && $tablesExist['users']) {
            $query .= " AND u.{$userNameField} LIKE ?";
            $params[] = "%$search%";
            $types .= 's';
          }
          
          if ($statusFilter == 'active') {
            $query .= " AND ci.quantity > 0";
          }
          
          $query .= " ORDER BY u.{$userNameField}, c.created_at DESC";
          
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
            $customerName = isset($row['customer_name']) ? htmlspecialchars($row['customer_name']) : 'User #' . $userId;
            
            // Use product info from the joined query, with fallbacks
            $productName = !empty($row['product_name']) ? htmlspecialchars($row['product_name']) : 'Product #' . $row['product_id'];
            $productPrice = !empty($row['price']) ? $row['price'] : (!empty($row['product_price']) ? $row['product_price'] : 0);
            $categoryName = !empty($row['category_name']) ? htmlspecialchars($row['category_name']) : 'N/A';
            
            // Calculate subtotal if not stored or if price changed
            $storedSubtotal = $row['subtotal'];
            $calculatedSubtotal = $productPrice * $row['quantity'];
            $subtotal = !empty($storedSubtotal) ? $storedSubtotal : $calculatedSubtotal;
            
            // Update subtotal in database if it's different (price may have changed)
            if (abs($storedSubtotal - $calculatedSubtotal) > 0.01) {
                $updateSubtotalStmt = $conn->prepare("UPDATE cart_item SET price=?, subtotal=? WHERE id=?");
                $updateSubtotalStmt->bind_param("ddi", $productPrice, $calculatedSubtotal, $cartItemId);
                $updateSubtotalStmt->execute();
                $subtotal = $calculatedSubtotal;
            }
            
            // Group by customer
            if ($currentUser != $userId && $currentUser != '') {
              // Show customer total row
              echo "<tr class='table-info fw-bold'>
                      <td colspan='5'>Customer Total</td>
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
              echo "<tr><td colspan='8'><hr></td></tr>";
              $customerTotal = 0;
              $customerItemCount = 0;
            }
            
            if ($currentUser != $userId) {
              $currentUser = $userId;
            }
            
            $customerTotal += $subtotal;
            $customerItemCount += $row['quantity'];
            
            echo "<tr class='cart-item-row'>";
            echo "<td>
                    <strong>$customerName</strong><br>";
            if (isset($row['customer_email']) && $row['customer_email']) {
              echo "<small class='text-muted'>" . htmlspecialchars($row['customer_email']) . "</small>";
            }
            echo "</td>";
            echo "<td>" . $productName . "</td>";
            echo "<td><span class='badge bg-secondary'>" . $categoryName . "</span></td>";
            echo "<td>₱" . number_format($productPrice, 2) . "</td>";
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
            echo "<td class='fw-bold text-primary'>₱" . number_format($subtotal, 2) . "</td>";
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
                    <td colspan='5'>Customer Total</td>
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
            echo "<tr><td colspan='8' class='text-center text-muted py-4'>
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

  <?php endif; // End check for required tables ?>
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
          <?php if ($tablesExist['users']): ?>
          <div class="mb-3">
            <label class="form-label">Customer</label>
            <select name="user_id" class="form-select" required>
              <option value="">Select Customer</option>
              <?php
              $customerQuery = $conn->query("SELECT id, {$userNameField}" . ($userEmailField ? ", {$userEmailField}" : '') . " FROM users ORDER BY {$userNameField}");
              while ($customer = $customerQuery->fetch_assoc()) {
                $displayName = htmlspecialchars($customer[$userNameField]);
                if ($userEmailField && !empty($customer[$userEmailField])) {
                  $displayName .= " (" . htmlspecialchars($customer[$userEmailField]) . ")";
                }
                echo "<option value='" . $customer['id'] . "'>" . $displayName . "</option>";
              }
              ?>
            </select>
          </div>
          <?php else: ?>
          <div class="mb-3">
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle me-2"></i>
              Users table not found. Please create a users table first.
            </div>
            <input type="hidden" name="user_id" value="1">
          </div>
          <?php endif; ?>

          <?php if ($tablesExist['categories']): ?>
          <div class="mb-3">
            <label class="form-label">Category (Optional)</label>
            <select name="category_id" class="form-select">
              <option value="">No specific category</option>
              <?php
              $categoryQuery = $conn->query("SELECT id, name FROM categories ORDER BY name");
              while ($category = $categoryQuery->fetch_assoc()) {
                echo "<option value='" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
              }
              ?>
            </select>
          </div>
          <?php endif; ?>
          
          <div class="mb-3">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select" required>
              <option value="">Select Product</option>
              <?php
              // Show products from the products table
              if ($tablesExist['products']) {
                $productQuery = $conn->query("SELECT p.id, p.name, p.price, c.name as category_name 
                                            FROM products p 
                                            LEFT JOIN categories c ON p.category_id = c.id 
                                            ORDER BY p.name");
                if ($productQuery) {
                  while ($product = $productQuery->fetch_assoc()) {
                    $displayName = htmlspecialchars($product['name']) . " - ₱" . number_format($product['price'], 2);
                    if ($product['category_name']) {
                        $displayName .= " (" . htmlspecialchars($product['category_name']) . ")";
                    }
                    echo "<option value='" . $product['id'] . "'>" . $displayName . "</option>";
                  }
                }
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
<script>
// Form validation and user feedback
document.addEventListener('submit', function(e) {
    if (e.target.matches('form[method="POST"]')) {
        const submitBtn = e.target.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            
            // Re-enable after 10 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 10000);
        }
    }
});

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>

<?php $conn->close(); ?>
</body>
</html>