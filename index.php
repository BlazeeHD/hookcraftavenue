<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Flower Shop - HookcraftAvenue</title>
  <link rel="icon" href="../asset/images/logo.jpg" type="image/png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Open+Sans&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  
  <link rel="stylesheet" href="../hookcraftavenue/asset/styles.css">
</head>
<?php 
include('pages/login.php'); 
include('pages/signup.php'); 
?>

<body>

<!-- E-commerce Navbar naol -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <!-- Brand Logo -->
    <a class="navbar-brand fw-bold" href="#">
      <img src="../hookcraftavenue/asset/images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
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
        <li class="nav-item"><a class="nav-link" href="pages/shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="pages/about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="pages/gallery.php">Gallery</a></li>
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
          <a class="nav-link position-relative" href="pages/cart.php">
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


  <!-- Hero Section -->
  <section class="hero">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between">
      <div class="hero-content">
        <h1>Elevate your moments with our handcrafted floral creations</h1>
        <p>Celebrate Beauty, one petal at a time.</p>
        <a href="shop.php" class="shop-now">Shop Now</a>
      </div>
      <img src="../hookcraftavenue/asset/images/hero-image.png" alt="Flower Hero" class="hero-img img-fluid mt-4 mt-md-0">
    </div>
  </section>

  <!-- Categories Section -->
  <section class="categories">
    <div class="container">
      <h1>Shop by Category</h1>
      <div class="category-grid">
        <div class="category-item">
          <img src="../hookcraftavenue/asset/images/birthday.jpg" alt="Birthday Bouquet">
          <h3>Birthday Bouquets</h3>
        </div>
        <div class="category-item">
          <img src="../hookcraftavenue/asset/images/casual.jpg" alt="Casual Bouquet">
          <h3>Casual Bouquets</h3>
        </div>
        <div class="category-item">
          <img src="../hookcraftavenue/asset/images/tiny.jpg" alt="Tiny Bouquets">
          <h3>Tiny Bouquets</h3>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section class="about">
    <div class="container about-content">
      <div class="about-text">
        <h2>About Us</h2>
        <p>
          At Hookcraft Avenue, we are passionate about delivering freshly picked flowers, carefully handcrafted into beautiful arrangements that bring joy to every occasion. We take pride in our eco-friendly practices, ensuring that each bouquet is both stunning and sustainable. Our team is dedicated to providing custom orders tailored to your special moments, because we believe that every flower tells a story — and your story deserves the perfect bloom.
        </p>
      </div>
      <div class="about-image">
        <img src="../hookcraftavenue/asset/images/about-flower.jpg" alt="Flower 1" class="img-top">
        <img src="../hookcraftavenue/asset/images/about-flower1.jpg" alt="Flower 2" class="img-bottom">
      </div>
    </div>
  </section>

  <!-- Gallery Section -->
  <section class="gallery">
    <div class="container">
      <h2>Gallery</h2>
   <div class="gallery-grid">
  <div class="gallery-item">
    <img src="../hookcraftavenue/asset/images/gallery1.jpg" alt="Red Rose Delight">
    <div class="overlay">
      <strong>Red Rose Delight</strong><br>
      ₱899 – Elegant red roses arranged to express love and romance.
    </div>
  </div>
  <div class="gallery-item">
    <img src="../hookcraftavenue/asset/images/gallery2.jpg" alt="Sunshine Mix">
    <div class="overlay">
      <strong>Sunshine Mix</strong><br>
      ₱749 – Bright seasonal bouquet perfect for casual gifts.
    </div>
  </div>
  <div class="gallery-item">
    <img src="../hookcraftavenue/asset/images/gallery3.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
   <div class="gallery-item">
    <img src="../hookcraftavenue/asset/images/gallery4.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
   <div class="gallery-item">
    <img src="../hookcraftavenue/asset/images/gallery5.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
     <div class="gallery-item">
    <img src="../hookcraftavenue/asset/images/gallery6.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
     <div class="gallery-item">
    <img src="../hookcraftavenue/asset/images/gallery7.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
     <div class="gallery-item">
    <img src="../hookcraftavenue/asset/images/gallery8.png" alt="Tiny Blossom">
    <div class="overlay">
      <strong>Tiny Blossom</strong><br>
      ₱399 – A petite bundle, great for small thoughtful surprises.
    </div>
  </div>
   
  <!-- Add more in same format -->
</div>

    </div>
  </section>

  <!-- Customize Section -->
 <section class="customize py-4">
  <div class="container">
    <h2 class="fs-3 mb-4">Customize Your Own</h2>
    <div class="row justify-content-center g-3">
      <div class="col-4 col-sm-3 col-md-2">
        <img src="../hookcraftavenue/asset/images/custom1.png" alt="Customize 1" class="img-fluid rounded">
      </div>
      <div class="col-4 col-sm-3 col-md-2">
        <img src="../hookcraftavenue/asset/images/custom2.png" alt="Customize 2" class="img-fluid rounded">
      </div>
      <div class="col-4 col-sm-3 col-md-2">
        <img src="../hookcraftavenue/asset/images/custom3.png" alt="Customize 3" class="img-fluid rounded">
      </div>
    </div>
    <a href="#" class="btn btn-sm mt-4" style="background-color: #ff6fa4; color: white; border-radius: 20px;">Customize Now</a>
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


</body>
</html>

