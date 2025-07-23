<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');

// Handle Add to Cart (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
  $isLoggedIn = isset($_SESSION['user_id']);
  if (!$isLoggedIn) {
    echo "not_logged_in";
    exit;
  }
  $pid = (int)$_POST['product_id'];
  $stock_query = $conn->prepare("SELECT stock FROM products WHERE id = ?");
  $stock_query->bind_param("i", $pid);
  $stock_query->execute();
  $stock_query->bind_result($productStock);
  $stock_query->fetch();
  $stock_query->close();
  $cart_qty = $_SESSION['cart'][$pid] ?? 0;
  if ($cart_qty < $productStock) {
    $_SESSION['cart'][$pid] = $cart_qty + 1;
  } else {
    echo "max";
    exit;
  }
  $new_cart_count = 0;
  foreach ($_SESSION['cart'] as $qty) {
    $new_cart_count += $qty;
  }
  echo $new_cart_count;
  exit;
}

// Only include UI if not an AJAX add-to-cart request
include('login_modal.php');             // Assuming login.php is UI only (no logic)
include('signup_modal.php');      // ✅ Use modal only, NOT signup logic!

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$cart_count = 0;
foreach ($_SESSION['cart'] as $qty) {
  $cart_count += $qty;
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Flower Shop - Shop</title>
  <link rel="icon" href="../asset/images/logo.jpg" type="image/png">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../asset/styles.css">
  <style>
    .cart-bounce { animation: bounce 0.6s; }
    @keyframes bounce {
      0% { transform: scale(1); }
      30% { transform: scale(1.3); }
      50% { transform: scale(0.9); }
      70% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
    .flash-added {
      background: #d63384 !important;
      color: #fff !important;
      transition: background 0.3s;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
      <img src="../asset/images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
      HookcraftAvenue
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>

      <form class="d-flex me-3" role="search">
        <input type="text" id="searchInput" class="form-control" placeholder="Search products..." style="max-width: 250px;">
        <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
      </form>

      <ul class="navbar-nav flex-row">
        <li class="nav-item me-3">
          <?php if ($isLoggedIn): ?>
            <a class="nav-link position-relative" href="cart.php">
          <?php else: ?>
            <a class="nav-link position-relative" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
          <?php endif; ?>
              <i class="bi bi-cart fs-5"></i>
              <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $cart_count ?>
              </span>
            </a>
        </li>
        <?php if ($isLoggedIn): ?>
          <li class="nav-item">
            <a class="nav-link" href="profile.php">
              <i class="bi bi-person-circle fs-5"></i> <?= htmlspecialchars($userName); ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">
              <i class="bi bi-box-arrow-right fs-5"></i> Logout
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
              <i class="bi bi-person fs-5"></i>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Product Grid -->
<div class="container-fluid mt-4">
  <div class="row">
    <aside class="col-md-3 sidebar">
      <h2>Categories</h2>
      <ul class="list-group mb-4">
        <li class="list-group-item"><a href="#" onclick="filterProducts('all')">All</a></li>
        <li class="list-group-item"><a href="#" onclick="filterProducts('mini')">Fancy Mini Flower</a></li>
        <li class="list-group-item"><a href="#" onclick="filterProducts('giant')">Giant Flower</a></li>
        <li class="list-group-item"><a href="#" onclick="filterProducts('choco')">Chocolate Flower</a></li>
        <li class="list-group-item"><a href="#" onclick="filterProducts('bouquet')">Flower Bouquet</a></li>
        <li class="list-group-item"><a href="#" onclick="filterProducts('accessories')">Accessories</a></li>
      </ul>
      <div class="filter-price">
        <h3>Filter by Price</h3>
        <input type="range" class="form-range" min="20" max="1000" value="1000" id="priceRange">
        <div class="d-flex justify-content-between">
          <span>₱20</span>
          <span>₱<span id="maxPrice">1000</span></span>
        </div>
      </div>
    </aside>

    <section class="col-md-9">
      <div class="row g-4" id="product-list">
        <?php
        $query = "SELECT * FROM products";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
          $productName = $row['name'];
          $productImage = $row['image'];
          $productPrice = $row['price'];
          $productCategory = $row['category'];
          $productStock = $row['stock'];
        ?>
        <div class="col-sm-6 col-md-4 product-item" data-category="<?= $productCategory ?>" data-name="<?= $productName ?>" data-price="<?= $productPrice ?>">
          <div class="product-card position-relative">
            <img src="<?= $productImage ?>" alt="<?= $productName ?>">
            <p class="product-price">₱<?= number_format($productPrice, 2) ?></p>
            <div class="position-absolute top-0 end-0 m-2">
              <span class="badge bg-secondary">Stock: <?= $productStock ?></span>
            </div>
            <?php if ($productStock > 0): ?>
              <button class="add-to-cart-btn" data-id="<?= $row['id'] ?>">Add to Cart</button>
            <?php else: ?>
              <button class="add-to-cart-btn btn-secondary" disabled>Out of Stock</button>
            <?php endif; ?>
          </div>
        </div>
        <?php } ?>
      </div>
    </section>
  </div>
</div>

<div id="cart-notify" style="display:none;position:fixed;top:80px;right:30px;z-index:9999;" class="alert alert-success shadow">Added to cart!</div>

<footer class="footer mt-5 text-center">
  <p>&copy; 2025 Hookcraft Avenue. All rights reserved.</p>
</footer>

<!-- Signup/Login Modals -->
<?php include('signup_modal.php'); ?>
<?php include('login.php'); ?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
  function filterProducts(category) {
    document.querySelectorAll('.product-item').forEach(item => {
      item.style.display = (category === 'all' || item.dataset.category === category) ? 'block' : 'none';
    });
  }

  document.getElementById('searchInput').addEventListener('input', function () {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
      item.style.display = item.dataset.name.toLowerCase().includes(keyword) ? 'block' : 'none';
    });
  });

  document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', function () {
      const productId = this.getAttribute('data-id');
      const cartCount = document.getElementById('cart-count');
      const thisButton = this;

      fetch('shop.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
      })
      .then(res => res.text())
      .then(data => {
        if (data === "not_logged_in") {
          alert("Please log in to add products to your cart.");
          return;
        }
        if (data === "max") {
          alert("You’ve reached the maximum stock for this product.");
          return;
        }

        document.getElementById('cart-notify').style.display = 'block';
        cartCount.innerText = data;
        cartCount.classList.add('cart-bounce');
        setTimeout(() => {
          document.getElementById('cart-notify').style.display = 'none';
          cartCount.classList.remove('cart-bounce');
        }, 1000);

        thisButton.classList.add('flash-added');
        setTimeout(() => thisButton.classList.remove('flash-added'), 500);
      });
    });
  });

  const priceSlider = document.getElementById('priceRange');
  const maxPriceText = document.getElementById('maxPrice');

  priceSlider.addEventListener('input', function () {
    const maxPrice = parseInt(this.value);
    maxPriceText.textContent = maxPrice;
    document.querySelectorAll('.product-item').forEach(item => {
      const itemPrice = parseFloat(item.dataset.price);
      item.style.display = (itemPrice <= maxPrice) ? 'block' : 'none';
    });
  });

  window.addEventListener('DOMContentLoaded', () => {
    priceSlider.value = 1000;
    maxPriceText.textContent = '1000';
  });
</script>
</body>
</html>
