<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
$cart_count = array_sum($_SESSION['cart']);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';


$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Flower Shop - About</title>
  <link rel="icon" href="../asset/images/logo.jpg" type="image/png">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <link rel="stylesheet" href="../asset/styles.css">
</head>
<body>

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


<!-- Include Login and Signup Modals -->
<?php include 'login_modal.php'; ?>
<?php include 'signup_modal.php'; ?>

<main class="about-page">
  <!-- Section 1 -->
  <section class="about-block container d-flex flex-column flex-md-row align-items-center" data-aos="fade-right">
    <div class="about-text me-md-4" style="margin-bottom: 150px;">
      <h2>What is Hookcraft Avenue?</h2>
      <p>
        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.
      </p>
    </div>
    <div class="about-gallery" style="margin-bottom: 150px;">
      <img src="../asset/images/about1.jpg" alt="Gallery 1" />
    </div>
  </section>

  <h2 class="section-title text-center" data-aos="fade-down">Work Of Hookcraft Avenue</h2>

  <section class="about-gallery-grid container text-center" data-aos="zoom-in-up">
    <img src="../asset/images/gallery1.jpg" alt="Event 1" />
    <img src="../asset/images/about4.png" alt="Event 2" />
    <img src="../asset/images/about5.png" alt="Event 3" />
    <img src="../asset/images/about6.png" alt="Event 4" />
  </section>

  <!-- Section 2 -->
  <section class="about-block container d-flex flex-column flex-md-row align-items-center reverse" data-aos="fade-left">
    <div class="about-gallery me-md-4">
      <img src="../asset/images/about7.png" alt="Gallery 3" />
      <img src="../asset/images/about8.png" alt="Gallery 4" />
    </div>
    <div class="about-text">
      <h2>What we do?</h2>
      <p>
        We provide handcrafted bouquets and creative gift items for all kinds of celebrations.
        Whether it’s a birthday, anniversary, graduation, or just a sweet gesture—our blooms bring joy.
      </p>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="testimonials" data-aos="fade-up">
    <h2 class="mb-4">What Our Customers Say</h2>
    <div class="testimonial-card">
      <img src="../asset/images/avatar1.png" alt="Client 1" />
      <h5>Anna Lopez</h5>
      <p>"Beautiful flowers and wonderful customer service! They made my anniversary extra special."</p>
    </div>
    <div class="testimonial-card">
      <img src="../asset/images/avatar2.png" alt="Client 2" />
      <h5>Jake Mendoza</h5>
      <p>"Highly recommend Hookcraft Avenue. Fast delivery and such elegant arrangements!"</p>
    </div>
  </section>
</main>

<!-- Back to Top -->
<button id="backToTopBtn" title="Go to top">↑</button>

<!-- Footer -->
<footer class="footer bg-dark text-white text-center py-4 mt-5">
  <div class="container">
    <p class="mb-0">&copy; 2025 Hookcraft Avenue. All rights reserved.</p>
  </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init();
  const backToTopBtn = document.getElementById("backToTopBtn");
  window.onscroll = function () {
    backToTopBtn.style.display = (document.documentElement.scrollTop > 200) ? "block" : "none";
  };
  backToTopBtn.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
</script>
</body>
</html>
