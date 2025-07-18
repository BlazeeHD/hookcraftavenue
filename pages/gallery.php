<?php
session_start();
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gallery - HookcraftAvenue</title>
  <link rel="icon" href="../asset/images/logo.jpg" type="image/png">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Open+Sans&display=swap" rel="stylesheet" />

  <!-- Custom Styles -->
  <link rel="stylesheet" href="../asset/styles.css" />
</head>
<body>


<!-- E-commerce Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <!-- Brand Logo -->
    <a class="navbar-brand fw-bold" href="#">
      <img src="../asset/images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
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
        <li class="nav-item"><a class="nav-link active" href="../index.php">Home</a></li>
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
            <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?php echo isset($cart_count) ? $cart_count : 0; ?>
            </span>
            <span id="cart-icon-badge" style="display:none"></span>
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

<!-- Bootstrap Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


<!-- Gallery Section -->
<section class="gallery py-5">
  <div class="container">
    <h2 class="mb-4 text-center">Our Flower Gallery</h2>
    <div class="row g-3">
      <div class="col-6 col-md-4 col-lg-3">
        <img src="../asset/images/gallery1.jpg" alt="Gallery 1" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="../asset/images/gallery2.jpg" alt="Gallery 2" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="../asset/images/gallery3.png" alt="Gallery 3" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="../asset/images/gallery4.png" alt="Gallery 4" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="../asset/images/gallery5.png" alt="Gallery 5" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="../asset/images/gallery6.png" alt="Gallery 6" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="../asset/images/gallery7.png" alt="Gallery 7" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="../asset/images/gallery8.png" alt="Gallery 8" class="img-fluid rounded">
      </div>
    </div>
  </div>
</section>



<!-- Footer -->
<footer class="footer mt-5">
  <div class="container">
    <div class="footer-content text-center">
      <p>&copy; 2025 Hookcraft Avenue. All rights reserved.</p>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
