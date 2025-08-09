<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /hookcraftavenue/index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$sql = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_name = $user ? htmlspecialchars($user['name']) : "Guest";

// FIX: Define these variables for navbar
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $user_name;
$userProfilePic = $userProfilePic ?? '../asset/images/default-profile.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Personal Settings</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../asset/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f7ececff;
      font-family: 'Poppins', sans-serif;
    }
    .sidebar {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      padding: 30px 20px;
      min-height: 100vh;
    }
    .sidebar img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
      cursor: pointer;
      border: 3px solid #f9c2d1;
    }
    .sidebar h3 {
      margin-bottom: 20px;
      font-size: 18px;
      color: #333;
    }
    .sidebar button, .sidebar a button {
      display: block;
      width: 100%;
      margin: 10px 0;
      padding: 10px;
      border: none;
      background-color: #f9c2d1;
      color: #000;
      font-weight: 500;
      cursor: pointer;
      text-align: left;
      padding-left: 20px;
      border-radius: 5px;
      transition: background-color 0.3s;
      font-size: 16px;
      text-decoration: none;
    }
    .sidebar button:hover, .sidebar a button:hover {
      background-color: #f199b6;
    }
    .settings-header {
      background-color: #f9c2d1;
      padding: 10px 20px;
      border-radius: 20px;
      font-weight: 500;
      border: none;
      cursor: pointer;
      display: inline-block;
      margin-bottom: 20px;
      font-size: 18px;
      width: 100%;
      text-align: center;
      transition: background-color 0.3s ease;
    }
    .settings-header:hover {
      background-color: #f199b6;
    }
    .personal-setting, .notification-setting {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      margin-bottom: 30px;
    }
    .form-group label {
      font-weight: 600;
      margin-bottom: 6px;
      font-size: 14px;
    }
    .form-group input {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }
    .save-button {
      margin-top: 10px;
      padding: 12px 30px;
      background-color: #f17fb5;
      border: none;
      color: white;
      border-radius: 25px;
      cursor: pointer;
      font-weight: 500;
      width: 100%;
      font-size: 16px;
    }
    #fileInput { display: none; }
  </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="../index.php">
      <img src="../asset/images/logo.jpg" alt="Logo" width="35" height="35" class="rounded-circle me-2">
      HookcraftAvenue
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="../index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>
      <form class="d-flex me-3" role="search">
        <input type="text" id="searchInput" class="form-control" placeholder="Search products..." style="max-width: 250px;">
        <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
      </form>
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
                <?php echo isset($cart_count) ? $cart_count : 0; ?>
              </span>
            </a>
        </li>
        <!-- User dropdown/login -->
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

<div class="container-fluid mt-4">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
      <div class="sidebar text-center">
        <img id="userImage" src="https://via.placeholder.com/100" alt="User Image" onclick="changeProfile()" title="Click to change profile picture" />
        <a href="profile.php"><button><i class="fa fa-home"></i> My Account</button></a>
        <a href="purchase_history.php"><button><i class="fa fa-history"></i> Purchase History</button></a>
        <a href="track.php"><button><i class="fa fa-truck"></i> Track Order</button></a>
      </div>
    </div>
    <!-- Main Content -->
    <div class="col-md-9">
      <div class="personal-setting mb-4">
        <button class="settings-header" onclick="togglePersonalForm()">⚙️ Personal Setting</button>
        <form id="personalForm" style="display:none;">
          <div class="form-group mb-3">
            <label>First Name</label>
            <input type="text" class="form-control" placeholder="Enter first name" />
          </div>
          <div class="form-group mb-3">
            <label>Last Name</label>
            <input type="text" class="form-control" placeholder="Enter last name" />
          </div>
          <div class="form-group mb-3">
            <label>Birthday</label>
            <input type="date" class="form-control" />
          </div>
          <div class="form-group mb-3">
            <label>Phone Number</label>
            <input type="text" class="form-control" placeholder="Enter phone number" />
          </div>
          <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" class="form-control" placeholder="Enter email" />
          </div>
          <div class="form-group mb-3">
            <label>Address</label>
            <input type="text" class="form-control" placeholder="Enter address" />
          </div>
        </form>
      </div>
      <div class="notification-setting">
        <h3>Notification Setting</h3>
        <div class="toggle-group d-flex justify-content-between align-items-center mb-3">
          <span><strong>Email Notification</strong><br>Receive notification via Email</span>
          <label class="toggle-switch">
            <input type="checkbox" checked />
            <span class="slider"></span>
          </label>
        </div>
        <div class="toggle-group d-flex justify-content-between align-items-center mb-3">
          <span><strong>Order Updates</strong><br>Get notified about order status changes</span>
          <label class="toggle-switch">
            <input type="checkbox" checked />
            <span class="slider"></span>
          </label>
        </div>
        <div class="toggle-group d-flex justify-content-between align-items-center mb-3">
          <span><strong>Promotion & Offers</strong><br>Receive promotional emails and special offers</span>
          <label class="toggle-switch">
            <input type="checkbox" checked />
            <span class="slider"></span>
          </label>
        </div>
        <div class="toggle-group d-flex justify-content-between align-items-center mb-3">
          <span><strong>Newsletters</strong><br>Subscribe to our monthly newsletters</span>
          <label class="toggle-switch">
            <input type="checkbox" />
            <span class="slider"></span>
          </label>
        </div>
        <button class="save-button">Save</button>
      </div>
    </div>
  </div>
</div>

<input type="file" id="fileInput" accept="image/*" onchange="updateImage(event)" />

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function changeProfile() {
    document.getElementById('fileInput').click();
  }
  function updateImage(event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('userImage').src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  }
  function togglePersonalForm() {
    const form = document.getElementById('personalForm');
    if (form.style.display === 'flex' || form.style.display === '') {
      form.style.display = 'none';
    } else {
      form.style.display = 'flex';
    }
  }
</script>
</body>
</html>
