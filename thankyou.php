<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Confirmation - HookcraftAvenue</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #fff6f9;
      font-family: 'Open Sans', sans-serif;
    }
    .thankyou-container {
      max-width: 600px;
      margin: 80px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      text-align: center;
    }
    .thankyou-container h2 {
      color: #ff6fa4;
      margin-bottom: 15px;
    }
    .thankyou-container p {
      color: #444;
      font-size: 1.1rem;
    }
    .btn-home {
      background-color: #ff6fa4;
      color: white;
      border-radius: 30px;
      padding: 10px 30px;
      text-decoration: none;
    }
    .btn-home:hover {
      background-color: #e95892;
    }
  </style>
</head>
<body>

  <div class="thankyou-container">
    <h2>🎉 Thank You for Your Order!</h2>
    <p>Your order has been successfully placed and is being processed.</p>
    <p>We'll get in touch shortly via your provided phone number.</p>
    <hr>
    <p>If you have questions, feel free to <a href="contact.html">contact us</a>.</p>
    <a href="shop.php" class="btn btn-home mt-3">Continue Shopping</a>
  </div>

</body>
</html>
