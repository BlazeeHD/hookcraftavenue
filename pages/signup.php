<?php
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $name, $email, $password);

  if ($stmt->execute()) {
    echo "<script>alert('Signup successful! Please login.'); window.location.href='index.php';</script>";
  } else {
    echo "<script>alert('Signup failed: Email may already exist.'); window.location.href='index.php';</script>";
  }
}
?>
<!-- signup_modal.php -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- Sign Up Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
      <div class="row g-0">
        <!-- Left Panel with Logo -->
        <div class="col-md-6 d-flex align-items-center justify-content-center" style="background-color: #fbd3e9;">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 250px; height: 250px; background-color: #fcb3d7;">
            <img src="../asset/images/logo.jpg" alt="Hookcraft Logo" style="width: 100%;">
          </div>
        </div>

        <!-- Right Panel with Form -->
        <div class="col-md-6 p-4" style="background: url('https://www.transparenttextures.com/patterns/pw-maze-white.png') repeat; background-color: rgba(255,255,255,0.85);">
          <h3 class="text-center mb-4" style="font-weight: bold;">Sign Up</h3>
          <form action="signup.php" method="POST">
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="fas fa-envelope"></i></span>
              <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
              <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn w-100" style="background-color: #fcb3d7; color: #333; font-weight: bold;">Create Account</button>
            <div class="text-center mt-3" style="font-size: 0.9em;">
              Already have an account? 
              <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
