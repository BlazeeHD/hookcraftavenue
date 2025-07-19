<?php
include __DIR__ . '/../includes/db.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders - Hookcraft Avenue</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
              <th>Products</th>
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
            if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id'])) {
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

            $sql = "SELECT id, customer_name, address, phone, total, payment_proof, created_at, payment_method, payment_status FROM orders ORDER BY created_at DESC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $orderId = $row['id'];
                $modalId = "uploadModal" . $orderId;
                $proofModalId = "proofModal" . $orderId;

                // Get product names using correct schema
                $products = [];
                $productQuery = $conn->prepare("
                  SELECT p.name 
                  FROM order_item oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = ?
                ");
                $productQuery->bind_param("i", $orderId);
                $productQuery->execute();
                $productResult = $productQuery->get_result();
                while ($productRow = $productResult->fetch_assoc()) {
                  $products[] = htmlspecialchars($productRow['name']);
                }
                $productList = implode(", ", $products);

                echo "<tr>";
                echo "<td>$orderId</td>";
                echo "<td>" . htmlspecialchars($row["customer_name"]) . "</td>";
                echo "<td>$productList</td>";
                echo "<td>" . htmlspecialchars($row["address"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                echo "<td>₱" . number_format($row["total"], 2) . "</td>";

                if (!empty($row["payment_proof"])) {
                  echo "<td>
                          <a href='#' data-bs-toggle='modal' data-bs-target='#$proofModalId'>View</a>
                          <div class='modal fade' id='$proofModalId' tabindex='-1' aria-labelledby='{$proofModalId}Label' aria-hidden='true'>
                            <div class='modal-dialog modal-dialog-centered'>
                              <div class='modal-content'>
                                <div class='modal-header'>
                                  <h5 class='modal-title' id='{$proofModalId}Label'>Payment Proof</h5>
                                  <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                </div>
                                <div class='modal-body text-center'>
                                  <img src='../uploads/" . htmlspecialchars($row["payment_proof"]) . "' class='img-fluid rounded shadow' alt='Payment Proof'>
                                </div>
                              </div>
                            </div>
                          </div>
                        </td>";
                } else {
                  echo "<td><span class='text-muted'>None</span></td>";
                }

                echo "<td>" . date("F j, Y", strtotime($row["created_at"])) . "</td>";
                echo "<td>" . htmlspecialchars($row["payment_method"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["payment_status"]) . "</td>";
                echo "<td>
                        <button class='btn btn-success btn-sm' data-bs-toggle='modal' data-bs-target='#$modalId'>
                          <i class='fas fa-upload'></i> Upload
                        </button>
                      </td>";
                echo "</tr>";

                // Upload Modal
                echo "
                <div class='modal fade' id='$modalId' tabindex='-1' aria-labelledby='{$modalId}Label' aria-hidden='true'>
                  <div class='modal-dialog'>
                    <div class='modal-content'>
                      <form method='POST' enctype='multipart/form-data'>
                        <div class='modal-header'>
                          <h5 class='modal-title' id='{$modalId}Label'>Upload Payment Proof</h5>
                          <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <div class='modal-body'>
                          <input type='hidden' name='order_id' value='{$orderId}'>
                          <div class='mb-3'>
                            <label for='payment_proof' class='form-label'>Select Image</label>
                            <input type='file' class='form-control' name='payment_proof' accept='image/*' required>
                          </div>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                          <button type='submit' class='btn btn-primary'>Upload</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                ";
              }
            } else {
              echo "<tr><td colspan='11' class='text-muted'>No orders found.</td></tr>";
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
