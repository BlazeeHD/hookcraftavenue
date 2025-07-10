<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Flower Shop - About</title>
  <link rel="icon" href="images/logo.jpg" type="image/png">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- AOS CSS -->
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <link rel="stylesheet" href="../asset/styles.css">
  <!-- Custom CSS -->
  <style>

  </style>
</head>
<body>

<!-- E-commerce Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <!-- Brand Logo -->
    <a class="navbar-brand fw-bold" href="#">
      <img src="../images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
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


  <!-- Main Content -->
  <main class="about-page">
 

    <!-- Section 1 -->
    <section class="about-block container d-flex flex-column flex-md-row align-items-center" data-aos="fade-right">
      <div class="about-text me-md-4" style="margin-bottom: 150px;">
        <h2>What is Hookcraft Avenue?</h2>
        <p>
          Lorem Ipsum is simply dummy text of the printing and typesetting industry.
          Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.
          It has survived not only five centuries, but also the leap into electronic typesetting.
        </p>
      </div>
      <div class="about-gallery" style="margin-bottom: 150px;">
        <img src="../images/gallery1.jpg" alt="Gallery 1" />
        
      </div>
        
    </section>
     <h2 class="section-title text-center" data-aos="fade-down">Work Of Hookcraft Avenue</h2>

    <!-- Image Grid -->
    <section class="about-gallery-grid container text-center" data-aos="zoom-in-up">
      <img src="../images/gallery1.jpg" alt="Event 1" />
      <img src="images/about4.png" alt="Event 2" />
      <img src="images/about5.png" alt="Event 3" />
      <img src="images/about6.png" alt="Event 4" />
    </section>

    <!-- Section 2 -->
    <section class="about-block container d-flex flex-column flex-md-row align-items-center reverse" data-aos="fade-left">
      <div class="about-gallery me-md-4">
        <img src="images/about7.png" alt="Gallery 3" />
        <img src="images/about8.png" alt="Gallery 4" />
      </div>
      <div class="about-text">
        <h2>What we do?</h2>
        <p>
          We provide handcrafted bouquets and creative gift items for all kinds of celebrations.
          Whether it’s a birthday, anniversary, graduation, or just a sweet gesture—our blooms bring joy.
          From pop-up bazaars to personalized orders, Hookcraft Avenue blooms with love.
        </p>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" data-aos="fade-up">
      <h2 class="mb-4">What Our Customers Say</h2>
      <div class="testimonial-card">
        <img src="images/avatar1.png" alt="Client 1" />
        <h5>Anna Lopez</h5>
        <p>"Beautiful flowers and wonderful customer service! They made my anniversary extra special."</p>
      </div>
      <div class="testimonial-card">
        <img src="images/avatar2.png" alt="Client 2" />
        <h5>Jake Mendoza</h5>
        <p>"Highly recommend Hookcraft Avenue. Fast delivery and such elegant arrangements!"</p>
      </div>
    </section>
  </main>

  <!-- Back to Top Button -->
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
