<?php
include '../includes/db.php';
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$total = 0;
$cart_items = [];
$cart_errors = [];

// Function to get category name
function getCategoryName($conn, $category_id) {
  $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
  $stmt->bind_param("i", $category_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $category = $result->fetch_assoc();
  return $category ? $category['name'] : 'Unknown';
}

// Function to get product from unified products table
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

// Function to update stock in unified products table
function updateProductStock($conn, $category_id, $product_id, $quantity) {
    try {
        $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND category_id = ? AND stock >= ?");
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("iiii", $quantity, $product_id, $category_id, $quantity);
        $result = $stmt->execute();
        
        return $result && $stmt->affected_rows > 0;
    } catch (Exception $e) {
        error_log("Error updating stock: " . $e->getMessage());
    }
    return false;
}

// Clean and validate cart items
if (!empty($_SESSION['cart'])) {
    $cleaned_cart = [];
    
    foreach ($_SESSION['cart'] as $cart_key => $cart_data) {
        error_log("Processing cart item: $cart_key => " . print_r($cart_data, true));
        
        // Parse cart key format: "category_id_product_id"
        if (strpos($cart_key, '_') !== false) {
            $parts = explode('_', $cart_key);
            if (count($parts) == 2) {
                $category_id = (int)$parts[0];
                $product_id = (int)$parts[1];
            } else {
                error_log("Invalid cart key format: $cart_key");
                continue;
            }
        } else {
            error_log("Cart key missing separator: $cart_key");
            continue;
        }
        
        // Get quantity from cart data
        if (is_array($cart_data)) {
            $qty = (int)($cart_data['quantity'] ?? 1);
        } else {
            $qty = (int)$cart_data;
        }
        
        if ($qty <= 0) {
            $cart_errors[] = "Invalid quantity for cart item, removed.";
            continue;
        }
        
        // Get fresh product data from database
        $product = getProductDetails($conn, $product_id, $category_id);
        
        if (!$product) {
            $cart_errors[] = "Product no longer available and removed from cart.";
            error_log("Product not found: Product ID $product_id, Category ID $category_id");
            continue;
        }
        
        // Check if product has sufficient stock
        if ($product['stock'] <= 0) {
            $cart_errors[] = htmlspecialchars($product['name']) . " is out of stock and removed from cart.";
            continue;
        }
        
        // Adjust quantity if exceeds available stock
        if ($qty > $product['stock']) {
            $qty = $product['stock'];
            $cart_errors[] = htmlspecialchars($product['name']) . " quantity adjusted to available stock ($qty).";
        }
        
        // Calculate subtotal
        $subtotal = $qty * (float)$product['price'];
        $total += $subtotal;
        
        // Add to cleaned cart items
        $cart_items[] = [
            'category_id' => $category_id,
            'product_id' => $product_id,
            'name' => $product['name'],
            'image' => $product['image'],
            'price' => (float)$product['price'],
            'quantity' => $qty,
            'subtotal' => $subtotal,
            'stock' => $product['stock'],
            'category_name' => $product['category_name']
        ];
        
        // Update cleaned cart session
        $cleaned_cart[$cart_key] = [
            'product_id' => $product_id,
            'category_id' => $category_id,
            'quantity' => $qty
        ];
        
        error_log("Added valid cart item: " . $product['name']);
    }
    
    // Update session with cleaned cart
    $_SESSION['cart'] = $cleaned_cart;
    error_log("Cleaned cart: " . print_r($_SESSION['cart'], true));
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'] ?? 'GCash';
    $status = 'Pending';

    // Validate inputs
    if (empty($name) || empty($address) || empty($phone)) {
        echo '<script>alert("Please fill out all required fields.");</script>';
    } elseif (empty($cart_items)) {
        echo '<script>alert("Your cart is empty.");</script>';
    } else {
        try {
            $conn->begin_transaction();

            // Re-validate cart items and stock before final processing
            $final_total = 0;
            $valid_items = [];
            
            foreach ($cart_items as $item) {
                // Get fresh product data again to ensure accuracy
                $fresh_product = getProductDetails($conn, $item['product_id'], $item['category_id']);
                
                if (!$fresh_product) {
                    throw new Exception("Product '" . $item['name'] . "' is no longer available.");
                }
                
                if ($fresh_product['stock'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for '" . $item['name'] . "'. Available: " . $fresh_product['stock']);
                }
                
                // Update item with fresh price (in case admin changed prices)
                $item['price'] = (float)$fresh_product['price'];
                $item['subtotal'] = $item['quantity'] * $item['price'];
                $final_total += $item['subtotal'];
                $valid_items[] = $item;
            }

            // Check if user is logged in
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            // Check if orders table has customer fields
            $columns_check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'customer_name'");
            $has_customer_fields = mysqli_num_rows($columns_check) > 0;
            
            if ($has_customer_fields) {
                // Create order with customer details
                $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, address, phone, total, payment_status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssds", $user_id, $name, $address, $phone, $final_total, $payment_status);
            } else {
                // Create order without customer details (basic table structure)
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total, payment_status) VALUES (?, ?, ?)");
                $stmt->bind_param("ids", $user_id, $final_total, $payment_status);
            }
            
            $stmt->execute();
            $order_id = $stmt->insert_id;

            // Insert order items (do NOT update stock here)
            $insert_items = $conn->prepare("INSERT INTO order_item (order_id, category_id, product_id, quantity, price) VALUES (?, ?, ?, ?, ?)");

            foreach ($valid_items as $item) {
                $insert_items->bind_param("iiiid", $order_id, $item['category_id'], $item['product_id'], $item['quantity'], $item['price']);
                $insert_items->execute();
            }
            // Stock will be updated only when order is marked as successful/paid.

            $conn->commit();

            // Send email notification (optional)
            try {
                $formspree_url = "https://formspree.io/f/xjkvwyyq";
                $email_body = http_build_query([
                    'name' => $name,
                    'address' => $address,
                    'phone' => $phone,
                    'total' => "₱" . number_format($final_total, 2),
                    'order_id' => $order_id,
                    'items' => implode(', ', array_map(function($item) {
                        return $item['name'] . ' x' . $item['quantity'];
                    }, $valid_items))
                ]);

                $ch = curl_init($formspree_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $email_body);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_exec($ch);
                curl_close($ch);
            } catch (Exception $e) {
                // Email failed but order succeeded - log error but don't fail checkout
                error_log("Email notification failed: " . $e->getMessage());
            }

            // Clear cart and redirect
            $_SESSION['cart'] = [];
            echo '<script>
                alert("Order placed successfully! Order ID: ' . $order_id . '");
                window.location="thankyou.php?order_id=' . $order_id . '&total=' . $final_total . '";
            </script>';
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            echo '<script>alert("Checkout failed: ' . addslashes($e->getMessage()) . '");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - HookcraftAvenue</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .cart-error { background-color: #fff3cd; border: 1px solid #ffeaa7; }
    .product-image { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background-color: #ffcad4;">
  <div class="container">
    <a class="navbar-brand fw-bold" href="shop.php">HookcraftAvenue</a>
  </div>
</nav>

<div class="container mt-5">
  <h2 class="mb-4">Checkout</h2>
  
  <!-- Display cart errors/warnings -->
  <?php if (!empty($cart_errors)): ?>
    <div class="alert alert-warning cart-error">
      <h6>Cart Updates:</h6>
      <ul class="mb-0">
        <?php foreach ($cart_errors as $error): ?>
          <li><?= $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  
  <?php if (empty($cart_items)): ?>
    <div class="alert alert-info">
      <h5>Your cart is empty</h5>
      <p>Add some products to your cart before checking out.</p>
      <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
    </div>
  <?php else: ?>
  
  <div class="row">
    <div class="col-lg-7">
      <form method="POST" id="checkoutForm">
        <div class="card mb-4">
          <div class="card-header">
            <h5>Customer Information</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label for="name" class="form-label">Full Name *</label>
              <input type="text" class="form-control" name="name" id="name" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Delivery Address *</label>
              <div class="row g-2">
                <div class="col-md-4">
                  <select class="form-select" name="region" required>
                    <option value="">Select Region</option>
                    <option value="Region XI">Region XI (Davao)</option>
                    <option value="Region VII">Region VII (Central Visayas)</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <select class="form-select" name="province" required>
                    <option value="">Select Province</option>
                    <option value="Davao del Sur">Davao del Sur</option>
                    <option value="Cebu">Cebu</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <select class="form-select" name="city" required>
                    <option value="">Select City/Municipality</option>
                    <option value="Davao City">Davao City</option>
                    <option value="Cebu City">Cebu City</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <input type="text" class="form-control" name="barangay" placeholder="Barangay" required>
                </div>
                <div class="col-md-6">
                  <input type="text" class="form-control" name="street" placeholder="Street / House No." required>
                </div>
              </div>
              <input type="hidden" name="address" id="finalAddress">
            </div>

            <div class="mb-3">
              <label for="phone" class="form-label">Phone Number *</label>
              <input type="tel" class="form-control" name="phone" id="phone" required 
                     pattern="[0-9]{11}" placeholder="e.g. 09123456789" maxlength="11">
            </div>

            <div class="mb-3">
              <label class="form-label">Payment Method</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="GCash" checked>
                <label class="form-check-label" for="gcash">
                  GCash (Manual Payment) - You will receive payment instructions after placing order
                </label>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header">
          <h5>Order Summary</h5>
        </div>
        <div class="card-body">
          <?php foreach ($cart_items as $item): ?>
            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
              <?php if (!empty($item['image'])): ?>
                <img src="../asset/images/<?= htmlspecialchars($item['image']) ?>" 
                     alt="<?= htmlspecialchars($item['name']) ?>" 
                     class="product-image me-3">
              <?php endif; ?>
              <div class="flex-grow-1">
                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                <small class="text-muted"><?= htmlspecialchars($item['category_name']) ?></small><br>
                <small class="text-muted">Qty: <?= $item['quantity'] ?> × ₱<?= number_format($item['price'], 2) ?></small>
              </div>
              <div class="text-end">
                <strong>₱<?= number_format($item['subtotal'], 2) ?></strong>
              </div>
            </div>
          <?php endforeach; ?>
          
          <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <h5 class="mb-0">Total:</h5>
            <h5 class="mb-0 text-primary">₱<?= number_format($total, 2) ?></h5>
          </div>
          
          <div class="d-grid gap-2 mt-4">
            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#confirmModal">
              Place Order
            </button>
            <a href="cart.php" class="btn btn-outline-secondary">← Back to Cart</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmModalLabel">Confirm Your Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Please review your order details:</p>
          <ul>
            <li><strong>Items:</strong> <?= count($cart_items) ?> product(s)</li>
            <li><strong>Total:</strong> ₱<?= number_format($total, 2) ?></li>
            <li><strong>Payment:</strong> GCash (Manual Payment)</li>
          </ul>
          <p class="text-muted">By confirming, you agree to our terms and conditions.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Review Order</button>
          <button type="button" class="btn btn-success" onclick="submitOrder()">Confirm & Place Order</button>
        </div>
      </div>
    </div>
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
        
        <h6>Total:</h6>
        <p>₱<?= number_format($total, 2) ?></p>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function submitOrder() {
  // Build final address
  const region = document.querySelector('[name="region"]').value;
  const province = document.querySelector('[name="province"]').value;
  const city = document.querySelector('[name="city"]').value;
  const barangay = document.querySelector('[name="barangay"]').value;
  const street = document.querySelector('[name="street"]').value;
  
  if (!region || !province || !city || !barangay || !street) {
    alert('Please complete all address fields.');
    return;
  }
  
  document.getElementById('finalAddress').value = `${street}, ${barangay}, ${city}, ${province}, ${region}`;
  
  // Add confirm_checkout input
  const form = document.getElementById('checkoutForm');
  const confirmInput = document.createElement('input');
  confirmInput.type = 'hidden';
  confirmInput.name = 'confirm_checkout';
  confirmInput.value = '1';
  form.appendChild(confirmInput);
  
  // Submit form
  form.submit();
}

// Auto-refresh cart every 30 seconds to sync with admin changes
setInterval(function() {
  // Only refresh if user hasn't started filling the form
  const name = document.querySelector('[name="name"]').value;
  if (!name) {
    window.location.reload();
  }
}, 30000);
</script>
</body>
</html>