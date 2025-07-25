<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
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
