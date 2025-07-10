<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gallery - HookcraftAvenue</title>
  <link rel="icon" href="images/logo.jpg" type="image/png">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Open+Sans&display=swap" rel="stylesheet" />

  <!-- Custom Styles -->
  <link rel="stylesheet" href="styles.css" />
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

<!-- Bootstrap Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


<!-- Gallery Section -->
<section class="gallery py-5">
  <div class="container">
    <h2 class="mb-4 text-center">Our Flower Gallery</h2>
    <div class="row g-3">
      <div class="col-6 col-md-4 col-lg-3">
        <img src="images/gallery1.jpg" alt="Gallery 1" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="images/gallery2.jpg" alt="Gallery 2" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="images/gallery3.png" alt="Gallery 3" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="images/gallery4.png" alt="Gallery 4" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="images/gallery5.png" alt="Gallery 5" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="images/gallery6.png" alt="Gallery 6" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="images/gallery7.png" alt="Gallery 7" class="img-fluid rounded">
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <img src="images/gallery8.png" alt="Gallery 8" class="img-fluid rounded">
      </div>
    </div>
  </div>
</section>

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
