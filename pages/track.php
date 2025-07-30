<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Track Order</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #d3bebe;
    }

    .container {
      display: flex;
      flex-direction: column;
      height: 100vh;
    }

    .topbar {
      background-color: #f9c2d1;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .topbar .logo {
      font-size: 24px;
      font-weight: 600;
      color: #d9539f;
    }

    .topbar .top-icons i {
      margin-left: 30px;
      font-size: 18px;
      cursor: pointer;
      color: #555;
    }

    .main {
      display: flex;
      flex: 1;
    }

    .sidebar {
      width: 250px;
      background-color: #fff;
      padding: 20px;
      text-align: center;
    }

    .sidebar img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
    }

    .sidebar button {
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
    }

    .sidebar button:hover {
      background-color: #f199b6;
    }

    .content {
      flex: 1;
      background-color: #fff;
      padding: 30px;
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #f3f3f3;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .order-header h2 {
      font-size: 20px;
      font-weight: 600;
    }

    .order-header .print-button {
      padding: 10px 15px;
      border: none;
      background-color: #f17fb5;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }

    .status-bar {
      display: flex;
      justify-content: space-between;
      margin: 20px 0;
      text-align: center;
    }

    .status-step {
      flex: 1;
    }

    .status-label {
      display: inline-block;
      padding: 10px 15px;
      background-color: #f9c2d1;
      border-radius: 25px;
      font-weight: 500;
      margin-bottom: 5px;
    }

    .status-time {
      font-size: 12px;
      color: #666;
    }

    .order-details {
      background-color: #e4e4e4;
      padding: 30px;
      border-radius: 10px;
      margin-top: 10px;
    }

    .item-row {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .item-row .item-box {
      width: 50px;
      height: 50px;
      background-color: black;
    }

    .item-desc {
      flex: 1;
    }

    .item-price {
      font-weight: 600;
    }

    .status-paid {
      color: green;
      font-weight: 600;
    }

    .info-label {
      margin-bottom: 5px;
      font-size: 14px;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div class="logo">🌸 HookcraftAvenue </div>
      <div class="top-icons">
        <i class="fa fa-user" onclick="openAccountPage()"></i> My Account
        <i class="fa fa-sign-out-alt" onclick="logout()"></i> Logout
      </div>
    </div>

    <div class="main">
      <div class="sidebar">
        <img src="https://via.placeholder.com/100" alt="User">
        <button onclick="openAccountPage()"><i class="fa fa-user"></i> My Account</button>
        <button><i class="fa fa-history"></i> Purchase History</button>
        <button><i class="fa fa-truck"></i> Track Order</button>
        <a href="personal setting.html"><button>Profile</button></a> <!-- Link to Profile Page -->
  <a href="purchase history.html"><button>Settings</button></a> <!-- Link to Settings Page -->
      </div>

      <div class="content">
        <div class="order-header">
          <div>
            <h2>Order ID: #14324</h2>
            <div class="info-label">Date: 07/24/2025</div>
          </div>
          <button class="print-button"><i class="fa fa-print"></i> Print Invoice</button>
        </div>

        <!-- Status Progress with Timestamps -->
        <div class="status-bar">
          <div class="status-step">
            <div class="status-label">Packed</div>
            <div class="status-time">07/23/2025 - 10:15 AM</div>
          </div>
          <div class="status-step">
            <div class="status-label">Picked</div>
            <div class="status-time">07/23/2025 - 1:30 PM</div>
          </div>
          <div class="status-step">
            <div class="status-label">Out for Delivery</div>
            <div class="status-time">07/24/2025 - 8:00 AM</div>
          </div>
          <div class="status-step">
            <div class="status-label">Delivered</div>
            <div class="status-time">07/24/2025 - 1:20 PM</div>
          </div>
        </div>

        <!-- Order Details -->
        <div class="order-details">
          <h3>Item Ordered</h3>
          <div class="item-row">
            <div class="item-box"></div>
            <div class="item-desc">Black</div>
            <div class="item-price">350</div>
            <div class="status-paid">Paid</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Function to open account page
    function openAccountPage() {
      window.location.href = "my-account.html"; // Redirect to the user's account page
    }

    // Function to handle logout
    function logout() {
      // Clear session data or cookies (example)
      sessionStorage.clear();
      localStorage.clear();

      // Redirect to login page
      window.location.href = "login.html";
    }
  </script>
