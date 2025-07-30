<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purchased History</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
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

    .topbar button {
      background: none;
      border: none;
      color: inherit;
      font: inherit;
      cursor: pointer;
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
      margin-bottom: 20px;
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

    /* Conditional row colors */
    .status-delivered {
      background-color: #af7786; /* Pink for Delivered */
    }

    .status-cancelled {
      background-color: #d3d3d3; /* Gray for Cancelled */
    }

    .status-refunded {
      background-color: #b6acac; /* White for Refunded */
    }

    .fa-eye {
      color: #555;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div class="logo">🌸 HookcraftAvenue</div>
      <div class="top-icons">
        <button onclick="goToAccount()"><i class="fa fa-user"></i> My Account</button>
        <button onclick="logout()"><i class="fa fa-sign-out-alt"></i> Logout</button>
      </div>
    </div>

    <div class="main">
      <div class="sidebar">
        <img src="https://via.placeholder.com/100" alt="User Profile">
        <button><i class="fa fa-user"></i> My Account</button>
        <button><i class="fa fa-history"></i> Purchase History</button>
        <button><i class="fa fa-truck"></i> Track Order</button>
      </div>

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

  <script>
    // Function to handle "My Account" click
    function goToAccount() {
      alert("Redirecting to My Account...");
      // You can replace the following with an actual page redirect
      window.location.href = '/my-account'; // Replace with the actual URL for your account page
    }

    // Function to handle "Logout" click
    function logout() {
      alert("Logging out...");
      // Implement the logout functionality here (e.g., clearing user session, cookies)
      window.location.href = '/logout'; // Replace with the actual logout URL or process
    }
  </script>
</body>
</html>
