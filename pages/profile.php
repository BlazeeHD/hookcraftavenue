<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}
$user_id = $_SESSION['user_id'];
// Fetch user info
$stmt = $conn->prepare('SELECT name, email, phone FROM users WHERE id=?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone);
$stmt->fetch();
$stmt->close();
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_name = trim($_POST['name']);
  $new_email = trim($_POST['email']);
  $new_phone = trim($_POST['phone']);
  if ($new_name && $new_email && $new_phone) {
    $stmt = $conn->prepare('UPDATE users SET name=?, email=?, phone=? WHERE id=?');
    $stmt->bind_param('sssi', $new_name, $new_email, $new_phone, $user_id);
    $stmt->execute();
    $_SESSION['user_name'] = $new_name;
    header('Location: profile.php?updated=1');
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile - Hookcraft Avenue</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2>My Profile</h2>
  <?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">Profile updated!</div>
  <?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Phone</label>
      <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update Profile</button>
  </form>
  <a href="logout.php" class="btn btn-link mt-3">Logout</a>
</div>
</body>
</html>
