<?php
include 'includes/db.php'; // uses $conn (MySQLi)

// Get total users
$usersResult = $conn->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $usersResult->fetch_assoc()['total'];

// Get total orders
$ordersResult = $conn->query("SELECT COUNT(*) as total FROM orders");
$totalOrders = $ordersResult->fetch_assoc()['total'];

// Get total products
$productsResult = $conn->query("SELECT COUNT(*) as total FROM products");
$totalProducts = $productsResult->fetch_assoc()['total'];

// Get total revenue - only from successful payments
$revenueResult = $conn->query("SELECT SUM(total) as total FROM orders WHERE payment_status = 'Successful'");
$totalRevenue = $revenueResult->fetch_assoc()['total'] ?? 0;

// Get cart count
$cartResult = $conn->query("SELECT COUNT(*) as total FROM cart");
$cartCount = $cartResult->fetch_assoc()['total'];

// ✅ FIX: Get recent orders with proper query
$recentOrdersResult = $conn->query("SELECT id, customer_name, total, payment_status FROM orders ORDER BY created_at DESC LIMIT 6");
?>

<!-- HTML PART (unchanged layout/design) -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - Hookcraft Avenue</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
 <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Main Content - Adjusted for sidebar */
        .main-content {
            flex: 1;
            margin-left: 80px; /* Account for fixed sidebar width */
            padding: 30px;
            background: transparent;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-logo {
            background: linear-gradient(45deg, #e91e63, #f8bbd9);
            padding: 15px 25px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(233, 30, 99, 0.3);
        }

        .brand-logo h2 {
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-btn {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: #666;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .notification-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e91e63;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 8px 15px;
            border-radius: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(45deg, #e91e63, #f8bbd9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 25px;
            height: calc(100vh - 180px);
        }

        /* Cards */
        .dashboard-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--card-color, #e91e63);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        /* Card Variants */
        .card-users {
            --card-color: #e91e63;
        }

        .card-users .card-icon {
            background: linear-gradient(45deg, #e91e63, #f8bbd9);
        }

        .card-orders {
            --card-color: #f06292;
        }

        .card-orders .card-icon {
            background: linear-gradient(45deg, #f06292, #f8bbd9);
        }

        .card-analytics {
            grid-row: 1 / 3;
            --card-color: #ad1457;
        }

        .card-analytics .card-icon {
            background: linear-gradient(45deg, #ad1457, #e91e63);
        }

        .card-recent {
            grid-column: 1 / 3;
            --card-color: #ec407a;
        }

        .card-recent .card-icon {
            background: linear-gradient(45deg, #ec407a, #f8bbd9);
        }

        .card-revenue {
            --card-color: #c2185b;
        }

        .card-revenue .card-icon {
            background: linear-gradient(45deg, #c2185b, #e91e63);
        }

        /* Card Content */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 16px;
            color: #666;
            font-weight: 500;
            margin: 0;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .card-value {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            line-height: 1;
        }

        .card-change {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .card-change.positive {
            color: #10b981;
        }

        .card-change.negative {
            color: #ef4444;
        }

        .card-change i {
            font-size: 12px;
        }

        /* Recent Orders Table */
        .recent-orders-table {
            flex: 1;
            margin-top: 20px;
        }

        .table-header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 2px solid #f1f5f9;
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }

        .table-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f8fafc;
            align-items: center;
            transition: all 0.2s ease;
        }

        .table-row:hover {
            background: #f8fafc;
            margin: 0 -30px;
            padding: 15px 30px;
            border-radius: 10px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            max-width: fit-content;
        }

        .status-delivered {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-successful {
            background: #dcfce7;
            color: #166534;
        }

        /* Analytics Chart Placeholder */
        .chart-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            margin-top: 20px;
            min-height: 200px;
        }

        .chart-placeholder {
            text-align: center;
            color: #666;
        }

        .chart-placeholder i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto auto auto;
            }
            
            .card-analytics {
                grid-row: 3;
                grid-column: 1 / 3;
            }
            
            .card-recent {
                grid-column: 1 / 3;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                margin-bottom: 80px; /* Account for bottom sidebar on mobile */
                padding: 20px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .card-analytics,
            .card-recent {
                grid-column: 1;
                grid-row: auto;
            }
        }
    </style>
</head>
<body>
  <div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
      <div class="header">
        <div class="brand-section">
          <div class="brand-logo">
            <h2>Hookcraft Avenue</h2>
          </div>
        </div>
        <div class="user-section">
          <div class="notification-btn">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
          </div>
          <div class="user-profile">
            <div class="user-avatar">A</div>
            <div class="user-info">
              <div class="user-name">Admin</div>
              <div class="user-role">Administrator</div>
            </div>
          </div>
        </div>
      </div>

      <div class="dashboard-grid">
        <!-- Total Users -->
        <div class="dashboard-card card-users">
          <div class="card-header">
            <h3 class="card-title">Total Users</h3>
            <div class="card-icon"><i class="fas fa-users"></i></div>
          </div>
          <div class="card-value"><?= number_format($totalUsers) ?></div>
          <div class="card-change positive">
            <i class="fas fa-arrow-up"></i> +12% from last month
          </div>
        </div>

        <!-- Total Orders -->
        <div class="dashboard-card card-orders">
          <div class="card-header">
            <h3 class="card-title">Total Orders</h3>
            <div class="card-icon"><i class="fas fa-shopping-bag"></i></div>
          </div>
          <div class="card-value"><?= number_format($totalOrders) ?></div>
          <div class="card-change positive">
            <i class="fas fa-arrow-up"></i> +8% from last month
          </div>
        </div>

        <!-- Sales Analytics -->
        <div class="dashboard-card card-analytics">
          <div class="card-header">
            <h3 class="card-title">Sales Analytics</h3>
            <div class="card-icon"><i class="fas fa-chart-line"></i></div>
          </div>
          <div class="chart-container">
            <div class="chart-placeholder">
              <i class="fas fa-chart-area"></i>
              <div>Revenue: ₱<?= number_format($totalRevenue, 0) ?></div>
              <div style="font-size: 12px; margin-top: 10px;">Products: <?= number_format($totalProducts) ?></div>
              <div style="font-size: 12px;">Active Carts: <?= number_format($cartCount) ?></div>
            </div>
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="dashboard-card card-recent">
          <div class="card-header">
            <h3 class="card-title">Recent Orders</h3>
            <div class="card-icon"><i class="fas fa-list"></i></div>
          </div>
          <div class="recent-orders-table">
            <div class="table-header">
              <div>Order ID</div>
              <div>Customer</div>
              <div>Amount</div>
              <div>Status</div>
            </div>
            <?php if ($recentOrdersResult && $recentOrdersResult->num_rows > 0): ?>
              <?php $count = 0; ?>
              <?php while (($order = $recentOrdersResult->fetch_assoc()) && $count < 6): ?>
                <div class="table-row">
                  <div>#<?= $order['id'] ?></div>
                  <div><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></div>
                  <div>₱<?= number_format($order['total'], 0) ?></div>
                  <div>
                    <?php
                    // ✅ FIX: Map payment_status to display status
                    $paymentStatus = strtolower($order['payment_status']);
                    
                    // Map database status to display status
                    $displayStatus = match($paymentStatus) {
                        'successful' => 'delivered',
                        'pending' => 'pending',
                        'cancelled' => 'cancelled',
                        'processing' => 'processing',
                        default => 'pending'
                    };
                    
                    $statusClass = match($displayStatus) {
                        'delivered' => 'status-delivered',
                        'cancelled' => 'status-cancelled', 
                        'processing' => 'status-processing',
                        default => 'status-pending'
                    };
                    ?>
                    <span class="status-badge <?= $statusClass ?>">
                      <?= ucfirst($displayStatus) ?>
                    </span>
                  </div>
                </div>
                <?php $count++; ?>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="table-row">
                <div style="text-align: center; color: #666; grid-column: 1 / 5;">
                  No recent orders found
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Revenue Summary -->
        <div class="dashboard-card card-revenue">
          <div class="card-header">
            <h3 class="card-title">Revenue Summary</h3>
            <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
          </div>
          <div class="card-value">₱<?= number_format($totalRevenue, 0) ?></div>
          <div class="card-change positive">
            <i class="fas fa-arrow-up"></i> +15% from last month
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>