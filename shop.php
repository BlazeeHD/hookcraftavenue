<?php
include 'db.php';
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
      font-weight: bold;
      color: #333;
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
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background-color: #ffcad4;">
  <div class="container">
    <a class="navbar-brand logo" href="#" style="font-family: 'ARIAL'; color: #333;">HookcraftAvenue</a>
    
    <!-- Hamburger icon -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links -->
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav nav-links">
        <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-3 d-flex justify-content-end align-items-center gap-2 flex-wrap">
  <input type="text" id="searchInput" class="form-control" placeholder="Search products..." style="max-width: 250px;">
  <button class="btn btn-dark" onclick="alert('Cart page under construction')">🛒 Cart</button>
</div>
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
          <input type="range" class="form-range" min="20" max="1000" value="45">
          <div class="d-flex justify-content-between">
            <span>₱70</span>
            <span>₱1000</span>
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
        ?>
        <div class="col-sm-6 col-md-4 product-item" data-category="<?= $productCategory ?>" data-name="<?= $productName ?>" data-price="<?= $productPrice ?>" data-image="<?= $productImage ?>">
          <div class="product-card">
            <img src="<?= $productImage ?>" alt="<?= $productName ?>">
            <p class="product-price">₱<?= number_format($productPrice, 2) ?></p>
            <form action="cart_action.php?action=add" method="post">
              <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
              <button type="submit" class="add-to-cart-btn">Add to Cart</button>
            </form>
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
    const products = document.querySelectorAll('.product-item');
    products.forEach(product => {
      product.style.display = (category === 'all' || product.dataset.category === category) ? 'block' : 'none';
    });
  }
  document.getElementById('searchInput').addEventListener('input', function () {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
      item.style.display = item.dataset.name.toLowerCase().includes(keyword) ? 'block' : 'none';
    });
  });
</script>
</body>
</html>