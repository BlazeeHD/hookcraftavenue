<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users - Hookcraft Avenue</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome (for icons if needed) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Custom Styles (Optional) -->
  <link rel="stylesheet" href="../asset/dashboard.css">
</head>
<body>

  <?php include __DIR__ . '/sidebar.php'; ?>
  <?php include __DIR__ . '/header.php'; ?>

  <!-- Main Content -->
  <div class="main-content p-4">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">User List</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Password</th>
                <th>Created At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              include __DIR__ . '/../includes/db.php';
              session_start();

              $sql = "SELECT id, name, email, password, created_at FROM users";
              $result = $conn->query($sql);

              if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $row["id"] . "</td>";
                  echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["password"]) . "</td>";
                  echo "<td>" . date("F j, Y", strtotime($row["created_at"])) . "</td>";
                  echo "<td>
                          <button class='btn btn-success btn-sm'><i class='fas fa-check'></i></button>
                          <button class='btn btn-danger btn-sm'><i class='fas fa-times'></i></button>
                        </td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='6' class='text-center text-muted'>No users found.</td></tr>";
              }

              $conn->close();
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
