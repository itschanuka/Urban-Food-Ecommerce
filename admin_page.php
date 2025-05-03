<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
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
   <title>Admin Page</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/admin_style.css">

   <style>
      .date-time-container {
         display: flex;
         justify-content: flex-end;
         align-items: center;
         gap: 20px;
         padding: 10px 20px;
         margin: 10px 0 30px;
         background-color: #f0f0f0;
         border-radius: 8px;
         box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      }

      .datetime-box {
         font-size: 16px;
         font-weight: 500;
         color: #444;
         display: flex;
         align-items: center;
         gap: 8px;
      }

      .datetime-box i {
         color: #007bff;
      }


      /* Style for the entire container of boxes */
.box-container {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    justify-content: space-between;
    margin-top: 20px;
}

/* General box styling */
.box {
    background: linear-gradient(135deg, #ff9ff3, #ff6b81);
    color: white;
    border-radius: 15px;
    padding: 25px;
    width: 250px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
}

/* Hover effect to give a cute interactive feel */
.box:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2);
}

/* Title and number styling */
.box h3 {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 10px;
    margin-top: 15px;
}

/* Style for the entire container of boxes */
.box-container {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    justify-content: space-between;
    margin-top: 20px;
}

/* General box styling */
.box {
    background: linear-gradient(135deg, #74b9ff, #a29bfe);
    color: white;
    border-radius: 15px;
    padding: 25px;
    width: 250px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
}

/* Hover effect to give a cute interactive feel */
.box:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2);
}

/* Title and number styling */
.box h3 {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 10px;
    margin-top: 15px;
}

/* Add cute emojis to each box */
.box.pendings::before {
    content: "💸";
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
}

.box.completed::before {
    content: "✅";
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
}

.box.orders::before {
    content: "📦";
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
}

.box.products::before {
    content: "🛍️";
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
}

.box.users::before {
    content: "👥";
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
}

.box.admins::before {
    content: "👑";
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
}

.box.accounts::before {
    content: "📱";
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
}

/* Paragraph styling for box descriptions */
.box p {
    font-size: 16px;
    font-weight: 400;
}

/* Styling for the button */
.box .btn {
    background-color:rgb(14, 18, 226);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
    display: inline-block;
    margin-top: 10px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

/* Button hover effect */
.box .btn:hover {
    background-color: #00cec9;
}

.recent-activity {
  margin-top: 50px;
  font-size: 18px; /* Slightly reduced font size for better proportionality */
}

.recent-activity h2 {
  color: #007bff;
  margin-bottom: 20px;
  font-size: 24px; /* Increased heading size for emphasis */
  font-weight: 600;
}

.activity-log {
  background-color: #ffffff; /* Slightly more defined white background */
  border-radius: 10px; /* Increased border-radius for smoother corners */
  padding: 25px; /* Increased padding for better spacing */
  box-shadow: 0 4px 12px rgba(0,0,0,0.1); /* Soft, deeper shadow for a modern look */
  list-style: none;
  transition: box-shadow 0.3s ease, transform 0.3s ease; /* Added transition for smooth hover effects */
}

.activity-log:hover {
  box-shadow: 0 6px 18px rgba(0,0,0,0.15); /* Stronger shadow on hover */
  transform: translateY(-5px); /* Subtle lift effect on hover */
}

.activity-log li {
  padding: 15px 0; /* More padding for each list item */
  border-bottom: 1px solid #e1e1e1;
  color: #555;
  font-size: 16px; /* Smaller font size for list items */
}

.activity-log li:hover {
  background-color: #f1f1f1; /* Light background on hover for items */
  color: #333; /* Darker text color on hover */
}

.activity-log li:last-child {
  border-bottom: none; /* No border for the last item */
}

@media (max-width: 768px) {
  .recent-activity {
    margin-top: 30px; /* Adjusted margin for smaller screens */
  }

  .activity-log {
    padding: 15px; /* Reduced padding for smaller screens */
  }

  .activity-log li {
    padding: 12px 0; /* Reduced padding for list items */
  }
}


/* Real-Time Summary Table Styles */
.summary-table-container {
    margin: 30px 0;
    overflow-x: auto;
}

.summary-table-container table {
    width: 100%;
    border-collapse: collapse;
    background-color: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    font-family: 'Poppins', sans-serif;
}

.summary-table-container thead {
    background: linear-gradient(135deg, #74b9ff, #a29bfe);
    color: white;
}

.summary-table-container th, 
.summary-table-container td {
    padding: 15px 20px;
    text-align: center;
    font-size: 16px;
}

.summary-table-container tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.summary-table-container tbody tr:hover {
    background-color: #f1f1f1;
    transition: background-color 0.3s ease;
}

.summary-table-container th {
    font-size: 18px;
    font-weight: 600;
}

.summary-table-container td {
    color: #555;
}

/* Make table responsive */
@media (max-width: 768px) {
    .summary-table-container table {
        font-size: 14px;
    }
}

   </style>

</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="dashboard">

   <h1 class="title">Dashboard</h1>

   <div class="date-time-container">
      <?php
         date_default_timezone_set('Asia/Colombo');
         $current_date = date('l, F j, Y');
         $current_time = date('h:i A');
      ?>
      <div class="datetime-box">
         <i class="fas fa-calendar-alt"></i> <?= $current_date; ?>
      </div>
      <div class="datetime-box">
         <i class="fas fa-clock"></i> <?= $current_time; ?>
      </div>
   </div>

   <div class="box-container">

      <!-- Total Pendings -->
      <div class="box pendings">
         <?php
            $total_pendings = 0;
            $select_pendings = $conn->prepare("SELECT TOTAL_PRICE FROM ORDERS WHERE PAYMENT_STATUS = :status");
            $select_pendings->execute([':status' => 'pending']);
            while($row = $select_pendings->fetch(PDO::FETCH_ASSOC)){
               $total_pendings += $row['TOTAL_PRICE'];
            }
         ?>
         <h3>$<?= $total_pendings; ?>/-</h3>
         <p><strong>Total Pendings</strong></p>
         <p>Payments waiting for approval.</p>
         <a href="admin_orders.php" class="btn">See Orders</a>
      </div>

      <!-- Completed Orders -->
      <div class="box completed">
         <?php
            $total_completed = 0;
            $select_completed = $conn->prepare("SELECT TOTAL_PRICE FROM ORDERS WHERE PAYMENT_STATUS = :status");
            $select_completed->execute([':status' => 'completed']);
            while($row = $select_completed->fetch(PDO::FETCH_ASSOC)){
               $total_completed += $row['TOTAL_PRICE'];
            }
         ?>
         <h3>$<?= $total_completed; ?>/-</h3>
         <p><strong>Completed Orders</strong></p>
         <p>Orders successfully processed and paid for.</p>
         <a href="admin_orders.php" class="btn">See Orders</a>
      </div>

      <!-- Orders Placed -->
      <div class="box orders">
         <?php
            $select_orders = $conn->prepare("SELECT COUNT(*) AS TOTAL FROM ORDERS");
            $select_orders->execute();
            $number_of_orders = $select_orders->fetch(PDO::FETCH_ASSOC)['TOTAL'];
         ?>
         <h3><?= $number_of_orders; ?></h3>
         <p><strong>Orders Placed</strong></p>
         <p>All orders placed in the system.</p>
         <a href="admin_orders.php" class="btn">See Orders</a>
      </div>

      <!-- Products Added -->
      <div class="box products">
         <?php
            $select_products = $conn->prepare("SELECT COUNT(*) AS TOTAL FROM PRODUCTS");
            $select_products->execute();
            $number_of_products = $select_products->fetch(PDO::FETCH_ASSOC)['TOTAL'];
         ?>
         <h3><?= $number_of_products; ?></h3>
         <p><strong>Products Added</strong></p>
         <p>Number of products currently listed in the store.</p>
         <a href="admin_products.php" class="btn">See Products</a>
      </div>

      <!-- Total Users -->
      <div class="box users">
         <?php
            $select_users = $conn->prepare("SELECT COUNT(*) AS TOTAL FROM USERS WHERE USER_TYPE = :type");
            $select_users->execute([':type' => 'user']);
            $number_of_users = $select_users->fetch(PDO::FETCH_ASSOC)['TOTAL'];
         ?>
         <h3><?= $number_of_users; ?></h3>
         <p><strong>Total Users</strong></p>
         <p>Number of registered users on the platform.</p>
         <a href="admin_users.php" class="btn">See Accounts</a>
      </div>

      <!-- Total Admins -->
      <div class="box admins">
         <?php
            $select_admins = $conn->prepare("SELECT COUNT(*) AS TOTAL FROM USERS WHERE USER_TYPE = :type");
            $select_admins->execute([':type' => 'admin']);
            $number_of_admins = $select_admins->fetch(PDO::FETCH_ASSOC)['TOTAL'];
         ?>
         <h3><?= $number_of_admins; ?></h3>
         <p><strong>Total Admins</strong></p>
         <p>Number of admins managing the platform.</p>
         <a href="admin_users.php" class="btn">See Accounts</a>
      </div>

      <!-- Total Accounts -->
      <div class="box accounts">
         <?php
            $select_accounts = $conn->prepare("SELECT COUNT(*) AS TOTAL FROM USERS");
            $select_accounts->execute();
            $number_of_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)['TOTAL'];
         ?>
         <h3><?= $number_of_accounts; ?></h3>
         <p><strong>Total Accounts</strong></p>
         <p>Total registered users and admins in the system.</p>
         <a href="admin_users.php" class="btn">See Accounts</a>
      </div>

   </div>

</section>

<!-- Real-Time Summary Table -->
<div class="summary-table-container" style="margin: 30px 0; overflow-x: auto;">
   <table style="width:100%; border-collapse: collapse; text-align: center; background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden;">
      <thead style="background-color: #007bff; color: white;">
         <tr>
            <th style="padding: 15px;">Category</th>
            <th style="padding: 15px;">Amount</th>
         </tr>
      </thead>
      <tbody>
         <tr>
            <td style="padding: 15px;">Total Pendings</td>
            <td style="padding: 15px;">$<?= $total_pendings; ?>/-</td>
         </tr>
         <tr style="background-color: #f9f9f9;">
            <td style="padding: 15px;">Completed Orders</td>
            <td style="padding: 15px;">$<?= $total_completed; ?>/-</td>
         </tr>
         <tr>
            <td style="padding: 15px;">Orders Placed</td>
            <td style="padding: 15px;"><?= $number_of_orders; ?></td>
         </tr>
         <tr style="background-color: #f9f9f9;">
            <td style="padding: 15px;">Products Added</td>
            <td style="padding: 15px;"><?= $number_of_products; ?></td>
         </tr>
         <tr>
            <td style="padding: 15px;">Total Users</td>
            <td style="padding: 15px;"><?= $number_of_users; ?></td>
         </tr>
         <tr style="background-color: #f9f9f9;">
            <td style="padding: 15px;">Total Admins</td>
            <td style="padding: 15px;"><?= $number_of_admins; ?></td>
         </tr>
         <tr>
            <td style="padding: 15px;">Total Accounts</td>
            <td style="padding: 15px;"><?= $number_of_accounts; ?></td>
         </tr>
      </tbody>
   </table>
</div>



<div class="recent-activity">
      <h2>📝 Recent Activities</h2>
      <ul class="activity-log">
        <li>🔓 Logged in</li>
        <li>📦 Checked inventory updates</li>
        <li>📤 Sent notification to all users</li>
        <li>🔐 Updated user access permissions</li>
        <li>🛒 Reviewed order</li>
      </ul>
    </div>

  </div>
<script src="js/script.js"></script>


</body>
</html>
