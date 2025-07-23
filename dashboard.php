<?php
include 'includes/db.php'; // uses $conn (MySQLi)
include 'includes/sidebar.php';
include 'includes/header.php';

// Get total users
$usersResult = $conn->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $usersResult->fetch_assoc()['total'];

// Get total orders
$ordersResult = $conn->query("SELECT COUNT(*) as total FROM orders");
$totalOrders = $ordersResult->fetch_assoc()['total'];

// Get total products
$productsResult = $conn->query("SELECT COUNT(*) as total FROM products");
$totalProducts = $productsResult->fetch_assoc()['total'];

// Get total revenue
$revenueResult = $conn->query("SELECT SUM(total) as total FROM orders WHERE payment_status = 'Successful'");
$totalRevenue = $revenueResult->fetch_assoc()['total'] ?? 0;

// Get cart count
$cartResult = $conn->query("SELECT COUNT(*) as total FROM cart");
$cartCount = $cartResult->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Hookcraft Avenue</title>
  <link rel="stylesheet" href="../hookcraftavenue/asset/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
    }
    .main-content {
      padding: 30px;
      background: #f4f4f4;
      min-height: 100vh;
    }
    .dashboard-header {
      margin-bottom: 30px;
    }
    .dashboard-header h1 {
      font-size: 28px;
      color: #2c3e50;
      margin-bottom: 10px;
    }
    .dashboard-header p {
      color: #7f8c8d;
    }
    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    .card {
      background: #fff;
      padding: 20px;
      border-left: 4px solid #3498db;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card h3 {
      margin: 0 0 10px;
      color: #2c3e50;
    }
    .card p {
      font-size: 24px;
      font-weight: bold;
      color: #34495e;
    }
    .card.revenue { border-left-color: #9b59b6; }
    .card.cart { border-left-color: #f39c12; }
  </style>
</head>
<body>

<div class="main-content">
  <div class="dashboard-header">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h1>
    <p>Welcome to Hookcraft Avenue Flower Shop Management System</p>
  </div>

  <div class="card-container">
    <div class="card">
      <h3>Total Users</h3>
      <p><?= number_format($totalUsers) ?></p>
    </div>
    <div class="card">
      <h3>Total Orders</h3>
      <p><?= number_format($totalOrders) ?></p>
    </div>
    <div class="card">
      <h3>Total Products</h3>
      <p><?= number_format($totalProducts) ?></p>
    </div>
    <div class="card revenue">
      <h3>Total Revenue</h3>
      <p>₱<?= number_format($totalRevenue, 2) ?></p>
    </div>
    <div class="card cart">
      <h3>Active Carts</h3>
      <p><?= number_format($cartCount) ?></p>
    </div>
  </div>
</div>

</body>
</html>
