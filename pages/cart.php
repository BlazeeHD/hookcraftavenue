<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  $_SESSION['login_required'] = true;
  header('Location: ../index.php');
  exit;
}

$user_id = $_SESSION['user_id'];

// Function to get or create cart and return cart_id
function getCartId($conn, $user_id) {
  $result = mysqli_query($conn, "SELECT id FROM cart WHERE user_id = $user_id LIMIT 1");
  if ($row = mysqli_fetch_assoc($result)) {
    return $row['id'];
  } else {
    mysqli_query($conn, "INSERT INTO cart (user_id) VALUES ($user_id)");
    return mysqli_insert_id($conn);
  }
}

// Function to sync session cart to database
function syncCartToDatabase($conn, $user_id, $session_cart) {
  $cart_id = getCartId($conn, $user_id);

  // Clear current cart items
  mysqli_query($conn, "DELETE FROM cart_item WHERE cart_id = $cart_id");

  // Insert each item
  foreach ($session_cart as $product_id => $quantity) {
    $stmt = mysqli_prepare($conn, "INSERT INTO cart_item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "iii", $cart_id, $product_id, $quantity);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }
  }
}

// Function to load cart from database to session
function loadCartFromDatabase($conn, $user_id) {
  $cart = [];
  $result = mysqli_query($conn, "SELECT id FROM cart WHERE user_id = $user_id LIMIT 1");
  if ($row = mysqli_fetch_assoc($result)) {
    $cart_id = $row['id'];
    $items = mysqli_query($conn, "SELECT product_id, quantity FROM cart_item WHERE cart_id = $cart_id");
    while ($item = mysqli_fetch_assoc($items)) {
      $cart[$item['product_id']] = $item['quantity'];
    }
  }
  return $cart;
}

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = loadCartFromDatabase($conn, $user_id);
}

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
  $remove_id = intval($_POST['remove_id']);
  if (isset($_SESSION['cart'][$remove_id])) {
    unset($_SESSION['cart'][$remove_id]);

    $cart_id = getCartId($conn, $user_id);
    $stmt = mysqli_prepare($conn, "DELETE FROM cart_item WHERE cart_id = ? AND product_id = ?");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "ii", $cart_id, $remove_id);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }
  }
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'], $_POST['update_qty'])) {
  $update_id = intval($_POST['update_id']);
  $update_qty = intval($_POST['update_qty']);

  $stock_check = mysqli_query($conn, "SELECT stock FROM products WHERE id = $update_id");
  $stock_row = mysqli_fetch_assoc($stock_check);
  if ($stock_row && $update_qty > 0 && $update_qty <= $stock_row['stock']) {
    $_SESSION['cart'][$update_id] = $update_qty;

    $cart_id = getCartId($conn, $user_id);
    $stmt = mysqli_prepare($conn, "
      INSERT INTO cart_item (cart_id, product_id, quantity)
      VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)
    ");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "iii", $cart_id, $update_id, $update_qty);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }
  }
}

// Sync session cart to DB
syncCartToDatabase($conn, $user_id, $_SESSION['cart']);

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
      'stock' => $row['stock'],
      'subtotal' => $subtotal
    ];
  }
}
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
            <td>
              <form method="post" class="d-flex align-items-center">
                <input type="hidden" name="update_id" value="<?= $item['id'] ?>">
                <select name="update_qty" class="form-select form-select-sm me-2" onchange="this.form.submit()" style="width:auto;">
                  <?php for ($i = 1; $i <= $item['stock']; $i++): ?>
                    <option value="<?= $i ?>" <?= $i == $item['quantity'] ? 'selected' : '' ?>><?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </form>
            </td>
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
