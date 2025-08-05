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

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_count = array_sum($_SESSION['cart']);
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Shop - Hookcraft Avenue</title>
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
    <!-- Brand -->
    <a class="navbar-brand fw-bold" href="../index.php">
      <img src="../asset/images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
      HookcraftAvenue
    </a>

    <!-- Mobile toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav content -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- Center links -->
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="../index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>

      <!-- Search form -->
      <form class="d-flex me-3" role="search">
        <input type="text" id="searchInput" class="form-control" placeholder="Search products..." style="max-width: 250px;">
        <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
      </form>

      <!-- Right icons -->
      <ul class="navbar-nav flex-row align-items-center">

        <!-- Cart -->
        <li class="nav-item me-3">
          <?php if ($isLoggedIn): ?>
            <a class="nav-link position-relative" href="pages/cart.php">
          <?php else: ?>
            <a class="nav-link position-relative" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
          <?php endif; ?>
              <i class="bi bi-cart fs-5"></i>
              <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo isset($cart_count) ? $cart_count : 0; ?>
              </span>
            </a>
        </li>

        <!-- User dropdown -->
        <?php if ($isLoggedIn): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle p-0 border-0 bg-transparent" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?php echo $userProfilePic ?? 'asset/images/default-profile.png'; ?>" alt="Profile" width="35" height="35" class="rounded-circle" style="object-fit: cover;">
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-2 p-3 text-center" aria-labelledby="userDropdown" style="min-width: 220px;">
              <li class="mb-2">
                <img src="<?php echo $userProfilePic ?? 'asset/images/default-profile.png'; ?>" alt="Profile" width="60" height="60" class="rounded-circle shadow" style="object-fit: cover;">
              </li>
              <li class="fw-bold"><?php echo htmlspecialchars($userName); ?></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="pages/profile.php"><i class="bi bi-person-circle me-2"></i> Profile</a></li>
              <li><a class="dropdown-item text-danger" href="pages/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
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
          $productDesc = $row['description'] ?? 'No description available.';
        ?>
        <div class="col-sm-6 col-md-4 product-item" data-category="<?= $productCategory ?>" data-name="<?= $productName ?>" data-price="<?= $productPrice ?>">
          <div class="product-card position-relative">
            <a href="#" 
              class="open-product-modal"
              data-bs-toggle="modal"
              data-bs-target="#productModal"
              data-id="<?= $row['id'] ?>"
              data-name="<?= htmlspecialchars($productName) ?>"
              data-image="<?= $productImage ?>"
              data-price="<?= $productPrice ?>"
              data-description="<?= htmlspecialchars($productDesc) ?>"
              data-stock="<?= $productStock ?>">
              <img src="<?= $productImage ?>" alt="<?= $productName ?>" class="img-fluid">
            </a>
            <!-- Price removed here -->
            <div class="position-absolute top-0 end-0 m-2">
              <span class="badge bg-secondary">Stock: <?= $productStock ?></span>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
    </section>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body row">
        <div class="col-md-6">
          <img id="modalProductImage" src="" alt="" class="img-fluid w-100">
        </div>
        <div class="col-md-6">
          <h4 class="text-danger">₱<span id="modalProductPrice"></span></h4>
          <p id="modalProductDescription"></p>
          <p><strong>Stock:</strong> <span id="modalProductStock"></span></p>
          <button id="modalAddToCartBtn" class="btn btn-primary mt-2" data-id="">Add to Cart</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Notification -->
<div id="cart-notify" style="display:none;position:fixed;top:80px;right:30px;z-index:9999;" class="alert alert-success shadow">Added to cart!</div>

<!-- Footer -->
<footer class="footer mt-5 text-center">
  <p>&copy; 2025 Hookcraft Avenue. All rights reserved.</p>
</footer>

<?php include('../pages/login_modal.php'); ?>

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

  // Modal data population
  document.querySelectorAll('.open-product-modal').forEach(link => {
    link.addEventListener('click', function () {
      document.getElementById('productModalLabel').innerText = this.dataset.name;
      document.getElementById('modalProductImage').src = this.dataset.image;
      document.getElementById('modalProductPrice').innerText = parseFloat(this.dataset.price).toFixed(2);
      document.getElementById('modalProductDescription').innerText = this.dataset.description;
      document.getElementById('modalProductStock').innerText = this.dataset.stock;
      document.getElementById('modalAddToCartBtn').setAttribute('data-id', this.dataset.id);
    });
  });

  // Modal Add to Cart
  document.getElementById('modalAddToCartBtn').addEventListener('click', function () {
    const productId = this.getAttribute('data-id');
    const cartCount = document.getElementById('cart-count');

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
    });
  });

  window.addEventListener('DOMContentLoaded', () => {
    priceSlider.value = 1000;
    maxPriceText.textContent = '1000';
  });
</script>
</body>
</html>
