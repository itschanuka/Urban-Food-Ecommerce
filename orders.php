<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('location:login.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/header.css">
   <link rel="stylesheet" href="css/orders.css">
   <style>
      .order-stats {
   padding: 50px 20px;
   background-color: #f7f9fb;
   text-align: center;
}

.order-stats .title {
   font-size: 32px;
   font-weight: bold;
   color: #333;
   margin-bottom: 30px;
}

.order-stats .box-container {
   display: flex;
   justify-content: center;
   flex-wrap: wrap;
   gap: 25px;
}

.order-stats .box {
   background: #fff;
   border-radius: 12px;
   box-shadow: 0 4px 8px rgba(0,0,0,0.1);
   padding: 30px 20px;
   width: 300px;
   transition: all 0.3s ease;
}

.order-stats .box:hover {
   transform: translateY(-10px);
   box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.order-stats .box p {
   font-size: 18px;
   color: #555;
   margin: 10px 0;
}

.order-stats .box p span {
   font-weight: bold;
   font-size: 20px;
   color: #000;
}

.order-stats .box p span[style*="green"] {
   color: #2ecc71; /* softer green */
}

.order-stats .box p span[style*="red"] {
   color: #e74c3c; /* softer red */
}
</style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="order-summary">
   <div class="summary-card">
      <h2>Order Summary <i class="fas fa-box"></i></h2>
      <div class="summary-box">
         <?php
            // Get total orders
            $total_orders_stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :user_id");
            $total_orders_stmt->bindParam(':user_id', $user_id);
            $total_orders_stmt->execute();
            $total_orders_count = $total_orders_stmt->fetchColumn();
         ?>
         <div class="summary-item">
            <p>Total Orders: <span><i class="fas fa-shopping-cart"></i> <?= $total_orders_count ?> 🛒</span></p>
            <div class="progress-bar">
               <div class="progress" style="width: <?= ($total_orders_count > 0) ? '100' : '0' ?>%;"></div>
            </div>
         </div>

         <div class="summary-details">
            <p style="margin-top: 15px; color: #444;">
               Thank you for being a valued customer! Here you can track the number of orders you've placed. For more details on each order, please visit your <a href="orders.php" style="color: #007bff;">Order History</a>.
            </p>
         </div>
      </div>
   </div>
</section>

<section class="placed-orders">
   <h1 class="title">Placed Orders</h1>

   <div class="box-container">

   <?php
      $select_orders = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id");
      $select_orders->bindParam(':user_id', $user_id);
      $select_orders->execute();

      $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

      if ($orders && count($orders) > 0) {
         foreach ($orders as $order) {
   ?>
   <div class="box">
      <p>Placed on : <span><?= htmlspecialchars($order['PLACED_ON']) ?></span></p>
      <p>Name : <span><?= htmlspecialchars($order['NAME']) ?></span></p>
      <p>Phone number : <span><?= htmlspecialchars($order['PHONE_NUMBER']) ?></span></p>
      <p>Email : <span><?= htmlspecialchars($order['EMAIL']) ?></span></p>
      <p>Address : <span><?= htmlspecialchars($order['ADDRESS']) ?></span></p>
      <p>Payment method : <span><?= htmlspecialchars($order['METHOD']) ?></span></p>
      <p>Your orders : <span><?= htmlspecialchars($order['TOTAL_PRODUCTS']) ?></span></p>
      <p>Total price : <span>$<?= htmlspecialchars($order['TOTAL_PRICE']) ?>/-</span></p>
      <p>Payment status : 
         <span style="color:<?= strtolower($order['PAYMENT_STATUS']) === 'pending' ? 'red' : 'green'; ?>">
            <?= htmlspecialchars($order['PAYMENT_STATUS']) ?>
         </span>
      </p>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No orders placed yet!</p>';
      }
   ?>

   </div>
</section>

<!-- New Section for Summary Using Procedure -->
<section class="order-stats">
   <h1 class="title">Order Status Overview</h1>

   <div class="box-container">

   <?php
      // Total Orders
      $stmt_total = $conn->prepare("SELECT COUNT(*) FROM ORDERS WHERE USER_ID = :user_id");
      $stmt_total->bindParam(':user_id', $user_id);
      $stmt_total->execute();
      $total_orders = $stmt_total->fetchColumn();

      // Completed Orders
      $stmt_completed = $conn->prepare("SELECT COUNT(*) FROM ORDERS WHERE USER_ID = :user_id AND LOWER(PAYMENT_STATUS) = 'completed'");
      $stmt_completed->bindParam(':user_id', $user_id);
      $stmt_completed->execute();
      $completed_orders = $stmt_completed->fetchColumn();

      // Pending Orders
      $stmt_pending = $conn->prepare("SELECT COUNT(*) FROM ORDERS WHERE USER_ID = :user_id AND LOWER(PAYMENT_STATUS) = 'pending'");
      $stmt_pending->bindParam(':user_id', $user_id);
      $stmt_pending->execute();
      $pending_orders = $stmt_pending->fetchColumn();
   ?>

      <div class="box">
         <p>Total Orders: <span><?= $total_orders ?></span></p>
         <p>Completed Orders: <span style="color:green;"><?= $completed_orders ?></span></p>
         <p>Pending Orders: <span style="color:red;"><?= $pending_orders ?></span></p>
      </div>

   </div>
</section>


<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
