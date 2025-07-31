<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Personal Settings</title>
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

    .settings-header {
      background-color: #f9c2d1;
      display: inline-block;
      padding: 8px 20px;
      border-radius: 20px;
      margin-bottom: 25px;
      font-weight: 500;
    }

    .form-container {
      display: flex;
      gap: 30px;
    }

    .form-left, .form-right {
      flex: 1;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group input, .form-group label {
      display: block;
      width: 100%;
    }

    .form-group input {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }

    .form-group label {
      margin-bottom: 5px;
      font-size: 14px;
    }

    .form-right h3 {
      margin-bottom: 15px;
    }

    .toggle-group {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .toggle-group span {
      max-width: 80%;
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
      margin-top: 20px;
      padding: 12px 30px;
      background-color: #f17fb5;
      border: none;
      color: white;
      border-radius: 25px;
      cursor: pointer;
      font-weight: 500;
    }

    /* Hidden input for image file */
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
        <!-- Home Button -->
        <button onclick="goHome()"><i class="fa fa-home"></i> Home</button>

        <!-- Logout Button -->
        <button onclick="logout()"><i class="fa fa-sign-out-alt"></i> Logout</button>
      </div>
    </div>

    <div class="sidebar">
      <img id="userImage" src="https://via.placeholder.com/100" alt="User Image">
      
      <!-- Link to My Account Page -->
      <a href="profile.php">
        <button>
          <i class="fa fa-home"></i> My Account
        </button>
      </a>

      <!-- Link to Purchase History Page -->
      <a href="purchase_history.php">
        <button>
          <i class="fa fa-history"></i> Purchase History
        </button>
      </a>

      <!-- Link to Track Order Page -->
      <a href="track.php">
        <button>
          <i class="fa fa-truck"></i> Track Order
        </button>
      </a>
    </div>

    <!-- Content Section with Settings -->
    <div class="content">
      <div class="settings-header">Personal Setting</div>
      <div class="form-container">
        <div class="form-left">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" placeholder="Enter first name">
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" placeholder="Enter last name">
          </div>
          <div class="form-group">
            <label>Birthday</label>
            <input type="date">
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="text" placeholder="Enter phone number">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" placeholder="Enter email">
          </div>
          <div class="form-group">
            <label>Address</label>
            <input type="text" placeholder="Enter address">
          </div>
        </div>

        <div class="form-right">
          <h3>Notification Setting</h3>
          <div class="toggle-group">
            <span><strong>Email Notification</strong><br>Receive notification via Email</span>
            <label class="toggle-switch">
              <input type="checkbox" checked>
              <span class="slider"></span>
            </label>
          </div>
          <div class="toggle-group">
            <span><strong>Order Updates</strong><br>Get notified about order status changes</span>
            <label class="toggle-switch">
              <input type="checkbox" checked>
              <span class="slider"></span>
            </label>
          </div>
          <div class="toggle-group">
            <span><strong>Promotion & Offers</strong><br>Receive promotional emails and special offers</span>
            <label class="toggle-switch">
              <input type="checkbox" checked>
              <span class="slider"></span>
            </label>
          </div>
          <div class="toggle-group">
            <span><strong>Newsletters</strong><br>Subscribe to our monthly newsletters</span>
            <label class="toggle-switch">
              <input type="checkbox">
              <span class="slider"></span>
            </label>
          </div>

          <button class="save-button">Save</button>
        </div>
      </div>
    </div>
  </div>

  <input type="file" id="fileInput" accept="image/*" onchange="updateImage(event)">

  <script>
    // Redirect to Home page
    function goHome() {
      alert("Redirecting to Home...");
      window.location.href = 'index.php'; // Redirect to the homepage (index.php)
    }

    // Logout function
    function logout() {
      alert("Logging out...");
      window.location.href = 'index.php'; // Redirect to the homepage or login page after logging out
    }

    // Function to handle the profile image change
    function changeProfile() {
      document.getElementById('fileInput').click(); // Trigger the file input click
    }

    // Update the profile image
    function updateImage(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('userImage').src = e.target.result; // Update image source
        };
        reader.readAsDataURL(file);
      }
    }
  </script>
</body>
</html>
