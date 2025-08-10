<?php
include '../includes/db.php';
session_start();

// Ensure session cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle ADD TO CART action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $category_id = (int)$_POST['category_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    // Debug logging
    error_log("Add to cart - Product ID: $product_id, Category ID: $category_id, Quantity: $quantity");
    
    if ($product_id > 0 && $category_id > 0 && $quantity > 0) {
        // Verify product exists before adding
        $product = getProductDetails($conn, $product_id, $category_id);
        if ($product) {
            // Create a unique key for cart items (category_id + product_id)
            $cart_key = $category_id . '_' . $product_id;
            
            // Check if item already exists in cart
            if (isset($_SESSION['cart'][$cart_key]) && is_array($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
                error_log("Updated existing cart item: $cart_key");
            } else {
                $_SESSION['cart'][$cart_key] = [
                    'product_id' => $product_id,
                    'category_id' => $category_id,
                    'quantity' => $quantity
                ];
                error_log("Added new cart item: $cart_key");
            }
            
            $_SESSION['success'] = "Product '{$product['name']}' added to cart successfully!";
            error_log("Cart after adding: " . print_r($_SESSION['cart'], true));
        } else {
            $_SESSION['error'] = "Product not found.";
            error_log("Product not found: Product ID $product_id, Category ID $category_id");
        }
    } else {
        $_SESSION['error'] = "Invalid product or quantity.";
        error_log("Invalid data: Product ID $product_id, Category ID $category_id, Quantity $quantity");
    }
    
    // If AJAX request, return JSON
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => isset($_SESSION['success']),
            'message' => $_SESSION['success'] ?? $_SESSION['error'] ?? 'Unknown error'
        ]);
        exit;
    }
    
    // Redirect to prevent form resubmission
    header('Location: cart.php');
    exit;
}

// Function to get product details from unified products table
function getProductDetails($conn, $product_id, $category_id) {
    try {
        $productQuery = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.category_id = ?");
        if (!$productQuery) {
            error_log("Failed to prepare product query");
            return null;
        }
        
        $productQuery->bind_param("ii", $product_id, $category_id);
        $productQuery->execute();
        $productResult = $productQuery->get_result();
        
        if ($product = $productResult->fetch_assoc()) {
            error_log("Found product: " . print_r($product, true));
            return $product;
        } else {
            error_log("No product found: Product ID $product_id, Category ID $category_id");
        }
    } catch (Exception $e) {
        error_log("Error in getProductDetails: " . $e->getMessage());
    }
    return null;
}

// Function to clean up cart - remove items that no longer exist
function cleanupCart($conn) {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return [];
    }
    
    $removed_items = [];
    $valid_cart = [];
    
    foreach ($_SESSION['cart'] as $cart_key => $item) {
        if (!is_array($item) || !isset($item['product_id'], $item['category_id'], $item['quantity'])) {
            $removed_items[] = "Invalid cart item structure";
            continue;
        }
        
        $product = getProductDetails($conn, $item['product_id'], $item['category_id']);
        if ($product) {
            $valid_cart[$cart_key] = $item;
        } else {
            $removed_items[] = "Product ID {$item['product_id']} not found and removed from cart.";
        }
    }
    
    $_SESSION['cart'] = $valid_cart;
    return $removed_items;
}

// Clean up cart first
$removed_items = cleanupCart($conn);
if (!empty($removed_items)) {
    $_SESSION['cart_updates'] = $removed_items;
}

// Handle REMOVE action
if (isset($_POST['remove_id'])) {
    $remove_key = $_POST['remove_id'];
    if (isset($_SESSION['cart'][$remove_key])) {
        unset($_SESSION['cart'][$remove_key]);
        $_SESSION['success'] = "Item removed from cart.";
    }
}

// Handle UPDATE action
if (isset($_POST['update_id']) && isset($_POST['update_qty'])) {
    $update_key = $_POST['update_id'];
    $qty = (int)$_POST['update_qty'];

    if (isset($_SESSION['cart'][$update_key])) {
        if ($qty > 0) {
            $_SESSION['cart'][$update_key]['quantity'] = $qty;
            $_SESSION['success'] = "Cart updated successfully.";
        } else {
            unset($_SESSION['cart'][$update_key]);
            $_SESSION['success'] = "Item removed from cart.";
        }
    }
}

// Sync to DB if logged in
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    error_log("Syncing cart to database for user: $user_id");

    try {
        // Check if a cart exists for the user
        $cart_result = mysqli_query($conn, "SELECT id FROM cart WHERE user_id = $user_id");

        if (!$cart_result) {
            throw new Exception("Error retrieving cart: " . mysqli_error($conn));
        }

        if ($cart_row = mysqli_fetch_assoc($cart_result)) {
            $cart_id = $cart_row['id'];
            error_log("Found existing cart: $cart_id");
            // Update created_at on access
            mysqli_query($conn, "UPDATE cart SET created_at = NOW() WHERE id = $cart_id");
        } else {
            // Insert new cart with current timestamp
            $insert_cart_sql = "INSERT INTO cart (user_id, created_at) VALUES (?, NOW())";
            $stmt = mysqli_prepare($conn, $insert_cart_sql);

            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Insert failed: " . mysqli_error($conn));
            }

            $cart_id = mysqli_insert_id($conn);
            error_log("Created new cart: $cart_id");
            mysqli_stmt_close($stmt);
        }

        // Clear existing cart items for this cart
        $delete_result = mysqli_query($conn, "DELETE FROM cart_item WHERE cart_id = $cart_id");
        error_log("Cleared existing cart items, affected rows: " . mysqli_affected_rows($conn));

        // Insert current items
        foreach ($_SESSION['cart'] as $cart_key => $item) {
            // Additional validation to ensure $item is an array
            if (!is_array($item) || !isset($item['product_id'], $item['category_id'], $item['quantity'])) {
                error_log("Skipping invalid cart item: " . print_r($item, true));
                continue;
            }
            
            $product_id = (int)$item['product_id'];
            $category_id = (int)$item['category_id'];
            $qty = (int)$item['quantity'];

            $product = getProductDetails($conn, $product_id, $category_id);
            if (!$product) {
                error_log("Skipping cart item - product not found: Product ID $product_id, Category ID $category_id");
                continue;
            }

            $price = $product['price'];
            $subtotal = $price * $qty;

            $stmt = mysqli_prepare($conn, "INSERT INTO cart_item (cart_id, category_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "iiiids", $cart_id, $category_id, $product_id, $qty, $price, $subtotal);
                $execute_result = mysqli_stmt_execute($stmt);
                if ($execute_result) {
                    error_log("Successfully inserted cart item: Product ID $product_id, Category ID $category_id, Quantity $qty");
                } else {
                    error_log("Failed to insert cart item: " . mysqli_error($conn));
                }
                mysqli_stmt_close($stmt);
            } else {
                error_log("Failed to prepare cart item insert statement: " . mysqli_error($conn));
            }
        }
    } catch (Exception $e) {
        $_SESSION['cart_db_error'] = "Database error: " . $e->getMessage();
        error_log("Cart DB Error: " . $e->getMessage());
    }
} else {
    error_log("User not logged in, skipping database sync");
}

// Prepare display data
$cart_items = [];
$total = 0.0;

// Debug: Show current cart contents
error_log("Current cart contents: " . print_r($_SESSION['cart'], true));

if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cart_key => $item) {
        // Validate that $item is an array with required keys
        if (!is_array($item)) {
            error_log("Cart item is not an array: " . print_r($item, true));
            continue;
        }
        
        if (!isset($item['product_id'], $item['category_id'], $item['quantity'])) {
            error_log("Cart item missing required keys: " . print_r($item, true));
            continue;
        }
        
        $product_id = (int)$item['product_id'];
        $category_id = (int)$item['category_id'];
        $qty = (int)$item['quantity'];

        error_log("Processing cart item - Product ID: $product_id, Category ID: $category_id, Quantity: $qty");

        $product = getProductDetails($conn, $product_id, $category_id);
        if (!$product) {
            error_log("Product not found: ID=$product_id, Category=$category_id");
            continue;
        }

        $subtotal = $product['price'] * $qty;

        $cart_items[] = [
            'cart_key' => $cart_key,
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'stock' => $product['stock'],
            'image' => $product['image'],
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];

        $total += $subtotal;
        error_log("Added item to display: " . $product['name'] . " - Price: " . $product['price'] . " - Subtotal: $subtotal");
    }
}

error_log("Total cart items for display: " . count($cart_items) . " - Total amount: $total");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Your Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background-color: #ffcad4;">
  <div class="container">
    <a class="navbar-brand" href="shop.php">HookcraftAvenue</a>
  </div>
</nav>

<div class="container mt-5">
  <!-- Display Cart Updates -->
  <?php if (isset($_SESSION['cart_updates']) && !empty($_SESSION['cart_updates'])): ?>
    <div class="alert alert-warning alert-dismissible fade show">
      <strong>Cart Updates:</strong>
      <ul class="mb-0">
        <?php foreach ($_SESSION['cart_updates'] as $update): ?>
          <li><?= htmlspecialchars($update) ?></li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['cart_updates']); ?>
  <?php endif; ?>

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

  <?php if (isset($_SESSION['cart_db_error'])): ?>
    <div class="alert alert-warning alert-dismissible fade show">
      <?= htmlspecialchars($_SESSION['cart_db_error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['cart_db_error']); ?>
  <?php endif; ?>

  <h2 class="mb-4">🛒 Your Shopping Cart</h2>
  <?php if (empty($cart_items)): ?>
    <div class="alert alert-info">Your cart is empty. <a href="shop.php">Continue shopping</a>.</div>
  <?php else: ?>
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cart_items as $item): ?>
          <tr>
            <td>
              <?php if (!empty($item['image'])): ?>
                <img src="../asset/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 50px; height: 50px; object-fit: cover;">
              <?php endif; ?>
              <?= htmlspecialchars($item['name']) ?>
            </td>
            <td>₱<?= number_format($item['price'], 2) ?></td>
            <td>
              <form method="post" class="d-flex align-items-center">
                <input type="hidden" name="update_id" value="<?= htmlspecialchars($item['cart_key']) ?>">
                <select name="update_qty" class="form-select form-select-sm me-2" onchange="this.form.submit()" style="width:auto;">
                  <?php for ($i = 1; $i <= min($item['stock'], 99); $i++): ?>
                    <option value="<?= $i ?>" <?= $i == $item['quantity'] ? 'selected' : '' ?>><?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </form>
            </td>
            <td>₱<?= number_format($item['subtotal'], 2) ?></td>
            <td>
              <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmRemoveModal" data-remove-id="<?= htmlspecialchars($item['cart_key']) ?>">Remove</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="d-flex justify-content-between mt-3">
      <a href="shop.php" class="btn btn-secondary">← Continue Shopping</a>
      <h4>Total: ₱<?= number_format($total, 2) ?></h4>
    </div>
    <div class="d-flex justify-content-end mt-3">
      <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    </div>
  <?php endif; ?>

  <!-- Debug Section (Remove this in production) -->
  <?php if (isset($_GET['debug'])): ?>
    <div class="card mt-4">
      <div class="card-header">
        <h5>Debug Information</h5>
      </div>
      <div class="card-body">
        <h6>Session Cart:</h6>
        <pre><?= htmlspecialchars(print_r($_SESSION['cart'] ?? 'No cart data', true)) ?></pre>
        
        <h6>Cart Items Array:</h6>
        <pre><?= htmlspecialchars(print_r($cart_items, true)) ?></pre>
        
        <h6>User Logged In:</h6>
        <p><?= isset($_SESSION['user_id']) ? 'Yes (ID: ' . $_SESSION['user_id'] . ')' : 'No' ?></p>
        
        <h6>Database Connection:</h6>
        <p><?= $conn ? 'Connected' : 'Not connected' ?></p>
        
        <h6>Available Tables:</h6>
        <?php
        $tables = mysqli_query($conn, "SHOW TABLES");
        while ($table = mysqli_fetch_array($tables)) {
            echo "<p>" . $table[0] . "</p>";
        }
        ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Remove Confirmation Modal -->
<div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-labelledby="confirmRemoveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmRemoveModalLabel">Confirm Removal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to remove this item from your cart?
          <input type="hidden" name="remove_id" id="modal-remove-id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Remove</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const confirmModal = document.getElementById('confirmRemoveModal');
  confirmModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const removeId = button.getAttribute('data-remove-id');
    confirmModal.querySelector('#modal-remove-id').value = removeId;
  });
</script>
</body>
</html>