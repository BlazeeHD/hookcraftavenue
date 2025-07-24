<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Enhanced error handling and validation
function validateFile($file, $maxSize = 5242880, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error: ' . $file['error']];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Max size: 5MB'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only images allowed.'];
    }
    
    return ['success' => true];
}

function generateSecureFilename($originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return uniqid('payment_', true) . '.' . $extension;
}

$message = '';
$messageType = '';

// Upload new payment proof
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id']) && isset($_FILES["payment_proof"])) {
    $orderId = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    
    if ($orderId === false) {
        $message = 'Invalid order ID';
        $messageType = 'danger';
    } else {
        $validation = validateFile($_FILES["payment_proof"]);
        
        if ($validation['success']) {
            $targetDir = __DIR__ . '/../uploads/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            
            $filename = generateSecureFilename($_FILES["payment_proof"]["name"]);
            $targetFile = $targetDir . $filename;
            
            if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $targetFile)) {
                $stmt = $conn->prepare("UPDATE orders SET payment_proof = ?, payment_status = 'Successful', updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $filename, $orderId);
                
                if ($stmt->execute()) {
                    $message = 'Payment proof uploaded successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Database update failed';
                    $messageType = 'danger';
                    unlink($targetFile); // Clean up file on DB failure
                }
            } else {
                $message = 'File upload failed';
                $messageType = 'danger';
            }
        } else {
            $message = $validation['message'];
            $messageType = 'danger';
        }
    }
}

// Mark as unsuccessful
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_unsuccessful'])) {
    $orderId = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    
    if ($orderId !== false) {
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'Unsuccessful', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        
        if ($stmt->execute()) {
            $message = 'Order marked as unsuccessful';
            $messageType = 'warning';
        }
    }
}

// Handle edit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_status'])) {
    $orderId = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    $newStatus = filter_var($_POST['new_status'], FILTER_SANITIZE_STRING);
    
    if ($orderId !== false && in_array($newStatus, ['Pending', 'Successful', 'Unsuccessful'])) {
        $updateQuery = "UPDATE orders SET payment_status = ?, updated_at = NOW()";
        $params = [$newStatus];
        $types = "s";
        
        if (!empty($_FILES["new_proof"]["name"])) {
            $validation = validateFile($_FILES["new_proof"]);
            
            if ($validation['success']) {
                $targetDir = __DIR__ . '/../uploads/';
                $filename = generateSecureFilename($_FILES["new_proof"]["name"]);
                $targetFile = $targetDir . $filename;
                
                if (move_uploaded_file($_FILES["new_proof"]["tmp_name"], $targetFile)) {
                    $updateQuery .= ", payment_proof = ?";
                    $params[] = $filename;
                    $types .= "s";
                }
            }
        }
        
        $updateQuery .= " WHERE id = ?";
        $params[] = $orderId;
        $types .= "i";
        
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $message = 'Order updated successfully!';
            $messageType = 'success';
        }
    }
}

// Sanitize and validate filters
$search = filter_var($_GET['search'] ?? '', FILTER_SANITIZE_STRING);
$statusFilter = filter_var($_GET['status'] ?? '', FILTER_SANITIZE_STRING);
$methodFilter = filter_var($_GET['method'] ?? '', FILTER_SANITIZE_STRING);
$page = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT));
$limit = 20;
$offset = ($page - 1) * $limit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Hookcraft Avenue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../asset/dashboard.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            --card-shadow: 0 10px 30px rgba(0,0,0,0.1);
            --hover-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .main-content {
            background: transparent;
            padding: 2rem;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: none;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }

        .card-header-custom {
            background: var(--primary-gradient);
            border-radius: 20px 20px 0 0 !important;
            padding: 1.5rem 2rem;
            border: none;
        }

        .card-header-custom h5 {
            margin: 0;
            font-weight: 600;
            color: white;
            font-size: 1.4rem;
        }

        .filter-section {
            background: rgba(248, 249, 250, 0.8);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }

        .btn-custom {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary-custom {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-success-custom {
            background: var(--success-gradient);
            color: white;
        }

        .btn-warning-custom {
            background: var(--warning-gradient);
            color: white;
        }

        .btn-danger-custom {
            background: var(--danger-gradient);
            color: white;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .table-custom {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            background: white;
        }

        .table-custom thead th {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border: none;
            padding: 1rem 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-custom tbody tr {
            transition: all 0.3s ease;
            border: none;
        }

        .table-custom tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
            transform: scale(1.01);
        }

        .table-custom td {
            padding: 1rem 0.75rem;
            border-color: #f8f9fa;
            vertical-align: middle;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
            color: #2d3436;
        }

        .status-successful {
            background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
            color: white;
        }

        .status-unsuccessful {
            background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
            color: white;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 120px;
        }

        .action-buttons .btn {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1.5rem 2rem;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 1;
        }

        .btn-close:hover {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        /* Fix modal backdrop and positioning issues */
        .modal {
            z-index: 1055 !important;
        }

        .modal-backdrop {
            z-index: 1050 !important;
        }

        .modal-dialog {
            z-index: 1056 !important;
        }

        /* Ensure buttons work properly in modals */
        .modal .btn {
            position: relative;
            z-index: 1;
            pointer-events: auto;
        }

        .modal-body .form-control,
        .modal-body .form-select {
            z-index: 1;
            position: relative;
        }

        .alert-custom {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .pagination-custom .page-link {
            border-radius: 10px;
            margin: 0 0.2rem;
            border: 2px solid transparent;
            color: #667eea;
            font-weight: 500;
        }

        .pagination-custom .page-link:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        .pagination-custom .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .loading-spinner {
            display: none;
        }

        .table-responsive {
            border-radius: 15px;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                min-width: 100px;
            }
            
            .filter-section .row > div {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="main-content">
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'times-circle') ?>"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <?php
        $statsQuery = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN payment_status = 'Successful' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN payment_status = 'Unsuccessful' THEN 1 ELSE 0 END) as unsuccessful,
            SUM(CASE WHEN payment_status = 'Successful' THEN total ELSE 0 END) as revenue
            FROM orders";
        $statsResult = $conn->query($statsQuery);
        $stats = $statsResult->fetch_assoc();
        ?>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-number text-primary"><?= number_format($stats['total']) ?></div>
                <div class="text-muted">Total Orders</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-number text-warning"><?= number_format($stats['pending']) ?></div>
                <div class="text-muted">Pending</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-number text-success"><?= number_format($stats['successful']) ?></div>
                <div class="text-muted">Successful</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-number text-info">₱<?= number_format($stats['revenue'], 2) ?></div>
                <div class="text-muted">Revenue</div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-shopping-cart me-2"></i>Order Management Dashboard</h5>
        </div>
        <div class="card-body p-4">
            <!-- Enhanced Filter Section -->
            <div class="filter-section">
                <form method="get" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold mb-2">
                                <i class="fas fa-search me-1"></i>Search Customer
                            </label>
                            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Enter customer name...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="fas fa-filter me-1"></i>Status
                            </label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="Pending" <?= $statusFilter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Successful" <?= $statusFilter == 'Successful' ? 'selected' : '' ?>>Successful</option>
                                <option value="Unsuccessful" <?= $statusFilter == 'Unsuccessful' ? 'selected' : '' ?>>Unsuccessful</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="fas fa-credit-card me-1"></i>Method
                            </label>
                            <select name="method" class="form-select">
                                <option value="">All Methods</option>
                                <option value="GCash" <?= $methodFilter == 'GCash' ? 'selected' : '' ?>>GCash</option>
                                <option value="PayPal" <?= $methodFilter == 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                                <option value="Cash on Delivery" <?= $methodFilter == 'Cash on Delivery' ? 'selected' : '' ?>>Cash on Delivery</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-custom btn-primary-custom flex-fill">
                                <i class="fas fa-search me-1"></i>Apply Filters
                            </button>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary btn-custom">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                            <button type="button" class="btn btn-outline-success btn-custom" onclick="exportToCSV()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-1"></i>ID</th>
                            <th><i class="fas fa-user me-1"></i>Customer</th>
                            <th><i class="fas fa-box me-1"></i>Products</th>
                            <th><i class="fas fa-map-marker-alt me-1"></i>Address</th>
                            <th><i class="fas fa-phone me-1"></i>Phone</th>
                            <th><i class="fas fa-peso-sign me-1"></i>Total</th>
                            <th><i class="fas fa-receipt me-1"></i>Proof</th>
                            <th><i class="fas fa-calendar me-1"></i>Date</th>
                            <th><i class="fas fa-credit-card me-1"></i>Method</th>
                            <th><i class="fas fa-info-circle me-1"></i>Status</th>
                            <th><i class="fas fa-cogs me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Optimized query with JOIN for better performance
                    $query = "SELECT o.*, 
                              GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR ', ') as products
                              FROM orders o
                              LEFT JOIN order_item oi ON o.id = oi.order_id
                              LEFT JOIN products p ON oi.product_id = p.id
                              WHERE 1=1";
                    $params = [];
                    $types = '';

                    if (!empty($search)) {
                        $query .= " AND o.customer_name LIKE ?";
                        $params[] = "%$search%";
                        $types .= 's';
                    }

                    if (!empty($statusFilter)) {
                        $query .= " AND o.payment_status = ?";
                        $params[] = $statusFilter;
                        $types .= 's';
                    }

                    if (!empty($methodFilter)) {
                        $query .= " AND o.payment_method = ?";
                        $params[] = $methodFilter;
                        $types .= 's';
                    }

                    $query .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
                    $params[] = $limit;
                    $params[] = $offset;
                    $types .= 'ii';

                    $stmt = $conn->prepare($query);
                    if (!empty($params)) {
                        $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 0) {
                        echo "<tr><td colspan='11' class='text-center py-5 text-muted'>";
                        echo "<i class='fas fa-inbox fa-3x mb-3 d-block'></i>";
                        echo "<h5>No orders found</h5>";
                        echo "<p>Try adjusting your search criteria or add new orders.</p>";
                        echo "</td></tr>";
                    }

                    while ($row = $result->fetch_assoc()) {
                        $orderId = $row['id'];
                        $modalId = "uploadModal$orderId";
                        $editModalId = "editModal$orderId";
                        $proofModalId = "proofModal$orderId";

                        echo "<tr>";
                        echo "<td><strong>#{$row['id']}</strong></td>";
                        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                        echo "<td><small>" . htmlspecialchars($row['products'] ?: 'No products') . "</small></td>";
                        echo "<td><small>" . htmlspecialchars(substr($row['address'], 0, 30)) . "...</small></td>";
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                        echo "<td><strong>₱" . number_format($row['total'], 2) . "</strong></td>";

                        // Payment proof with enhanced modal
                        if (!empty($row['payment_proof'])) {
                            echo "<td><button class='btn btn-sm btn-outline-primary' onclick='openProofModal({$orderId}, \"" . htmlspecialchars($row['payment_proof']) . "\")'>";
                            echo "<i class='fas fa-eye me-1'></i>View</button></td>";
                        } else {
                            echo "<td><span class='text-muted'><i class='fas fa-times-circle me-1'></i>None</span></td>";
                        }

                        echo "<td>" . date("M j, Y", strtotime($row['created_at'])) . "</td>";
                        echo "<td><span class='badge bg-info'>" . htmlspecialchars($row['payment_method']) . "</span></td>";
                        
                        // Enhanced status badges
                        $statusClass = [
                            'Pending' => 'status-pending',
                            'Successful' => 'status-successful',
                            'Unsuccessful' => 'status-unsuccessful'
                        ][$row['payment_status']] ?? 'status-pending';
                        
                        echo "<td><span class='status-badge $statusClass'>" . htmlspecialchars($row['payment_status']) . "</span></td>";

                        // Enhanced action buttons with onclick handlers
                        echo "<td><div class='action-buttons'>";
                        if ($row['payment_status'] === 'Pending') {
                            echo "<button class='btn btn-success-custom btn-custom btn-sm' onclick='openUploadModal({$orderId})'>";
                            echo "<i class='fas fa-upload me-1'></i>Upload Proof</button>";
                            echo "<form method='POST' onsubmit=\"return confirm('Mark this order as unsuccessful?');\" class='mb-0'>";
                            echo "<input type='hidden' name='order_id' value='$orderId'>";
                            echo "<button type='submit' name='mark_unsuccessful' class='btn btn-danger-custom btn-custom btn-sm'>";
                            echo "<i class='fas fa-times me-1'></i>Mark Failed</button>";
                            echo "</form>";
                        } else {
                            echo "<button class='btn btn-warning-custom btn-custom btn-sm' onclick='openEditModal({$orderId}, \"{$row['payment_status']}\", \"{$row['payment_proof']}\")'>";
                            echo "<i class='fas fa-edit me-1'></i>Edit Order</button>";
                        }
                        echo "</div></td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>



            <!-- Enhanced Pagination -->
            <?php
            // Get total count for pagination
            $countQuery = "SELECT COUNT(DISTINCT o.id) as total FROM orders o WHERE 1=1";
            $countParams = [];
            $countTypes = '';

            if (!empty($search)) {
                $countQuery .= " AND o.customer_name LIKE ?";
                $countParams[] = "%$search%";
                $countTypes .= 's';
            }

            if (!empty($statusFilter)) {
                $countQuery .= " AND o.payment_status = ?";
                $countParams[] = $statusFilter;
                $countTypes .= 's';
            }

            if (!empty($methodFilter)) {
                $countQuery .= " AND o.payment_method = ?";
                $countParams[] = $methodFilter;
                $countTypes .= 's';
            }

            $countStmt = $conn->prepare($countQuery);
            if (!empty($countParams)) {
                $countStmt->bind_param($countTypes, ...$countParams);
            }
            $countStmt->execute();
            $totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
            $totalPages = ceil($totalRecords / $limit);

            if ($totalPages > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing <?= ($offset + 1) ?> to <?= min($offset + $limit, $totalRecords) ?> of <?= $totalRecords ?> orders
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-custom mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Single Universal Modal for All Actions -->
<div class="modal fade" id="universalModal" tabindex="-1" aria-labelledby="universalModalLabel" aria-hidden="true">
    <div class="modal-dialog" id="modalSize">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="universalModalLabel">Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer" id="modalFooterContent">
                <!-- Footer will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Universal Modal Functions - This will definitely work!
    function openUploadModal(orderId) {
        const modal = document.getElementById('universalModal');
        const modalLabel = document.getElementById('universalModalLabel');
        const modalBody = document.getElementById('modalBodyContent');
        const modalFooter = document.getElementById('modalFooterContent');
        const modalSize = document.getElementById('modalSize');
        
        // Reset modal size
        modalSize.className = 'modal-dialog';
        
        // Set title
        modalLabel.innerHTML = '<i class="fas fa-upload me-2"></i>Upload Payment Proof';
        
        // Set body content
        modalBody.innerHTML = `
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="order_id" value="${orderId}">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Payment Proof Image</label>
                    <input type="file" name="payment_proof" class="form-control" required accept="image/*">
                    <div class="form-text">Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB</div>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Uploading proof will automatically mark this order as <strong>Successful</strong>.
                </div>
            </form>
        `;
        
        // Set footer content
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" onclick="submitUploadForm()">
                <i class="fas fa-upload me-1"></i>Upload & Mark Successful
            </button>
        `;
        
        // Show modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
    
    function openEditModal(orderId, currentStatus, currentProof) {
        const modal = document.getElementById('universalModal');
        const modalLabel = document.getElementById('universalModalLabel');
        const modalBody = document.getElementById('modalBodyContent');
        const modalFooter = document.getElementById('modalFooterContent');
        const modalSize = document.getElementById('modalSize');
        
        // Reset modal size
        modalSize.className = 'modal-dialog';
        
        // Set title
        modalLabel.innerHTML = `<i class="fas fa-edit me-2"></i>Edit Order #${orderId}`;
        
        // Current proof display
        let proofDisplay = '';
        if (currentProof && currentProof !== 'null') {
            proofDisplay = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Proof</label>
                    <div class="text-center">
                        <img src="../uploads/${currentProof}" class="img-thumbnail" style="max-height: 150px;">
                    </div>
                </div>
            `;
        }
        
        // Set body content
        modalBody.innerHTML = `
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="order_id" value="${orderId}">
                <div class="mb-3">
                    <label class="form-label fw-bold">Payment Status</label>
                    <select name="new_status" class="form-select" required>
                        <option value="Pending" ${currentStatus === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Successful" ${currentStatus === 'Successful' ? 'selected' : ''}>Successful</option>
                        <option value="Unsuccessful" ${currentStatus === 'Unsuccessful' ? 'selected' : ''}>Unsuccessful</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Replace Payment Proof (Optional)</label>
                    <input type="file" name="new_proof" class="form-control" accept="image/*">
                    <div class="form-text">Leave empty to keep existing proof</div>
                </div>
                ${proofDisplay}
            </form>
        `;
        
        // Set footer content
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitEditForm()">
                <i class="fas fa-save me-1"></i>Save Changes
            </button>
        `;
        
        // Show modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
    
    function openProofModal(orderId, proofFile) {
        const modal = document.getElementById('universalModal');
        const modalLabel = document.getElementById('universalModalLabel');
        const modalBody = document.getElementById('modalBodyContent');
        const modalFooter = document.getElementById('modalFooterContent');
        const modalSize = document.getElementById('modalSize');
        
        // Set large modal size
        modalSize.className = 'modal-dialog modal-lg';
        
        // Set title
        modalLabel.innerHTML = `<i class="fas fa-receipt me-2"></i>Payment Proof - Order #${orderId}`;
        
        // Set body content
        modalBody.innerHTML = `
            <div class="text-center p-4">
                <img src="../uploads/${proofFile}" class="img-fluid rounded shadow" style="max-height: 500px;">
            </div>
        `;
        
        // Set footer content
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <a href="../uploads/${proofFile}" target="_blank" class="btn btn-primary">
                <i class="fas fa-external-link-alt me-1"></i>Open Full Size
            </a>
        `;
        
        // Show modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
    
    function submitUploadForm() {
        const form = document.getElementById('uploadForm');
        const submitBtn = event.target;
        
        // Validate file
        const fileInput = form.querySelector('input[type="file"]');
        if (!fileInput.files[0]) {
            alert('Please select a file');
            return;
        }
        
        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Uploading...';
        
        // Submit form
        form.submit();
    }
    
    function submitEditForm() {
        const form = document.getElementById('editForm');
        const submitBtn = event.target;
        
        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        
        // Add hidden field for edit_status
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'edit_status';
        hiddenInput.value = '1';
        form.appendChild(hiddenInput);
        
        // Submit form
        form.submit();
    }

    // Enhanced JavaScript functionality with modal fixes
    document.addEventListener('DOMContentLoaded', function() {
        // Clean up any existing modal issues first
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        const existingBackdrops = document.querySelectorAll('.modal-backdrop');
        existingBackdrops.forEach(backdrop => backdrop.remove());

        // Simple modal initialization - let Bootstrap handle it naturally
        // No need to manually initialize modals, Bootstrap does this automatically

        // Auto-submit form on filter change
        const filterSelects = document.querySelectorAll('#filterForm select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });

        // Loading state for forms with better targeting
        document.addEventListener('submit', function(e) {
            if (e.target.tagName === 'FORM') {
                const submitBtn = e.target.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    setTimeout(() => {
                        submitBtn.disabled = true;
                        const originalHTML = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
                        
                        // Re-enable after 10 seconds as fallback
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalHTML;
                        }, 10000);
                    }, 50);
                }
            }
        });

        // Enhanced file upload preview
        document.addEventListener('change', function(e) {
            if (e.target.type === 'file') {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size
                    if (file.size > 5242880) { // 5MB
                        alert('File size must be less than 5MB');
                        e.target.value = '';
                        return;
                    }
                    
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please select a valid image file (JPG, PNG, GIF, WebP)');
                        e.target.value = '';
                        return;
                    }
                    
                    // Show file name
                    let feedback = e.target.parentNode.querySelector('.file-feedback');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'file-feedback text-success mt-1';
                        e.target.parentNode.appendChild(feedback);
                    }
                    feedback.innerHTML = `<i class="fas fa-check-circle me-1"></i>Selected: ${file.name}`;
                }
            }
        });

        // Better confirmation handling
        document.addEventListener('click', function(e) {
            if (e.target.matches('button[name="mark_unsuccessful"]')) {
                if (!confirm('⚠️ Are you sure you want to mark this order as UNSUCCESSFUL?\n\nThis action will update the order status and cannot be easily undone.')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
        });

        // Toast notifications for better UX
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        // Modal event listeners for debugging and cleanup
        document.addEventListener('show.bs.modal', function(e) {
            console.log('Modal showing:', e.target.id);
        });

        document.addEventListener('shown.bs.modal', function(e) {
            console.log('Modal shown:', e.target.id);
            // Ensure form elements are focusable
            const modal = e.target;
            const inputs = modal.querySelectorAll('input, select, button');
            inputs.forEach(input => {
                input.style.pointerEvents = 'auto';
                input.style.zIndex = '1';
            });
        });

        document.addEventListener('hidden.bs.modal', function(e) {
            console.log('Modal hidden:', e.target.id);
            // Clean up any lingering issues
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
                document.body.style.overflow = '';
            }, 100);
        });
    });

    // Export to CSV functionality
    function exportToCSV() {
        const table = document.querySelector('.table-custom');
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        // Get headers (excluding Actions column)
        const headers = Array.from(rows[0].querySelectorAll('th'))
            .slice(0, -1)
            .map(th => th.textContent.replace(/\s+/g, ' ').trim());
        csv.push(headers.join(','));
        
        // Get data rows
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cols = Array.from(row.querySelectorAll('td'))
                .slice(0, -1)
                .map(td => {
                    let text = td.textContent.replace(/\s+/g, ' ').trim();
                    // Handle commas in data
                    if (text.includes(',')) {
                        text = `"${text}"`;
                    }
                    return text;
                });
            if (cols.length > 0) {
                csv.push(cols.join(','));
            }
        }
        
        // Download CSV
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `orders_export_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Real-time search with debouncing
    let searchTimeout;
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    document.getElementById('filterForm').submit();
                }
            }, 500);
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            searchInput?.focus();
        }
        
        // Escape to clear search
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            document.getElementById('filterForm').submit();
        }
    });
</script>

<?php $conn->close(); ?>
</body>
</html>