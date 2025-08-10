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

// Function to get product from appropriate table
function getProduct($conn, $category_id, $product_id) {
  switch ($category_id) {
    case 1: // Satin
      $stmt = $conn->prepare("SELECT satin_id as product_id, name, price, image, stock, description FROM satin_products WHERE satin_id = ? AND category_id = ?");
      break;
    case 2: // Fizzywire  
      $stmt = $conn->prepare("SELECT fizzywire_id as product_id, name, price, image, stock, description FROM fizzywire_products WHERE fizzywire_id = ? AND category_id = ?");
      break;
    case 3: // Customize
      $stmt = $conn->prepare("SELECT customize_id as product_id, name, price, image, stock, description FROM customize_products WHERE customize_id = ? AND category_id = ?");
      break;
    default:
      return null;
  }
  
  $stmt->bind_param("ii", $product_id, $category_id);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_assoc();
}

// Function to update stock
function updateProductStock($conn, $category_id, $product_id, $quantity) {
  switch ($category_id) {
    case 1: // Satin
      $stmt = $conn->prepare("UPDATE satin_products SET stock = stock - ? WHERE satin_id = ? AND stock >= ?");
      break;
    case 2: // Fizzywire
      $stmt = $conn->prepare("UPDATE fizzywire_products SET stock = stock - ? WHERE fizzywire_id = ? AND stock >= ?");
      break;
    case 3: // Customize
      $stmt = $conn->prepare("UPDATE customize_products SET stock = stock - ? WHERE customize_id = ? AND stock >= ?");
      break;
    default:
      return false;
  }
  
  $stmt->bind_param("iii", $quantity, $product_id, $quantity);
  $stmt->execute();
  return $stmt->affected_rows > 0;
}

// Clean and validate cart items
if (!empty($_SESSION['cart'])) {
  $cleaned_cart = [];
  
  foreach ($_SESSION['cart'] as $cart_key => $cart_data) {
    // Parse cart key format: "category_id-product_id" or handle other formats
    if (strpos($cart_key, '-') !== false) {
      $parts = explode('-', $cart_key);
      if (count($parts) == 2) {
        $category_id = (int)$parts[0];
        $product_id = (int)$parts[1];
      } else {
        continue; // Skip invalid format
      }
    } else {
      // Handle simple numeric product IDs (legacy format)
      $product_id = (int)$cart_key;
      // Try to find the category by checking all product tables
      $category_id = null;
      
      // Check satin_products
      $stmt = $conn->prepare("SELECT category_id FROM satin_products WHERE satin_id = ?");
      $stmt->bind_param("i", $product_id);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($row = $result->fetch_assoc()) {
        $category_id = $row['category_id'];
      } else {
        // Check fizzywire_products
        $stmt = $conn->prepare("SELECT category_id FROM fizzywire_products WHERE fizzywire_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
          $category_id = $row['category_id'];
        } else {
          // Check customize_products
          $stmt = $conn->prepare("SELECT category_id FROM customize_products WHERE customize_id = ?");
          $stmt->bind_param("i", $product_id);
          $stmt->execute();
          $result = $stmt->get_result();
          if ($row = $result->fetch_assoc()) {
            $category_id = $row['category_id'];
          }
        }
      }
      
      if (!$category_id) {
        $cart_errors[] = "Product ID $product_id not found and removed from cart.";
        continue;
      }
    }
    
    // Get quantity - handle both array and direct value formats
    $qty = is_array($cart_data) ? (int)($cart_data['quantity'] ?? $cart_data['qty'] ?? 1) : (int)$cart_data;
    if ($qty <= 0) {
      $cart_errors[] = "Invalid quantity for product, item removed from cart.";
      continue;
    }
    
    // Get fresh product data from database
    $product = getProduct($conn, $category_id, $product_id);
    
    if (!$product) {
      $cart_errors[] = "Product no longer available and removed from cart.";
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
      'category_name' => getCategoryName($conn, $category_id)
    ];
    
    // Update cleaned cart session
    $cleaned_cart[$category_id . '-' . $product_id] = $qty;
  }
  
  // Update session with cleaned cart
  $_SESSION['cart'] = $cleaned_cart;
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
        $fresh_product = getProduct($conn, $item['category_id'], $item['product_id']);
        
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
      
      // Create order with final total
      $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)");
      $stmt->bind_param("ids", $user_id, $final_total, $status);
      $stmt->execute();
      $order_id = $stmt->insert_id;

      // Insert order items and update stock
      $insert_items = $conn->prepare("INSERT INTO order_item (order_id, category_id, product_id, quantity, price) VALUES (?, ?, ?, ?, ?)");

      foreach ($valid_items as $item) {
        // Insert order item
        $insert_items->bind_param("iiiid", $order_id, $item['category_id'], $item['product_id'], $item['quantity'], $item['price']);
        $insert_items->execute();

        // Update stock
        if (!updateProductStock($conn, $item['category_id'], $item['product_id'], $item['quantity'])) {
          throw new Exception("Failed to update stock for '" . $item['name'] . "'");
        }
      }

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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Add timeout
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