<?php
include '../includes/db.php';
session_start();

// Ensure session cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle REMOVE action
if (isset($_POST['remove_id'])) {
    $product_id = $_POST['remove_id'];
    unset($_SESSION['cart'][$product_id]);
}

// Handle UPDATE action
if (isset($_POST['update_id']) && isset($_POST['update_qty'])) {
    $product_id = (int)$_POST['update_id'];
    $qty = (int)$_POST['update_qty'];

    if ($qty > 0) {
        $_SESSION['cart'][$product_id] = $qty;
    } else {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Sync to DB if logged in
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];

    // Optional debug
    // echo "Logged in as user ID: " . $user_id;

    // Check if a cart exists for the user
    $cart_result = mysqli_query($conn, "SELECT id FROM cart WHERE user_id = $user_id");

    if (!$cart_result) {
        die("Error retrieving cart: " . mysqli_error($conn));
    }

    if ($cart_row = mysqli_fetch_assoc($cart_result)) {
        $cart_id = $cart_row['id'];
        // Update created_at on access
        mysqli_query($conn, "UPDATE cart SET created_at = NOW() WHERE id = $cart_id");
    } else {
        // Insert new cart with current timestamp
        $insert_cart_sql = "INSERT INTO cart (user_id, created_at) VALUES (?, NOW())";
        $stmt = mysqli_prepare($conn, $insert_cart_sql);

        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (!mysqli_stmt_execute($stmt)) {
            die("Insert failed: " . mysqli_error($conn));
        }

        $cart_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
    }

    // Clear existing cart items for this cart
    mysqli_query($conn, "DELETE FROM cart_item WHERE cart_id = $cart_id");

    // Insert current items
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $product_id = (int)$product_id;
        $qty = (int)$qty;

        $res = mysqli_query($conn, "SELECT price FROM products WHERE id = $product_id");
        if (!$res || mysqli_num_rows($res) === 0) continue;

        $product = mysqli_fetch_assoc($res);
        $price = $product['price'];
        $subtotal = $price * $qty;

        $stmt = mysqli_prepare($conn, "INSERT INTO cart_item (cart_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiidd", $cart_id, $product_id, $qty, $price, $subtotal);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Prepare display data
$cart_items = [];
$total = 0.0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $result = mysqli_query($conn, "SELECT id, name, price, stock, image FROM products WHERE id IN ($ids)");
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[$row['id']] = $row;
    }

    foreach ($_SESSION['cart'] as $product_id => $qty) {
        if (!isset($products[$product_id])) continue;
        $product = $products[$product_id];
        $subtotal = $product['price'] * $qty;

        $cart_items[] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'stock' => $product['stock'],
            'image' => $product['image'],
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];

        $total += $subtotal;
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
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 50px; height: 50px; object-fit: cover;">
              <?= htmlspecialchars($item['name']) ?>
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
