<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');

// Function to get product by ID from products table
function getProductById($conn, $productId, $categoryId) {
    $productQuery = $conn->prepare("SELECT * FROM products WHERE id = ? AND category_id = ?");
    $productQuery->bind_param("ii", $productId, $categoryId);
    $productQuery->execute();
    $productResult = $productQuery->get_result();
    
    if ($productResult->num_rows === 0) {
        return null;
    }
    
    return $productResult->fetch_assoc();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['category_id'])) {
    $isLoggedIn = isset($_SESSION['user_id']);
    if (!$isLoggedIn) {
        echo "not_logged_in";
        exit;
    }
    
    $productId = (int)$_POST['product_id'];
    $categoryId = (int)$_POST['category_id'];
    
    // Get product details
    $product = getProductById($conn, $productId, $categoryId);
    
    if (!$product) {
        echo "product_not_found";
        exit;
    }
    
    $productStock = $product['stock'];
    $cartKey = $categoryId . '_' . $productId; // Use category_productid as key
    
    // Get current quantity in cart for this item
    $currentQty = 0;
    if (isset($_SESSION['cart'][$cartKey]) && is_array($_SESSION['cart'][$cartKey])) {
        $currentQty = $_SESSION['cart'][$cartKey]['quantity'];
    }
    
    // Check if we can add more
    if ($currentQty < $productStock) {
        // Store cart item as array with proper structure
        $_SESSION['cart'][$cartKey] = [
            'product_id' => $productId,
            'category_id' => $categoryId,
            'quantity' => $currentQty + 1
        ];
        
        // Debug logging
        error_log("Added to cart: " . print_r($_SESSION['cart'][$cartKey], true));
    } else {
        echo "max";
        exit;
    }
    
    // Calculate new cart count
    $new_cart_count = 0;
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['quantity'])) {
            $new_cart_count += $item['quantity'];
        }
    }
    
    error_log("New cart count: $new_cart_count");
    echo $new_cart_count;
    exit;
}

// Calculate cart count for display
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['quantity'])) {
            $cart_count += $item['quantity'];
        }
    }
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

// Load all categories
$categories = [];
$catQuery = $conn->query("SELECT id, name FROM categories ORDER BY name");
if ($catQuery) {
    while ($cat = $catQuery->fetch_assoc()) {
        $categories[$cat['id']] = $cat['name'];
    }
}
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
    .product-card {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
    }
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .product-card img {
      width: 100%;
      height: 250px;
      object-fit: cover;
    }
    .product-info {
      padding: 15px;
    }
    .product-price {
      color: #d63384;
      font-weight: bold;
      font-size: 1.2em;
    }
    .category-badge {
      background: linear-gradient(45deg, #007bff, #6610f2);
      color: white;
      font-size: 0.8em;
    }
    .stock-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 2;
    }
    .out-of-stock {
      opacity: 0.6;
      position: relative;
    }
    .out-of-stock::after {
      content: "OUT OF STOCK";
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(220, 53, 69, 0.9);
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: bold;
      z-index: 3;
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
        <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="shop.php">Shop</a></li>
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
            <a class="nav-link position-relative" href="../pages/cart.php">
          <?php else: ?>
            <a class="nav-link position-relative" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
          <?php endif; ?>
              <i class="bi bi-cart fs-5"></i>
              <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo $cart_count; ?>
              </span>
            </a>
        </li>

        <!-- User dropdown -->
        <?php if ($isLoggedIn): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle p-0 border-0 bg-transparent" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?php echo $userProfilePic ?? '../asset/images/default-profile.png'; ?>" alt="Profile" width="35" height="35" class="rounded-circle" style="object-fit: cover;">
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-2 p-3 text-center" aria-labelledby="userDropdown" style="min-width: 220px;">
              <li class="mb-2">
                <img src="<?php echo $userProfilePic ?? '../asset/images/default-profile.png'; ?>" alt="Profile" width="60" height="60" class="rounded-circle shadow" style="object-fit: cover;">
              </li>
              <li class="fw-bold"><?php echo htmlspecialchars($userName); ?></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="../pages/profile.php"><i class="bi bi-person-circle me-2"></i> Profile</a></li>
              <li><a class="dropdown-item text-danger" href="../pages/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
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
        <li class="list-group-item">
          <a href="#" onclick="filterProducts('all')" class="text-decoration-none">
            <i class="fas fa-th-large me-2"></i>All Products
          </a>
        </li>
        <?php foreach ($categories as $catId => $catName): ?>
        <li class="list-group-item">
          <a href="#" onclick="filterProducts('<?= $catId ?>')" class="text-decoration-none">
            <i class="fas fa-tag me-2"></i><?= htmlspecialchars($catName) ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
      
      <div class="filter-price">
        <h3><i class="fas fa-filter me-2"></i>Filter by Price</h3>
        <input type="range" class="form-range" min="20" max="1000" value="1000" id="priceRange">
        <div class="d-flex justify-content-between">
          <span>₱20</span>
          <span>₱<span id="maxPrice">1000</span></span>
        </div>
      </div>
    </aside>

    <section class="col-md-9">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Our Products</h2>
        <div class="text-muted">
          <span id="product-count">0</span> products found
        </div>
      </div>
      
      <div class="row g-4" id="product-list">
        <?php
        // Fetch all products from unified products table with category names
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  ORDER BY p.name";
        $result = $conn->query($query);
        
        $productCount = 0;
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $productId = $row['id'];
                $productName = $row['name'] ?? 'Unnamed Product';
                $productImage = $row['image'] ?? '';
                $productPrice = $row['price'] ?? 0;
                $categoryId = $row['category_id'];
                $categoryName = $row['category_name'];
                $productStock = $row['stock'] ?? 0;
                $productDesc = !empty($row['description']) ? $row['description'] : 'High-quality ' . $categoryName . ' product.';
                
                // Use full image path
                $imagePath = !empty($productImage) ? '../asset/images/' . $productImage : '../asset/images/placeholder.jpg';
                $productCount++;
        ?>
        <div class="col-sm-6 col-md-4 col-lg-3 product-item" 
             data-category="<?= $categoryId ?>" 
             data-name="<?= strtolower($productName) ?>" 
             data-price="<?= $productPrice ?>">
          <div class="product-card <?= $productStock <= 0 ? 'out-of-stock' : '' ?>" 
               data-bs-toggle="modal"
               data-bs-target="#productModal"
               data-id="<?= $productId ?>"
               data-category-id="<?= $categoryId ?>"
               data-name="<?= htmlspecialchars($productName) ?>"
               data-image="<?= $imagePath ?>"
               data-price="<?= $productPrice ?>"
               data-description="<?= htmlspecialchars($productDesc) ?>"
               data-stock="<?= $productStock ?>"
               data-category-name="<?= htmlspecialchars($categoryName) ?>">
            
            <div class="stock-badge">
              <?php if ($productStock > 0): ?>
                <span class="badge bg-success">Stock: <?= $productStock ?></span>
              <?php else: ?>
                <span class="badge bg-danger">Out of Stock</span>
              <?php endif; ?>
            </div>
            
            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($productName) ?>" class="img-fluid">
            
            <div class="product-info">
              <h6 class="mb-2"><?= htmlspecialchars($productName) ?></h6>
              <div class="d-flex justify-content-between align-items-center">
                <span class="product-price">₱<?= number_format($productPrice, 2) ?></span>
                <span class="badge category-badge"><?= htmlspecialchars($categoryName) ?></span>
              </div>
            </div>
          </div>
        </div>
        <?php 
            }
        } else {
        ?>
        <div class="col-12 text-center py-5">
          <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
          <h4 class="text-muted">No products available</h4>
          <p class="text-muted">Please check back later or contact us for more information.</p>
        </div>
        <?php } ?>
      </div>
    </section>
  </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body row">
        <div class="col-md-6">
          <img id="modalProductImage" src="" alt="" class="img-fluid w-100 rounded">
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <span id="modalCategoryBadge" class="badge category-badge"></span>
          </div>
          <h4 class="text-danger mb-3">₱<span id="modalProductPrice"></span></h4>
          <p id="modalProductDescription" class="mb-3"></p>
          <div class="mb-3">
            <strong>Stock Available: </strong>
            <span id="modalProductStock" class="badge bg-info"></span>
          </div>
          <button id="modalAddToCartBtn" class="btn btn-primary btn-lg w-100" data-id="" data-category-id="">
            <i class="fas fa-cart-plus me-2"></i>Add to Cart
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Notification -->
<div id="cart-notify" style="display:none;position:fixed;top:80px;right:30px;z-index:9999;" class="alert alert-success shadow-lg">
  <i class="fas fa-check-circle me-2"></i>Added to cart!
</div>

<!-- Footer -->
<footer class="footer mt-5 bg-dark text-light py-4">
  <div class="container text-center">
    <p class="mb-0">&copy; 2025 Hookcraft Avenue. All rights reserved.</p>
  </div>
</footer>

<?php include('../pages/login_modal.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
  // Update product count
  function updateProductCount() {
    const visibleProducts = document.querySelectorAll('.product-item[style=""], .product-item:not([style])').length;
    document.getElementById('product-count').textContent = visibleProducts;
  }

  // Filter products by category
  function filterProducts(category) {
    document.querySelectorAll('.product-item').forEach(item => {
      const shouldShow = (category === 'all' || item.dataset.category === category);
      item.style.display = shouldShow ? 'block' : 'none';
    });
    updateProductCount();
    
    // Update active category
    document.querySelectorAll('.list-group-item a').forEach(link => {
      link.classList.remove('fw-bold', 'text-primary');
    });
    event.target.classList.add('fw-bold', 'text-primary');
  }

  // Search functionality
  document.getElementById('searchInput').addEventListener('input', function () {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
      const shouldShow = item.dataset.name.includes(keyword);
      item.style.display = shouldShow ? 'block' : 'none';
    });
    updateProductCount();
  });

  // Price filter
  const priceSlider = document.getElementById('priceRange');
  const maxPriceText = document.getElementById('maxPrice');

  priceSlider.addEventListener('input', function () {
    const maxPrice = parseInt(this.value);
    maxPriceText.textContent = maxPrice;
    document.querySelectorAll('.product-item').forEach(item => {
      const itemPrice = parseFloat(item.dataset.price);
      const shouldShow = (itemPrice <= maxPrice);
      item.style.display = shouldShow ? 'block' : 'none';
    });
    updateProductCount();
  });

  // Modal functionality
  document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function (e) {
      // Don't open modal if product is out of stock
      if (this.classList.contains('out-of-stock')) {
        e.preventDefault();
        return;
      }
      
      document.getElementById('productModalLabel').innerText = this.dataset.name;
      document.getElementById('modalProductImage').src = this.dataset.image;
      document.getElementById('modalProductPrice').innerText = parseFloat(this.dataset.price).toFixed(2);
      document.getElementById('modalProductDescription').innerText = this.dataset.description;
      document.getElementById('modalProductStock').innerText = this.dataset.stock;
      document.getElementById('modalCategoryBadge').innerText = this.dataset.categoryName;
      document.getElementById('modalAddToCartBtn').setAttribute('data-id', this.dataset.id);
      document.getElementById('modalAddToCartBtn').setAttribute('data-category-id', this.dataset.categoryId);
      
      // Disable add to cart button if out of stock
      const addToCartBtn = document.getElementById('modalAddToCartBtn');
      if (parseInt(this.dataset.stock) <= 0) {
        addToCartBtn.disabled = true;
        addToCartBtn.innerHTML = '<i class="fas fa-times me-2"></i>Out of Stock';
        addToCartBtn.classList.remove('btn-primary');
        addToCartBtn.classList.add('btn-secondary');
      } else {
        addToCartBtn.disabled = false;
        addToCartBtn.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Add to Cart';
        addToCartBtn.classList.remove('btn-secondary');
        addToCartBtn.classList.add('btn-primary');
      }
    });
  });

  // Add to cart functionality
  document.getElementById('modalAddToCartBtn').addEventListener('click', function () {
    const productId = this.getAttribute('data-id');
    const categoryId = this.getAttribute('data-category-id');
    const cartCount = document.getElementById('cart-count');

    fetch('shop.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `product_id=${productId}&category_id=${categoryId}`
    })
    .then(res => res.text())
    .then(data => {
      console.log('Cart response:', data); // Debug log
      
      if (data === "not_logged_in") {
        alert("Please log in to add products to your cart.");
        return;
      }
      if (data === "max") {
        alert("You've reached the maximum stock for this product.");
        return;
      }
      if (data === "product_not_found") {
        alert("Product not found.");
        return;
      }

      // Show success notification
      document.getElementById('cart-notify').style.display = 'block';
      cartCount.innerText = data;
      cartCount.classList.add('cart-bounce');
      
      // Hide modal
      bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
      
      setTimeout(() => {
        document.getElementById('cart-notify').style.display = 'none';
        cartCount.classList.remove('cart-bounce');
      }, 2000);
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred. Please try again.');
    });
  });

  // Initialize
  window.addEventListener('DOMContentLoaded', () => {
    updateProductCount();
    priceSlider.value = 1000;
    maxPriceText.textContent = '1000';
  });
  
  // Search form prevent default
  document.querySelector('form[role="search"]').addEventListener('submit', function(e) {
    e.preventDefault();
  });
</script>
</body>
</html>