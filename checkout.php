<?php
include 'db.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $address = trim($_POST['address']);
  $phone = trim($_POST['phone']);

  if ($name && $address && $phone && !empty($cart_items)) {
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, address, phone, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssd", $name, $address, $phone, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    $insert_items = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart_items as $item) {
      $insert_items->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
      $insert_items->execute();
    }

    // Send email to Formspree
    $formspree_url = "https://formspree.io/f/xjkvwyyq"; // Replace with your Formspree endpoint
    $body = "name=$name&address=$address&phone=$phone&total=$total";
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
    echo '<script>alert("Order placed successfully!"); window.location="thankyou.php";</script>';
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
  <form method="POST" onsubmit="return confirmOrder()">
    <div class="mb-3">
      <label for="name" class="form-label">Full Name</label>
      <input type="text" class="form-control" name="name" id="name" required>
    </div>
    <div class="mb-3">
      <label for="address" class="form-label">Address</label>
      <textarea class="form-control" name="address" id="address" rows="2" required></textarea>
    </div>
    <div class="mb-3">
      <label for="phone" class="form-label">Phone Number</label>
      <input type="tel" class="form-control" name="phone" id="phone" required pattern="[0-9]{11}" placeholder="e.g. 09123456789">
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
      <button type="submit" class="btn btn-success">Place Order</button>
    </div>
  </form>
</div>
<script>
function confirmOrder() {
  return confirm("Are you sure you want to place this order?");
}
</script>
</body>
</html>
