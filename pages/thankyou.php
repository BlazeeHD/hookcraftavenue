<?php 
session_start(); 
$order_id = $_GET['order_id'] ?? null;
$total = $_GET['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Confirmation - HookcraftAvenue</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #fff6f9 0%, #ffe0e6 100%);
      font-family: 'Open Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
    }
    .thankyou-container {
      max-width: 650px;
      margin: 20px auto;
      background: white;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      text-align: center;
      position: relative;
    }
    .thankyou-container::before {
      content: '';
      position: absolute;
      top: -2px;
      left: -2px;
      right: -2px;
      bottom: -2px;
      background: linear-gradient(45deg, #ff6fa4, #ff9a8b, #a8e6cf, #dcedc8);
      border-radius: 22px;
      z-index: -1;
    }
    .success-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #4CAF50, #45a049);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      color: white;
      font-size: 36px;
    }
    .thankyou-container h2 {
      color: #2c3e50;
      margin-bottom: 15px;
      font-weight: 700;
    }
    .thankyou-container p {
      color: #555;
      font-size: 1.1rem;
      line-height: 1.6;
    }
    .order-details {
      background: #f8f9fa;
      border-radius: 15px;
      padding: 20px;
      margin: 25px 0;
    }
    .order-details h5 {
      color: #2c3e50;
      margin-bottom: 15px;
    }
    .detail-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    .btn-home {
      background: linear-gradient(135deg, #ff6fa4, #ff8a80);
      color: white;
      border-radius: 25px;
      padding: 12px 30px;
      text-decoration: none;
      font-weight: 600;
      display: inline-block;
      margin: 10px;
      transition: all 0.3s ease;
      border: none;
    }
    .btn-home:hover {
      background: linear-gradient(135deg, #e91e63, #f44336);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      color: white;
    }
    .btn-secondary-custom {
      background: linear-gradient(135deg, #6c757d, #5a6268);
      color: white;
      border-radius: 25px;
      padding: 12px 30px;
      text-decoration: none;
      font-weight: 600;
      display: inline-block;
      margin: 10px;
      transition: all 0.3s ease;
      border: none;
    }
    .btn-secondary-custom:hover {
      background: linear-gradient(135deg, #5a6268, #495057);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      color: white;
    }
    .payment-info {
      background: #e8f5e8;
      border-left: 4px solid #4CAF50;
      padding: 15px;
      margin: 20px 0;
      border-radius: 5px;
    }
    .contact-info {
      background: #fff3cd;
      border-left: 4px solid #ffc107;
      padding: 15px;
      margin: 20px 0;
      border-radius: 5px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="thankyou-container">
    <div class="success-icon">
      ✓
    </div>
    
    <h2>🎉 Order Confirmed!</h2>
    <p>Thank you for choosing HookcraftAvenue! Your order has been successfully placed and is now being processed.</p>
    
    <?php if ($order_id): ?>
    <div class="order-details">
      <h5>📋 Order Details</h5>
      <div class="detail-item">
        <span><strong>Order ID:</strong></span>
        <span class="text-primary"><strong>#<?= htmlspecialchars($order_id) ?></strong></span>
      </div>
      <?php if ($total > 0): ?>
      <div class="detail-item">
        <span><strong>Total Amount:</strong></span>
        <span class="text-success"><strong>₱<?= number_format($total, 2) ?></strong></span>
      </div>
      <?php endif; ?>
      <div class="detail-item">
        <span><strong>Status:</strong></span>
        <span class="badge bg-warning">Pending Payment</span>
      </div>
      <div class="detail-item">
        <span><strong>Payment Method:</strong></span>
        <span>GCash (Manual Payment)</span>
      </div>
    </div>
    <?php endif; ?>
    
    <div class="payment-info">
      <h6><strong>📱 Next Steps - Payment Instructions</strong></h6>
      <p class="mb-2">We will contact you shortly via your provided phone number with:</p>
      <ul class="text-start mb-0">
        <li>GCash payment details and instructions</li>
        <li>Order confirmation and processing timeline</li>
        <li>Delivery/pickup arrangements</li>
      </ul>
    </div>
    
    <div class="contact-info">
      <h6><strong>📞 Need Help?</strong></h6>
      <p class="mb-0">If you have any questions about your order, please don't hesitate to <a href="contact.html">contact us</a>. Keep your Order ID <strong>#<?= htmlspecialchars($order_id ?? 'N/A') ?></strong> for reference.</p>
    </div>
    
    <div class="mt-4">
      <a href="shop.php" class="btn-home">🛍️ Continue Shopping</a>
      <a href="orders.php" class="btn-secondary-custom">📋 View Orders</a>
    </div>
    
    <hr class="my-4">
    <p class="text-muted small mb-0">
      You will receive updates about your order status. Please ensure your phone number is available for our contact.
    </p>
  </div>
</div>

<script>
// Auto-scroll to top
window.scrollTo(0, 0);

// Optional: Add some celebration animation
document.addEventListener('DOMContentLoaded', function() {
  const successIcon = document.querySelector('.success-icon');
  successIcon.style.transform = 'scale(0)';
  successIcon.style.transition = 'transform 0.5s ease-out';
  
  setTimeout(() => {
    successIcon.style.transform = 'scale(1)';
  }, 200);
});
</script>

</body>
</html>