<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders - Hookcraft Avenue</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../asset/dashboard.css">
</head>
<body>

  <?php include __DIR__ . '/sidebar.php'; ?>
  <?php include __DIR__ . '/header.php'; ?>

  <div class="main-content p-4">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Order List</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-dark">
              <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Total (₱)</th>
                <th>Payment Proof</th>
                <th>Date</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              include __DIR__ . '/../includes/db.php';
              session_start();

              // Fetch orders with new structure
              $sql = "SELECT id, customer_name, address, phone, total, payment_proof, created_at, payment_method, payment_status FROM orders ORDER BY created_at DESC";

              $result = $conn->query($sql);

              if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $row["id"] . "</td>";
                  echo "<td>" . htmlspecialchars($row["customer_name"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["address"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                  echo "<td>₱" . number_format($row["total"], 2) . "</td>";
                  // Payment proof as image or link if exists
                  if (!empty($row["payment_proof"])) {
                    echo "<td><a href='../uploads/" . htmlspecialchars($row["payment_proof"]) . "' target='_blank'>View</a></td>";
                  } else {
                    echo "<td class='text-muted'>None</td>";
                  }
                  echo "<td>" . date("F j, Y", strtotime($row["created_at"])) . "</td>";
                  echo "<td>" . htmlspecialchars($row["payment_method"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["payment_status"]) . "</td>";
                  echo "<td>
                          <button class='btn btn-success btn-sm' title='Mark Complete'><i class='fas fa-check'></i></button>
                          <button class='btn btn-danger btn-sm' title='Cancel'><i class='fas fa-times'></i></button>
                        </td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='10' class='text-muted'>No orders found.</td></tr>";
              }

              $conn->close();
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
