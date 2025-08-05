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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Personal Settings</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background-color: #f7ececff;
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
    .topbar .top-icons {
      display: flex;
      align-items: center;
    }
    .topbar .top-icons button {
      background: none;
      border: none;
      color: inherit;
      font: inherit;
      cursor: pointer;
      margin-left: 20px;
      font-size: 16px;
    }
    /* Main layout */
    .main {
      display: flex;
      flex: 1;
      height: calc(100vh - 80px);
      overflow: hidden;
    }
    /* Sidebar */
    .sidebar {
      width: 250px;
      background-color: #fff;
      padding: 20px;
      text-align: center;
      border-right: 1px solid #ddd;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .sidebar img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
      cursor: pointer;
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

    /* Side-settings stacked vertically, full width */
    .side-settings {
      display: flex;
      flex-direction: column;
      padding: 20px;
      background-color: #fff;
      overflow-y: auto;
      flex: 1;
      gap: 40px;
    }

    /* Each section full width */
    .personal-setting, .notification-setting {
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    /* Personal Setting Button */
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

    /* Personal Form */
    #personalForm {
      display: none;
      flex-direction: column;
      gap: 20px;
    }
    .form-group {
      display: flex;
      flex-direction: column;
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

    /* Notification Settings */
    .notification-setting h3 {
      margin-bottom: 15px;
      font-size: 20px;
    }
    .toggle-group {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    .toggle-group span {
      max-width: 75%;
      font-size: 14px;
    }
    .toggle-switch {
      position: relative;
      width: 50px;
      height: 25px;
    }
    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    .slider {
      position: absolute;
      cursor: pointer;
      background-color: #ccc;
      border-radius: 25px;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      transition: 0.4s;
      border: 1px solid #bbb;
    }
    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: 0.4s;
      border-radius: 50%;
    }
    .toggle-switch input:checked + .slider {
      background-color: #f17fb5;
    }
    .toggle-switch input:checked + .slider:before {
      transform: translateX(24px);
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

    /* Hide file input */
    #fileInput {
      display: none;
    }

  </style>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div class="logo">🌸 HookcraftAvenue</div>
      <div class="top-icons">
        <button onclick="goHome()"><i class="fa fa-home"></i> Home</button>
        <button onclick="logout()"><i class="fa fa-sign-out-alt"></i> Logout</button>
      </div>
    </div>

    <div class="main">
      <div class="sidebar">
        <img id="userImage" src="https://via.placeholder.com/100" alt="User Image" onclick="changeProfile()" title="Click to change profile picture" />
        <a href="profile.php"><button><i class="fa fa-home"></i> My Account</button></a>
        <a href="purchase_history.php"><button><i class="fa fa-history"></i> Purchase History</button></a>
        <a href="track.php"><button><i class="fa fa-truck"></i> Track Order</button></a>
      </div>

      <div class="side-settings">
        <!-- Personal Setting Section -->
        <div class="personal-setting">
          <button class="settings-header" onclick="togglePersonalForm()">⚙️ Personal Setting</button>
          <form id="personalForm">
            <div class="form-group">
              <label>First Name</label>
              <input type="text" placeholder="Enter first name" />
            </div>
            <div class="form-group">
              <label>Last Name</label>
              <input type="text" placeholder="Enter last name" />
            </div>
            <div class="form-group">
              <label>Birthday</label>
              <input type="date" />
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" placeholder="Enter phone number" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" placeholder="Enter email" />
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" placeholder="Enter address" />
            </div>
          </form>
        </div>

        <!-- Notification Setting Section -->
        <div class="notification-setting">
          <h3>Notification Setting</h3>
          <div class="toggle-group">
            <span><strong>Email Notification</strong><br>Receive notification via Email</span>
            <label class="toggle-switch">
              <input type="checkbox" checked />
              <span class="slider"></span>
            </label>
          </div>
          <div class="toggle-group">
            <span><strong>Order Updates</strong><br>Get notified about order status changes</span>
            <label class="toggle-switch">
              <input type="checkbox" checked />
              <span class="slider"></span>
            </label>
          </div>
          <div class="toggle-group">
            <span><strong>Promotion & Offers</strong><br>Receive promotional emails and special offers</span>
            <label class="toggle-switch">
              <input type="checkbox" checked />
              <span class="slider"></span>
            </label>
          </div>
          <div class="toggle-group">
            <span><strong>Newsletters</strong><br>Subscribe to our monthly newsletters</span>
            <label class="toggle-switch">
              <input type="checkbox" />
              <span class="slider"></span>
            </label>
          </div>
          <button class="save-button">Save</button>
        </div>
      </div>

      <div class="content">
        <!-- You can keep this empty or add extra content -->
      </div>
    </div>
  </div>

  <input type="file" id="fileInput" accept="image/*" onchange="updateImage(event)" />

  <script>
    function goHome() {
      alert("Redirecting to Home...");
      window.location.href = 'index.php';
    }

    function logout() {
      alert("Logging out...");
      window.location.href = 'index.php';
    }

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
      if (form.style.display === 'flex') {
        form.style.display = 'none';
      } else {
        form.style.display = 'flex';
      }
    }

    document.addEventListener("DOMContentLoaded", () => {
      document.getElementById('personalForm').style.display = 'none';
    });
  </script>
</body>
</html>
