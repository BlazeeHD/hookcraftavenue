<?php
session_start();
include('login_modal.php'); // Modal UI only
include('signup_modal.php'); // Modal UI only
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']);

// User session logic
$cart_count = array_sum($_SESSION['cart']);
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gallery - HookcraftAvenue</title>
  <link rel="icon" href="../asset/images/logo.jpg" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Open+Sans&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../asset/styles.css" />
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


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Gallery Section -->
<section class="gallery py-5">
  <div class="container">
    <h2 class="mb-4 text-center"> Flower Gallery</h2>
    <div class="row g-3">
      <?php
      $images = [
        "gallery1.jpg", "gallery2.jpg", "gallery3.png", "gallery4.png",
        "gallery5.png", "gallery6.png", "gallery7.png", "gallery8.png"
      ];
      foreach ($images as $img) {
        echo '<div class="col-6 col-md-4 col-lg-3">
                <img src="../asset/images/' . $img . '" alt="Gallery" class="img-fluid rounded">
              </div>';
      }
      ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
