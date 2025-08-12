<?php
session_start();

// Preserve cart data before logout
$cart_data = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Clear user-related session data instead of destroying entire session
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
// Add any other user-related session variables you want to clear
// unset($_SESSION['user_profile_pic']);
// unset($_SESSION['user_role']);

// Restore cart data
$_SESSION['cart'] = $cart_data;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Logging Out - Hookcraft Avenue</title>
  <meta http-equiv="refresh" content="3;url=../index.php"> <!-- Redirect in 3 seconds -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #fbd3e9, #bb377d);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      border-radius: 1rem;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>

<div class="card text-center p-5" style="max-width: 500px;">
  <i class="fas fa-sign-out-alt fa-4x text-danger mb-3"></i>
  <h3 class="mb-3">You have been logged out</h3>
  <p class="mb-4">Redirecting to home page in a few seconds...</p>
  <a href="../index.php" class="btn btn-outline-dark">
    <i class="fas fa-home"></i> Go to Homepage Now
  </a>
</div>

</body>
</html>