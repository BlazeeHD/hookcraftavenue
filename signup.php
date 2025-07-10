<?php
include 'db.php';

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
