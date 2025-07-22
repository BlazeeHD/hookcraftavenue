<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$total = 0;
$cart_items = [];
if (!empty($_SESSION['cart'])) {
  $ids = implode(',', array_keys($_SESSION['cart']));
  $query = "SELECT * FROM products WHERE id IN ($ids)";
  $result = mysqli_query($conn, $query);
  while ($row = mysqli_fetch_assoc($result)) {
    $pid = $row['id'];
    $qty = $_SESSION['cart'][$pid];
    $subtotal = $qty * $row['price'];
    $total += $subtotal;
    $cart_items[] = [
      'id' => $pid,
      'name' => $row['name'],
      'image' => $row['image'],
      'price' => $row['price'],
      'quantity' => $qty,
      'subtotal' => $subtotal
    ];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
  $name = trim($_POST['name']);
  $address = trim($_POST['address']);
  $phone = trim($_POST['phone']);
  $payment_method = $_POST['payment_method'] ?? 'GCash';
  $payment_status = 'Pending';

  if ($name && $address && $phone && !empty($cart_items)) {
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, address, phone, total, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $address, $phone, $total, $payment_method, $payment_status);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    $insert_items = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

    foreach ($cart_items as $item) {
      $insert_items->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
      $insert_items->execute();

      $qty = $item['quantity'];
      $pid = $item['id'];
      $update_stock->bind_param("iii", $qty, $pid, $qty);
      $update_stock->execute();
    }

    // Formspree Notification (without file)
    $formspree_url = "https://formspree.io/f/xjkvwyyq";
    $body = "name=$name&address=$address&phone=$phone&total=₱$total";
    foreach ($cart_items as $item) {
      $body .= "&items[]=" . urlencode($item['name'] . ' x' . $item['quantity']);
    }

    $ch = curl_init($formspree_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_exec($ch);
    curl_close($ch);

    $_SESSION['cart'] = [];
    echo '<script>window.location="thankyou.php?total=' . $total . '";</script>';
    exit();
  } else {
    echo '<script>alert("Please fill out all fields correctly.");</script>';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background-color: #ffcad4;">
  <div class="container">
    <a class="navbar-brand" href="shop.php">HookcraftAvenue</a>
  </div>
</nav>
<div class="container mt-5">
  <h2 class="mb-4">Checkout</h2>
  <form method="POST">
    <div class="mb-3">
      <label for="name" class="form-label">Full Name</label>
      <input type="text" class="form-control" name="name" id="name" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Address</label>
      <div class="row g-2">
        <div class="col-md-4">
          <select class="form-select" name="region" required>
            <option value="">Select Region</option>
            <option value="Region XI">Region XI</option>
            <option value="Region VII">Region VII</option>
            <!-- Add more regions if needed -->
          </select>
        </div>
        <div class="col-md-4">
          <select class="form-select" name="province" required>
            <option value="">Select Province</option>
            <option value="Davao del Sur">Davao del Sur</option>
            <option value="Cebu">Cebu</option>
            <!-- Add more provinces -->
          </select>
        </div>
        <div class="col-md-4">
          <select class="form-select" name="city" required>
            <option value="">Select City / Municipality</option>
            <option value="Davao City">Davao City</option>
            <option value="Cebu City">Cebu City</option>
            <!-- Add more cities -->
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
      <label for="phone" class="form-label">Phone Number</label>
      <input type="tel" class="form-control" name="phone" id="phone" required pattern="[0-9]{11}" placeholder="e.g. 09123456789">
    </div>
    <div class="mb-3">
      <label class="form-label">Payment Method</label><br>
      <input type="radio" name="payment_method" value="GCash" checked> GCash (Manual Payment)
    </div>
    <h4>Order Summary</h4>
    <ul class="list-group mb-3">
      <?php foreach ($cart_items as $item): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <?= $item['name'] ?> x <?= $item['quantity'] ?>
          <span>₱<?= number_format($item['subtotal'], 2) ?></span>
        </li>
      <?php endforeach; ?>
      <li class="list-group-item d-flex justify-content-between">
        <strong>Total</strong>
        <strong>₱<?= number_format($total, 2) ?></strong>
      </li>
    </ul>
    <div class="d-flex justify-content-between">
      <a href="cart.php" class="btn btn-secondary">← Go Back to Cart</a>
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">Place Order</button>
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirm Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to place this order?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="confirm_checkout" class="btn btn-success">Yes, Place Order</button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelector('form').addEventListener('submit', function() {
    const region = document.querySelector('[name="region"]').value;
    const province = document.querySelector('[name="province"]').value;
    const city = document.querySelector('[name="city"]').value;
    const barangay = document.querySelector('[name="barangay"]').value;
    const street = document.querySelector('[name="street"]').value;
    document.getElementById('finalAddress').value = `${street}, ${barangay}, ${city}, ${province}, ${region}`;
  });
</script>
</body>
</html>
