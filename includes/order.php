<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Upload new payment proof
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id']) && isset($_FILES["payment_proof"])) {
  $orderId = $_POST['order_id'];
  $targetDir = __DIR__ . '/../uploads/';
  $filename = basename($_FILES["payment_proof"]["name"]);
  $targetFile = $targetDir . $filename;

  if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $targetFile)) {
    $stmt = $conn->prepare("UPDATE orders SET payment_proof=?, payment_status='Successful' WHERE id=?");
    $stmt->bind_param("si", $filename, $orderId);
    $stmt->execute();
  }
}

// Mark as unsuccessful
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_unsuccessful'])) {
  $unsuccessfulId = $_POST['order_id'];
  $stmt = $conn->prepare("UPDATE orders SET payment_status='Unsuccessful' WHERE id=?");
  $stmt->bind_param("i", $unsuccessfulId);
  $stmt->execute();
}

// Handle edit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_status'])) {
  $orderId = $_POST['order_id'];
  $newStatus = $_POST['new_status'];
  $updateQuery = "UPDATE orders SET payment_status=?";
  $params = [$newStatus];
  $types = "s";

  if (!empty($_FILES["new_proof"]["name"])) {
    $targetDir = __DIR__ . '/../uploads/';
    $filename = basename($_FILES["new_proof"]["name"]);
    $targetFile = $targetDir . $filename;

    if (move_uploaded_file($_FILES["new_proof"]["tmp_name"], $targetFile)) {
      $updateQuery .= ", payment_proof=?";
      $params[] = $filename;
      $types .= "s";
    }
  }

  $updateQuery .= " WHERE id=?";
  $params[] = $orderId;
  $types .= "i";

  $stmt = $conn->prepare($updateQuery);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
}

$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$methodFilter = $_GET['method'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders - Hookcraft Avenue</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../asset/dashboard.css">
  <style>
    .d-flex.flex-column.gap-2 button,
    .d-flex.flex-column.gap-2 form {
      width: 100%;
    }
  </style>
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
      <form method="get" class="mb-4">
        <div class="row g-2 align-items-end">
          <div class="col-md-4">
            <label class="form-label fw-bold mb-1">Search Customer</label>
            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold mb-1">Payment Status</label>
            <select name="status" class="form-select">
              <option value="">All</option>
              <option value="Pending" <?= $statusFilter == 'Pending' ? 'selected' : '' ?>>Pending</option>
              <option value="Successful" <?= $statusFilter == 'Successful' ? 'selected' : '' ?>>Successful</option>
              <option value="Unsuccessful" <?= $statusFilter == 'Unsuccessful' ? 'selected' : '' ?>>Unsuccessful</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold mb-1">Payment Method</label>
            <select name="method" class="form-select">
              <option value="">All</option>
              <option value="GCash" <?= $methodFilter == 'GCash' ? 'selected' : '' ?>>GCash</option>
              <option value="PayPal" <?= $methodFilter == 'PayPal' ? 'selected' : '' ?>>PayPal</option>
            </select>
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-light w-100"><i class="fas fa-search"></i> Filter</button>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
          </div>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>Order ID</th>
              <th>Customer Name</th>
              <th>Products</th>
              <th>Address</th>
              <th>Phone</th>
              <th>Total (₱)</th>
              <th>Payment Proof</th>
              <th>Date</th>
              <th>Method</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $query = "SELECT * FROM orders WHERE 1=1";
          $params = [];
          $types = '';

          if (!empty($search)) {
            $query .= " AND customer_name LIKE ?";
            $params[] = "%$search%";
            $types .= 's';
          }

          if (!empty($statusFilter)) {
            $query .= " AND payment_status = ?";
            $params[] = $statusFilter;
            $types .= 's';
          }

          if (!empty($methodFilter)) {
            $query .= " AND payment_method = ?";
            $params[] = $methodFilter;
            $types .= 's';
          }

          $query .= " ORDER BY created_at DESC";

          $stmt = $conn->prepare($query);
          if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
          }
          $stmt->execute();
          $result = $stmt->get_result();

          while ($row = $result->fetch_assoc()) {
            $orderId = $row['id'];
            $modalId = "uploadModal$orderId";
            $editModalId = "editModal$orderId";
            $proofModalId = "proofModal$orderId";

            // 🆕 Get products with quantity
            $productList = '';
            $productQuery = $conn->prepare("
              SELECT p.name, oi.quantity 
              FROM order_item oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?
            ");
            $productQuery->bind_param("i", $orderId);
            $productQuery->execute();
            $productResult = $productQuery->get_result();
            while ($product = $productResult->fetch_assoc()) {
              $productList .= htmlspecialchars($product['name']) . " (x" . (int)$product['quantity'] . "), ";
            }
            $productList = rtrim($productList, ", ");

            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
            echo "<td>$productList</td>";
            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
            echo "<td>₱" . number_format($row['total'], 2) . "</td>";

            // Payment proof
            if (!empty($row['payment_proof'])) {
              echo "<td><a href='#' data-bs-toggle='modal' data-bs-target='#$proofModalId'>View</a></td>";
              echo "<div class='modal fade' id='$proofModalId'><div class='modal-dialog'><div class='modal-content'>
                    <div class='modal-header'><h5 class='modal-title'>Proof</h5><button class='btn-close' data-bs-dismiss='modal'></button></div>
                    <div class='modal-body text-center'><img src='../uploads/" . htmlspecialchars($row['payment_proof']) . "' class='img-fluid rounded shadow'></div>
                    </div></div></div>";
            } else {
              echo "<td><span class='text-muted'>None</span></td>";
            }

            echo "<td>" . date("F j, Y", strtotime($row['created_at'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
            echo "<td>" . htmlspecialchars($row['payment_status']) . "</td>";

            // Action buttons
            echo "<td><div class='d-flex flex-column gap-2'>";
            if ($row['payment_status'] === 'Pending') {
              echo "
                <button class='btn btn-success btn-sm' data-bs-toggle='modal' data-bs-target='#$modalId'><i class='fas fa-upload'></i> Upload</button>
                <form method='POST' onsubmit=\"return confirm('Mark this order as unsuccessful?');\">
                  <input type='hidden' name='order_id' value='$orderId'>
                  <button type='submit' name='mark_unsuccessful' class='btn btn-danger btn-sm'><i class='fas fa-times'></i> Unsuccessful</button>
                </form>
              ";
            } else {
              echo "
                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#$editModalId'>
                  <i class='fas fa-edit'></i> Edit
                </button>
              ";
            }
            echo "</div></td>";
            echo "</tr>";

            // Upload Modal
            echo "
              <div class='modal fade' id='$modalId'>
                <div class='modal-dialog'>
                  <div class='modal-content'>
                    <form method='POST' enctype='multipart/form-data'>
                      <div class='modal-header'><h5 class='modal-title'>Upload Proof</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                      </div>
                      <div class='modal-body'>
                        <input type='hidden' name='order_id' value='$orderId'>
                        <input type='file' name='payment_proof' class='form-control' required accept='image/*'>
                      </div>
                      <div class='modal-footer'>
                        <button class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                        <button type='submit' class='btn btn-primary'>Upload</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>";

            // Edit Modal
            echo "
              <div class='modal fade' id='$editModalId'>
                <div class='modal-dialog'>
                  <div class='modal-content'>
                    <form method='POST' enctype='multipart/form-data'>
                      <div class='modal-header'><h5 class='modal-title'>Edit Order</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                      </div>
                      <div class='modal-body'>
                        <input type='hidden' name='order_id' value='$orderId'>
                        <div class='mb-2'>
                          <label class='form-label'>Change Status</label>
                          <select name='new_status' class='form-select' required>
                            <option value='Pending' " . ($row['payment_status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                            <option value='Successful' " . ($row['payment_status'] == 'Successful' ? 'selected' : '') . ">Successful</option>
                            <option value='Unsuccessful' " . ($row['payment_status'] == 'Unsuccessful' ? 'selected' : '') . ">Unsuccessful</option>
                          </select>
                        </div>
                        <div class='mb-2'>
                          <label class='form-label'>Replace Payment Proof (optional)</label>
                          <input type='file' name='new_proof' class='form-control' accept='image/*'>
                        </div>
                      </div>
                      <div class='modal-footer'>
                        <button class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                        <button type='submit' name='edit_status' class='btn btn-primary'>Save</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>";
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
