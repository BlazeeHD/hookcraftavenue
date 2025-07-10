<?php
include 'db.php';
session_start();

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
  $pid = $_POST['product_id'];
  if (isset($_SESSION['cart'][$pid])) {
    $_SESSION['cart'][$pid]++;
  } else {
    $_SESSION['cart'][$pid] = 1;
  }
  echo count($_SESSION['cart']);
  exit;
}

$cart_count = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flower Shop - Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="icon" href="images/logo.jpg" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
  

  </style>
</head>
<body>

<!-- E-commerce Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <!-- Brand Logo -->
    <a class="navbar-brand fw-bold" href="#">
      <img src="images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
      HookcraftAvenue
    </a>

    <!-- Hamburger Icon for Mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links + Icons -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- Navigation Links -->
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>

      <!-- Search Bar -->
              <form class="d-flex me-3" role="search">
        <input type="text" id="searchInput" class="form-control" placeholder="Search products..." style="max-width: 250px;">
        <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
      </form>

      <!-- Cart & User Icons -->
      <ul class="navbar-nav flex-row">
        <li class="nav-item me-3">
          <a class="nav-link position-relative" href="cart.php">
            <i class="bi bi-cart fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              
            </span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
        <i class="bi bi-person fs-5"></i>
        </a>

        </li>
      </ul>
    </div>
  </div>
</nav>



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

<footer class="footer mt-5 text-center">
  <p>&copy; 2025 Hookcraft Avenue. All rights reserved.</p>
</footer>
            
<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="login.php" method="POST" class="modal-content p-4">
      <h5 class="modal-title mb-3">Login</h5>
      <div class="mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
      <p class="mt-3 mb-0 text-center">No account? <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal" data-bs-dismiss="modal">Sign Up</a></p>
    </form>
  </div>
</div>

<!-- Signup Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="signup.php" method="POST" class="modal-content p-4">
      <h5 class="modal-title mb-3">Sign Up</h5>
      <div class="mb-3">
        <input type="text" name="name" class="form-control" placeholder="Full Name" required>
      </div>
      <div class="mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-success">Create Account</button>
      </div>
      <p class="mt-3 mb-0 text-center">Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></p>
    </form>
  </div>
</div>

</body>
</html>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
      const cartIconBadge = document.getElementById('cart-icon-badge');
      const thisButton = this;

      fetch('shop.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
      })
      .then(res => res.text())
      .then(data => {
        cartCount.innerText = data;
        cartIconBadge.innerText = data;
        cartCount.classList.add('cart-bounce');
        cartIconBadge.classList.add('cart-bounce');
        setTimeout(() => {
          cartCount.classList.remove('cart-bounce');
          cartIconBadge.classList.remove('cart-bounce');
        }, 600);
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

