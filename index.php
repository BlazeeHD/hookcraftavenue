<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('pages/login_modal.php');
include('pages/signup_modal.php');

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']);
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Flower Shop - HookcraftAvenue</title>
  <link rel="icon" href="asset/images/logo.jpg" type="image/png">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="asset/styles.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
      <img src="asset/images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
      HookcraftAvenue
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="pages/shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="pages/about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="pages/gallery.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>

      <form class="d-flex me-3" role="search">
        <input type="text" id="searchInput" class="form-control" placeholder="Search products..." style="max-width: 250px;">
        <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
      </form>

      <ul class="navbar-nav flex-row">
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
        <?php if ($isLoggedIn): ?>
          <li class="nav-item">
            <a class="nav-link" href="pages/profile.php">
              <i class="bi bi-person-circle fs-5"></i> <?php echo htmlspecialchars($userName); ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="pages/logout.php">
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

<!-- Hero -->
<section class="hero">
  <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between">
    <div class="hero-content">
      <h1>Elevate your moments with our handcrafted floral creations</h1>
      <p>Celebrate Beauty, one petal at a time.</p>
      <a href="pages/shop.php" class="shop-now">Shop Now</a>
    </div>
    <img src="asset/images/hero-image.png" alt="Flower Hero" class="hero-img img-fluid mt-4 mt-md-0">
  </div>
</section>

<!-- Categories -->
<section class="categories">
  <div class="container">
    <h1>Shop by Category</h1>
    <div class="category-grid">
      <div class="category-item">
        <img src="asset/images/birthday.jpg" alt="Birthday Bouquet">
        <h3>Birthday Bouquets</h3>
      </div>
      <div class="category-item">
        <img src="asset/images/casual.jpg" alt="Casual Bouquet">
        <h3>Casual Bouquets</h3>
      </div>
      <div class="category-item">
        <img src="asset/images/tiny.jpg" alt="Tiny Bouquets">
        <h3>Tiny Bouquets</h3>
      </div>
    </div>
  </div>
</section>

<!-- About -->
<section class="about">
  <div class="container about-content">
    <div class="about-text">
      <h2>About Us</h2>
      <p>
        At Hookcraft Avenue, we are passionate about delivering freshly picked flowers, carefully handcrafted into beautiful arrangements that bring joy to every occasion...
      </p>
    </div>
    <div class="about-image">
      <img src="asset/images/about-flower.jpg" alt="Flower 1" class="img-top">
      <img src="asset/images/about-flower1.jpg" alt="Flower 2" class="img-bottom">
    </div>
  </div>
</section>

<!-- Gallery -->
<section class="gallery">
    <div class="container">
      <h2>Gallery</h2>
   <div class="gallery-grid">
  <div class="gallery-item">
    <img src="asset/images/gallery1.jpg" alt="Red Rose Delight">
    <div class="overlay">
      <strong>Red Rose Delight</strong><br>
      ₱899 – Elegant red roses arranged to express love and romance.
    </div>
  </div>
  <div class="gallery-item">
    <img src="asset/images/gallery2.jpg" alt="Sunshine Mix">
    <div class="overlay">
      <strong>Sunshine Mix</strong><br>
      ₱749 – Bright seasonal bouquet perfect for casual gifts.
    </div>
  </div>
  <div class="gallery-item">
    <img src="asset/images/gallery3.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
   <div class="gallery-item">
    <img src="asset/images/gallery4.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
   <div class="gallery-item">
    <img src="asset/images/gallery5.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
     <div class="gallery-item">
    <img src="asset/images/gallery6.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
     <div class="gallery-item">
    <img src="asset/images/gallery7.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
     <div class="gallery-item">
    <img src="asset/images/gallery8.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
   

</div>

    </div>
  </section>


<!-- Customize -->
<section class="customize py-4">
  <div class="container">
    <h2 class="fs-3 mb-4">Customize Your Own</h2>
    <div class="row justify-content-center g-3">
      <div class="col-4 col-sm-3 col-md-2">
        <img src="asset/images/custom1.png" class="img-fluid rounded">
      </div>
      <div class="col-4 col-sm-3 col-md-2">
        <img src="asset/images/custom2.png" class="img-fluid rounded">
      </div>
      <div class="col-4 col-sm-3 col-md-2">
        <img src="asset/images/custom3.png" class="img-fluid rounded">
      </div>
    </div>
    <a href="pages/customization.php" class="btn btn-sm mt-4" style="background-color: #ff6fa4; color: white; border-radius: 20px;">Customize Now</a>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-content">
      <p>&copy; 2025 Hookcraft Avenue. All rights reserved.</p>
    </div>
  </div>
</footer>

<!-- Auto-show login modal if redirected -->
<?php if (isset($_SESSION['login_required'])): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
      loginModal.show();
    });
  </script>
  <?php unset($_SESSION['login_required']); ?>
<?php endif; ?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
