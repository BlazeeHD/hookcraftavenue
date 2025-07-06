<?php
include 'db.php';
session_start();

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
  $remove_id = intval($_POST['remove_id']);
  if (isset($_SESSION['cart'][$remove_id])) {
    unset($_SESSION['cart'][$remove_id]);
  }
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
          <th>Action</th>
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
            <td>
              <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmRemoveModal" data-remove-id="<?= $item['id'] ?>">Remove</button>
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
</div>

<!-- Modal -->
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
