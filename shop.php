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
    body {
      font-family: 'Open Sans', sans-serif;
      background-color: #fff6f9;
    }
    .navbar {
      background-color: #ffcad4;
    }
    .navbar-brand {
      font-family: Arial, sans-serif;
      color: #333;
      font-weight: normal;
    }
    .product-card {
      background: #fff;
      padding: 15px;
      border-radius: 10px;
      text-align: center;
      transition: transform 0.3s, box-shadow 0.3s;
      position: relative;
      overflow: hidden;
      height: 100%;
      cursor: pointer;
    }
    .product-card img {
      width: 100%;
      height: auto;
      transition: transform 0.3s ease, filter 0.3s ease;
      border-radius: 10px;
      display: block;
    }
    .product-price {
      position: absolute;
      bottom: 60px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(255, 111, 164, 0.8);
      color: white;
      padding: 8px 16px;
      border-radius: 10px;
      font-weight: bold;
      font-size: 1.2rem;
      opacity: 0;
      transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .add-to-cart-btn {
      position: absolute;
      bottom: 15px;
      left: 50%;
      transform: translateX(-50%);
      background: #ff6fa4;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.9rem;
      cursor: pointer;
      opacity: 0;
      transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .product-card:hover img {
      transform: scale(1.05);
      filter: brightness(1.1);
    }
    .product-card:hover .product-price,
    .product-card:hover .add-to-cart-btn {
      opacity: 1;
      transform: translateX(-50%) translateY(-5px);
    }
    .cart-bounce {
      animation: bounce 0.6s ease;
    }
    @keyframes bounce {
      0%   { transform: scale(1); }
      30%  { transform: scale(1.3); }
      60%  { transform: scale(0.9); }
      100% { transform: scale(1); }
    }
    .flash-added {
      animation: flash 0.5s ease-in-out;
      background-color: #28a745 !important;
    }
    @keyframes flash {
      0%   { background-color: #ff6fa4; }
      50%  { background-color: #28a745; }
      100% { background-color: #ff6fa4; }
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
      <img src="images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
      HookcraftAvenue
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="index.html">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
        <li class="nav-item"><a class="nav-link" href="gallery.html">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>
      <form class="d-flex me-3" role="search">
        <input class="form-control form-control-sm me-2" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
      </form>
      <ul class="navbar-nav flex-row">
        <li class="nav-item me-3">
          <a class="nav-link position-relative" href="cart.php">
            <i class="bi bi-cart fs-5"></i>
            <span id="cart-icon-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?php echo $cart_count; ?>
            </span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="account.php"><i class="bi bi-person fs-5"></i></a>
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
</body>
</html>
