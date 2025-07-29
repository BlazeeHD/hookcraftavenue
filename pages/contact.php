<?php
session_start();
include('login_modal.php');
include('signup_modal.php');

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
$cart_count = array_sum($_SESSION['cart']);
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Facebook Feed - HookcraftAvenue</title>
  <link rel="icon" href="../asset/images/logo.jpg" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --primary-pink: #ff6b9d;
      --light-pink: #ffeef5;
      --accent-pink: #ff8fab;
      --dark-pink: #e91e63;
      --text-dark: #2c3e50;
      --text-light: #6c757d;
      --white: #ffffff;
      --shadow: rgba(255, 107, 157, 0.15);
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, var(--light-pink) 0%, var(--white) 100%);
      min-height: 100vh;
    }

    /* Navbar Styles */
    .navbar {
      background: rgba(255, 255, 255, 0.95) !important;
      backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(255, 107, 157, 0.1);
      transition: all 0.3s ease;
    }

    .navbar-brand {
      color: var(--primary-pink) !important;
      font-weight: 700;
      font-size: 1.5rem;
      transition: transform 0.3s ease;
    }

    .navbar-brand:hover {
      transform: scale(1.05);
    }

    .navbar-brand img {
      border: 2px solid var(--primary-pink);
      transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
      transform: rotate(360deg);
    }

    .nav-link {
      color: var(--text-dark) !important;
      font-weight: 500;
      position: relative;
      transition: all 0.3s ease;
      margin: 0 0.5rem;
    }

    .nav-link:hover, .nav-link.active {
      color: var(--primary-pink) !important;
    }

    .nav-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: -5px;
      left: 50%;
      background: linear-gradient(90deg, var(--primary-pink), var(--accent-pink));
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }

    .nav-link:hover::after, .nav-link.active::after {
      width: 100%;
    }

    /* Search Styles */
    .search-container {
      position: relative;
    }

    .form-control {
      border: 2px solid transparent;
      background: var(--light-pink);
      border-radius: 25px;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: var(--primary-pink);
      box-shadow: 0 0 0 0.2rem rgba(255, 107, 157, 0.25);
      background: var(--white);
    }

    .btn-outline-secondary {
      border: 2px solid var(--primary-pink);
      color: var(--primary-pink);
      border-radius: 25px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
      background: var(--primary-pink);
      border-color: var(--primary-pink);
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
    }

    /* Cart Badge */
    .cart-icon {
      position: relative;
      transition: transform 0.3s ease;
    }

    .cart-icon:hover {
      transform: scale(1.1);
    }

    .badge {
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% { transform: scale(1) translateX(-50%); }
      50% { transform: scale(1.1) translateX(-50%); }
      100% { transform: scale(1) translateX(-50%); }
    }

    /* Hero Section */
    .hero-section {
      background: linear-gradient(135deg, var(--primary-pink) 0%, var(--accent-pink) 100%);
      color: white;
      padding: 4rem 0 3rem;
      position: relative;
      overflow: hidden;
    }

    .hero-section::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      animation: float 20s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }

    .hero-content {
      position: relative;
      z-index: 2;
    }

    .hero-title {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 1rem;
      text-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .hero-subtitle {
      font-size: 1.2rem;
      opacity: 0.9;
      margin-bottom: 2rem;
    }

    /* Facebook Feed Section */
    .facebook-feed {
      padding: 5rem 0;
      position: relative;
    }

    .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 1rem;
      position: relative;
      display: inline-block;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-pink), var(--accent-pink));
      border-radius: 2px;
    }

    .section-subtitle {
      color: var(--text-light);
      font-size: 1.1rem;
      margin-bottom: 3rem;
    }

    .facebook-container {
      background: var(--white);
      border-radius: 20px;
      padding: 2.5rem;
      box-shadow: 0 15px 50px var(--shadow);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255, 107, 157, 0.1);
    }

    .facebook-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-pink), var(--accent-pink));
    }

    .facebook-container::after {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 100px;
      height: 100px;
      background: radial-gradient(circle, rgba(255, 107, 157, 0.1) 0%, transparent 70%);
      border-radius: 50%;
    }

    .facebook-container:hover {
      transform: translateY(-8px);
      box-shadow: 0 25px 70px rgba(255, 107, 157, 0.25);
    }

    /* Facebook Widget Styling */
    .facebook-widget-container {
      width: 100%;
      max-width: 600px;
      margin: 0 auto;
      display: flex;
      justify-content: center;
    }

    .fb-page {
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      width: 100% !important;
      max-width: 600px !important;
    }

    .fb-page span {
      width: 100% !important;
    }

    .fb-page iframe {
      width: 100% !important;
      min-width: 600px !important;
    }

    /* Social Icons */
    .social-links {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-top: 2rem;
    }

    .social-link {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, var(--primary-pink), var(--accent-pink));
      color: white;
      border-radius: 50%;
      font-size: 1.2rem;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .social-link:hover {
      transform: translateY(-3px) scale(1.1);
      box-shadow: 0 10px 25px rgba(255, 107, 157, 0.4);
      color: white;
    }

    /* Loading Animation */
    .loading-animation {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 600px;
      flex-direction: column;
    }

    .spinner {
      width: 50px;
      height: 50px;
      border: 4px solid var(--light-pink);
      border-top: 4px solid var(--primary-pink);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: 1rem;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Footer */
    .footer {
      background: linear-gradient(135deg, var(--text-dark) 0%, #34495e 100%);
      color: white;
      padding: 3rem 0 2rem;
      margin-top: 5rem;
    }

    .footer-content {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 2rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2rem;
      }
      
      .section-title {
        font-size: 2rem;
      }
      
      .facebook-container {
        padding: 1rem;
        margin: 0 1rem;
      }
      
      .facebook-widget-container {
        max-width: 100%;
      }
      
      .fb-page iframe {
        min-width: 320px !important;
        width: 100% !important;
      }
      
      .fb-page {
        max-width: 100% !important;
      }
    }

    /* Engagement Cards */
    .engagement-card {
      background: var(--white);
      border-radius: 15px;
      transition: all 0.3s ease;
      border: 1px solid rgba(255, 107, 157, 0.1);
      height: 100%;
    }

    .engagement-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(255, 107, 157, 0.15);
      border-color: var(--primary-pink);
    }

    .icon-wrapper {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, var(--light-pink), rgba(255, 255, 255, 0.8));
      border-radius: 50%;
      margin: 0 auto;
      transition: transform 0.3s ease;
    }

    .engagement-card:hover .icon-wrapper {
      transform: scale(1.1) rotate(5deg);
    }

    .engagement-stats {
      padding-top: 1rem;
      border-top: 1px solid rgba(255, 107, 157, 0.1);
      margin-top: 1rem;
    }

    /* Button Styles */
    .btn-outline-primary {
      border: 2px solid #1877f2;
      color: #1877f2;
      transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
      background: #1877f2;
      border-color: #1877f2;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(24, 119, 242, 0.3);
    }
    .fade-in {
      opacity: 0;
      transform: translateY(30px);
      animation: fadeInUp 0.6s ease forwards;
    }

    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
  </style>
</head>
<body>

<!-- Facebook SDK -->
<div id="fb-root"></div>
<script async defer crossorigin="anonymous"
  src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v19.0">
</script>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
  <div class="container">
    <a class="navbar-brand" href="#">
      <img src="../asset/images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
      HookcraftAvenue
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link active" href="gallery.php">Facebook</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>

      <div class="search-container me-3">
        <form class="d-flex" role="search">
          <input type="text" id="searchInput" class="form-control me-2" placeholder="Search products..." style="max-width: 250px;">
          <button class="btn btn-outline-secondary" type="submit">
            <i class="bi bi-search"></i>
          </button>
        </form>
      </div>

      <ul class="navbar-nav flex-row">
        <li class="nav-item me-3">
          <a class="nav-link cart-icon position-relative" href="#" onclick="handleCartClick()">
            <i class="bi bi-cart fs-5"></i>
            <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              0
            </span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="handleLoginClick()">
            <i class="bi bi-person fs-5"></i>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8 mx-auto text-center hero-content">
        <h1 class="hero-title fade-in stagger-1">Stay Connected</h1>
        <p class="hero-subtitle fade-in stagger-2">Follow our latest crochet creations and updates on Facebook</p>
        <div class="social-links fade-in stagger-3">
          <a href="https://www.facebook.com/CrochetbyAlys" target="_blank" class="social-link">
            <i class="bi bi-facebook"></i>
          </a>
          <a href="#facebook-feed" class="social-link">
            <i class="bi bi-arrow-down"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Facebook Feed Section -->
<section id="facebook-feed" class="facebook-feed">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title fade-in">Latest from Our Facebook Page</h2>
      <p class="section-subtitle fade-in stagger-1">Discover our newest crochet patterns, tutorials, and community highlights</p>
    </div>
    
    <div class="row justify-content-center">
      <div class="col-lg-10 col-md-12">
        <div class="facebook-container fade-in stagger-2">
          <div class="text-center mb-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="bi bi-facebook fs-3 me-2" style="color: #1877f2;"></i>
              <h4 class="mb-0" style="color: var(--text-dark);">Hookcraft Avenue</h4>
            </div>
            <p class="text-muted small mb-0">Follow us for daily crochet inspiration & tutorials</p>
          </div>
          
          <div id="fb-loading" class="loading-animation">
            <div class="spinner"></div>
            <p class="text-muted">Loading Facebook feed...</p>
          </div>
          
          <div class="facebook-widget-container">
            <div class="fb-page"
                 data-href="https://www.facebook.com/CrochetbyAlys"
                 data-tabs="timeline"
                 data-width="600"
                 data-height="700"
                 data-small-header="false"
                 data-adapt-container-width="true"
                 data-hide-cover="false"
                 data-show-facepile="true"
                 onload="hideLoading()">
              <blockquote cite="https://www.facebook.com/CrochetbyAlys" class="fb-xfbml-parse-ignore">
                <a href="https://www.facebook.com/CrochetbyAlys">Hookcraft Avenue</a>
              </blockquote>
            </div>
          </div>
          
          <div class="text-center mt-3">
            <a href="https://www.facebook.com/CrochetbyAlys" target="_blank" 
               class="btn btn-outline-primary btn-sm rounded-pill px-4">
              <i class="bi bi-facebook me-2"></i>Visit Our Page
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Additional Social Engagement -->
    <div class="row mt-5">
      <div class="col-md-4 text-center fade-in stagger-1">
        <div class="engagement-card p-4 h-100">
          <div class="icon-wrapper mb-3">
            <i class="bi bi-heart-fill fs-1 text-danger"></i>
          </div>
          <h5 style="color: var(--text-dark);">Like Our Page</h5>
          <p class="text-muted">Stay updated with our latest posts and announcements</p>
          <div class="engagement-stats">
            <small class="text-muted">1.3K followers</small>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-center fade-in stagger-2">
        <div class="engagement-card p-4 h-100">
          <div class="icon-wrapper mb-3">
            <i class="bi bi-share-fill fs-1" style="color: var(--primary-pink);"></i>
          </div>
          <h5 style="color: var(--text-dark);">Share Your Work</h5>
          <p class="text-muted">Tag us in your crochet creations using our patterns</p>
          <div class="engagement-stats">
            <small class="text-muted">#HookcraftAvenue</small>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-center fade-in stagger-3">
        <div class="engagement-card p-4 h-100">
          <div class="icon-wrapper mb-3">
            <i class="bi bi-chat-dots-fill fs-1" style="color: var(--accent-pink);"></i>
          </div>
          <h5 style="color: var(--text-dark);">Join the Community</h5>
          <p class="text-muted">Connect with fellow crochet enthusiasts and share tips</p>
          <div class="engagement-stats">
            <small class="text-muted">Active community</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-content text-center">
      <p>&copy; 2025 Hookcraft Avenue. All rights reserved. Made with <i class="bi bi-heart-fill text-danger"></i> for crochet lovers.</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  // Hide loading animation when Facebook feed loads
  function hideLoading() {
    const loading = document.getElementById('fb-loading');
    if (loading) {
      loading.style.display = 'none';
    }
  }

  // Wait for FB SDK to load, then hide loading
  window.fbAsyncInit = function() {
    setTimeout(hideLoading, 2000);
  };

  // Fallback to hide loading after 5 seconds
  setTimeout(hideLoading, 5000);

  // Cart functionality
  function handleCartClick() {
    // Add your cart logic here
    console.log('Cart clicked');
  }

  // Login functionality  
  function handleLoginClick() {
    // Add your login logic here
    console.log('Login clicked');
  }

  // Navbar scroll effect
  window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
      navbar.style.background = 'rgba(255, 255, 255, 0.98)';
      navbar.style.boxShadow = '0 2px 20px rgba(255, 107, 157, 0.1)';
    } else {
      navbar.style.background = 'rgba(255, 255, 255, 0.95)';
      navbar.style.boxShadow = 'none';
    }
  });

  // Intersection Observer for animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
      }
    });
  }, observerOptions);

  // Observe all fade-in elements
  document.addEventListener('DOMContentLoaded', function() {
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(el => {
      el.style.animationPlayState = 'paused';
      observer.observe(el);
    });
  });

  // Cart count animation
  function updateCartCount(count) {
    const cartCount = document.getElementById('cart-count');
    cartCount.textContent = count;
    cartCount.style.animation = 'none';
    setTimeout(() => {
      cartCount.style.animation = 'pulse 2s infinite';
    }, 10);
  }
</script>
</body>
</html>
