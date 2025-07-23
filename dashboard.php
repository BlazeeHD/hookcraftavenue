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
      font-family: 'Georgia', 'Times New Roman', serif;
      margin: 0;
      background: linear-gradient(135deg, #ffeef8 0%, #fff0fa 50%, #ffe8f4 100%);
      min-height: 100vh;
    }
    
    .main-content {
      padding: 40px;
      min-height: 100vh;
      position: relative;
    }
    
    /* Subtle floral background pattern */
    .main-content::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: 
        radial-gradient(circle at 20% 30%, rgba(255, 182, 193, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255, 20, 147, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 60% 20%, rgba(255, 105, 180, 0.08) 0%, transparent 50%);
      pointer-events: none;
    }
    
    .dashboard-header {
      margin-bottom: 40px;
      text-align: center;
      position: relative;
      z-index: 1;
    }
    
    .dashboard-header h1 {
      font-size: 36px;
      color: #d1477a;
      margin-bottom: 15px;
      text-shadow: 0 2px 4px rgba(209, 71, 122, 0.1);
      font-weight: 300;
      letter-spacing: 1px;
    }
    
    .dashboard-header h1 i {
      color: #ff69b4;
      margin-right: 15px;
    }
    
    .dashboard-header p {
      color: #8b5a6b;
      font-size: 18px;
      font-style: italic;
      margin: 0;
    }
    
    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      position: relative;
      z-index: 1;
    }
    
    .card {
      background: linear-gradient(145deg, #ffffff 0%, #fef9fc 100%);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 
        0 8px 25px rgba(255, 105, 180, 0.1),
        0 4px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.4s ease;
      border: 2px solid transparent;
      position: relative;
      overflow: hidden;
    }
    
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #ff69b4, #ffb6c1, #ffc0cb);
    }
    
    .card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 
        0 15px 35px rgba(255, 105, 180, 0.2),
        0 8px 25px rgba(0, 0, 0, 0.1);
      border-color: #ffb6c1;
    }
    
    .card h3 {
      margin: 0 0 15px;
      color: #8b4a6b;
      font-size: 18px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .card p {
      font-size: 32px;
      font-weight: bold;
      color: #d1477a;
      margin: 0;
      text-shadow: 0 1px 3px rgba(209, 71, 122, 0.1);
    }
    
    /* Individual card colors */
    .card:nth-child(1)::before {
      background: linear-gradient(90deg, #ff69b4, #ff1493);
    }
    
    .card:nth-child(2)::before {
      background: linear-gradient(90deg, #ffb6c1, #ff69b4);
    }
    
    .card:nth-child(3)::before {
      background: linear-gradient(90deg, #ffc0cb, #ffb6c1);
    }
    
    .card.revenue::before {
      background: linear-gradient(90deg, #da70d6, #ba55d3);
    }
    
    .card.cart::before {
      background: linear-gradient(90deg, #ff91a4, #ff69b4);
    }
    
    .card.revenue {
      background: linear-gradient(145deg, #ffffff 0%, #f8f0ff 100%);
    }
    
    .card.cart {
      background: linear-gradient(145deg, #ffffff 0%, #fff5f8 100%);
    }
    
    /* Add flower icons to cards */
    .card h3::before {
      content: '🌸';
      margin-right: 8px;
      font-size: 16px;
    }
    
    .card.revenue h3::before {
      content: '🌺';
    }
    
    .card.cart h3::before {
      content: '🌷';
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
      .main-content {
        padding: 20px;
      }
      
      .dashboard-header h1 {
        font-size: 28px;
      }
      
      .card {
        padding: 20px;
      }
      
      .card p {
        font-size: 24px;
      }
    }
    
    /* Add subtle animation */
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-5px); }
    }
    
    .card:nth-child(odd) {
      animation: float 6s ease-in-out infinite;
    }
    
    .card:nth-child(even) {
      animation: float 6s ease-in-out infinite reverse;
    }
  </style>
</head>
<body>

<div class="main-content">
  <div class="dashboard-header">
    <h1><i class="fas fa-seedling"></i> Dashboard Overview</h1>
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