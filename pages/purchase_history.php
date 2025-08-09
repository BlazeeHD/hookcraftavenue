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
  .content {
      flex: 1;
      background-color: #fff;
      padding: 30px;
    }

    .content h2 {
      margin-bottom: 10px;
    }

    .search-bar {
      margin: 20px 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .search-bar input {
      flex: 1;
      padding: 10px;
      border-radius: 25px;
      border: 1px solid #ccc;
      padding-left: 20px;
    }

    .search-bar button {
      padding: 10px 20px;
      background-color: #f9c2d1;
      border: none;
      border-radius: 25px;
      cursor: pointer;
    }

    .search-bar select {
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: center;
    }

    th {
      background-color: #f9c2d1;
      color: #000;
    }

    .status-delivered {
      background-color: #af7786;
    }

    .status-cancelled {
      background-color: #d3d3d3;
    }

    .status-refunded {
      background-color: #b6acac;
    }

    .fa-eye {
      color: #555;
      cursor: pointer;
    }
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

    <!-- Main Layout -->

<!-- Content -->
      <div class="content">
        <h2>Purchase History</h2>
        <p>View all your previous orders and their details</p>

        <div class="search-bar">
          <input type="text" placeholder="Search orders...">
          <button><i class="fa fa-search"></i></button>
          <select>
            <option value="all">All Orders</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Date</th>
              <th>Item</th>
              <th>Total</th>
              <th>Status</th>
              <th>Invoice</th>
            </tr>
          </thead>
          <tbody>
            <tr class="status-delivered">
              <td>001</td>
              <td>12/16/18</td>
              <td>Flower</td>
              <td>309</td>
              <td>Delivered</td>
              <td><i class="fa fa-eye"></i></td>
            </tr>
            <tr class="status-cancelled">
              <td>002</td>
              <td>12/17/19</td>
              <td>Bag</td>
              <td>1500</td>
              <td>Cancelled</td>
              <td>—</td>
            </tr>
            <tr class="status-refunded">
              <td>003</td>
              <td>12/20/19</td>
              <td>Shoes</td>
              <td>899</td>
              <td>Refunded</td>
              <td><i class="fa fa-eye"></i></td>
            </tr>
          </tbody>
        </table>
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

      