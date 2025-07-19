<?php
session_start();
include __DIR__ . '/../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $name, $hashed_password);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
      $_SESSION['user_id'] = $id;
      $_SESSION['user_name'] = $name;
      echo "<script>alert('Login successful!'); window.location.href='../index.php';</script>";
    } else {
      echo "<script>alert('Incorrect password.'); window.location.href='../index.php';</script>";
    }
  } else {
    echo "<script>alert('User not found.'); window.location.href='../index.php';</script>";
  }
}
?>

<!-- Include FontAwesome & Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
      <div class="row g-0">
        <!-- Left Panel with Logo -->
        <div class="col-md-6 d-flex align-items-center justify-content-center" style="background-color: #fbd3e9;">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 250px; height: 250px; background-color: #fcb3d7;">
            <img src="asset/images/logo.jpg" alt="Hookcraft Logo" style="width: 100%;">
            <!-- Replace with actual logo -->
          </div>
        </div>

        <!-- Right Panel with Form -->
        <div class="col-md-6 p-4" style="background: url('https://www.transparenttextures.com/patterns/pw-maze-white.png') repeat; background-color: rgba(255,255,255,0.85);">
          <h3 class="text-center mb-4" style="font-weight: bold;">Log In</h3>
          <form action="pages/login.php" method="POST">
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
              <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <div>
                <input type="checkbox" id="rememberMe">
                <label for="rememberMe">Remember me</label>
              </div>
              <a href="#" style="font-size: 0.9em;">Forgot Password?</a>
            </div>
            <button type="submit" class="btn w-100" style="background-color: #fcb3d7; color: #333; font-weight: bold;">Login</button>
            <div class="text-center mt-3" style="font-size: 0.9em;">
              Not a member yet?
              <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal" data-bs-dismiss="modal">Create a Account</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
