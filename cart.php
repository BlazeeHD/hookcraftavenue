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
      'name' => $row['name'],
      'image' => $row['image'],
      'price' => $row['price'],
      'quantity' => $qty,
      'subtotal' => $subtotal
    ];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background-color: #ffcad4;">
  <div class="container">
    <a class="navbar-brand" href="shop.php">HookcraftAvenue</a>
  </div>
</nav>
<div class="container mt-5">
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
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cart_items as $item): ?>
          <tr>
            <td>
              <img src="<?= $item['image'] ?>" alt="<?= $item['name'] ?>" style="width: 50px; height: 50px; object-fit: cover;">
              <?= $item['name'] ?>
            </td>
            <td>₱<?= number_format($item['price'], 2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>₱<?= number_format($item['subtotal'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="d-flex justify-content-end">
      <h4>Total: ₱<?= number_format($total, 2) ?></h4>
    </div>
    <div class="d-flex justify-content-end mt-3">
      <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
