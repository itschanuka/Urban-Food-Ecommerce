<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login.php');
   exit();
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_users = $conn->prepare("DELETE FROM USERS WHERE ID = :id");
   $delete_users->execute([':id' => $delete_id]);
   header('location:admin_users.php');
   exit();
}

if (isset($_GET['download']) && $_GET['download'] == 'csv') {
   header('Content-Type: text/csv');
   header('Content-Disposition: attachment;filename="users.csv"');

   $output = fopen('php://output', 'w');
   fputcsv($output, array('ID', 'Name', 'Email', 'User Type'));

   $select_users = $conn->prepare("SELECT * FROM USERS");
   $select_users->execute();
   while ($row = $select_users->fetch(PDO::FETCH_ASSOC)) {
      fputcsv($output, array($row['ID'], $row['NAME'], $row['EMAIL'], $row['USER_TYPE']));
   }
   fclose($output);
   exit();
}

if (isset($_GET['download']) && $_GET['download'] == 'xml') {
   header('Content-Type: text/xml');
   header('Content-Disposition: attachment;filename="users.xml"');

   $select_users = $conn->prepare("SELECT * FROM USERS");
   $select_users->execute();

   echo '<?xml version="1.0" encoding="UTF-8"?>';
   echo '<users>';
   while ($row = $select_users->fetch(PDO::FETCH_ASSOC)) {
      echo '<user>';
      echo '<id>' . htmlspecialchars($row['ID']) . '</id>';
      echo '<name>' . htmlspecialchars($row['NAME']) . '</name>';
      echo '<email>' . htmlspecialchars($row['EMAIL']) . '</email>';
      echo '<user_type>' . htmlspecialchars($row['USER_TYPE']) . '</user_type>';
      echo '</user>';
   }
   echo '</users>';
   exit();
}

// Call PL/SQL procedure to get the counts of admins, users, and total users
$admin_count = 0;
$user_count = 0;

$stmt = $conn->prepare("BEGIN get_user_counts(:admin_count, :user_count); END;");
$stmt->bindParam(':admin_count', $admin_count, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 50); // Adjust size if needed
$stmt->bindParam(':user_count', $user_count, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 50); // Adjust size if needed
$stmt->execute();

// Calculate total users
$total_users = $admin_count + $user_count;

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Accounts</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/admin_style.css">


   <style>
   .backup-section {
      margin: 30px 0;
      text-align: center;
   }

   .backup-section a {
      margin: 0 15px;
      padding: 15px 30px;
      background: #3498db;
      color: #fff;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      font-size: 18px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      transition: background 0.3s ease, transform 0.2s ease;
      display: inline-block;
   }

   .backup-section a:hover {
      background: #2980b9;
      transform: translateY(-2px);
   }

   /* Styling for Total Users section */
   .total-users {
      text-align: center;
      background-color: #f4f6f9;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 40px;
      font-family: 'Arial', sans-serif;
      border: 2px solid #3498db; /* Added border */
      transition: box-shadow 0.3s ease, transform 0.3s ease; /* Added transition for hover effect */
   }

   .total-users:hover {
      box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
      transform: translateY(-5px); /* Slight lift effect */
   }

   .total-users h3 {
      font-size: 26px;
      color: #2c3e50;
      font-weight: bold;
      margin-bottom: 15px;
   }

   .total-users h3 span {
      font-size: 20px;
      margin: 0 10px;
   }

   .total-users .count {
      font-size: 28px;
      font-weight: bold;
      color: #3498db;
      margin: 10px 0;
   }

   .total-users p {
      font-size: 18px;
      color: #7f8c8d;
   }

   .total-users .count span {
      color: #2ecc71;
   }

   .total-users .emoji {
      font-size: 20px;
      margin-top: 10px;
   }

   .total-users .info {
      color: #3498db;
      font-size: 18px;
      font-weight: bold;
   }

   /* Add hover effect for the backup buttons */
   .backup-section a:hover {
      background: #2980b9;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
   }
</style>


</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="user-accounts">

   <h1 class="title">User Accounts</h1>

   <!-- Display Total Users, Admins, and Regular Users -->
   <div class="total-users">
      <h3>
         <span>👥</span>
         Total Users: <span><?= htmlspecialchars($total_users); ?></span>
         <span>📊</span>
      </h3>
      <div class="count"><?= htmlspecialchars($total_users); ?></div>
      <p>
         <strong>Admins:</strong> <span><?= htmlspecialchars($admin_count); ?></span><br>
         <strong>Regular Users:</strong> <span><?= htmlspecialchars($user_count); ?></span>
      </p>
      <div class="emoji">
         <span>👨‍💻</span> Admins &nbsp; <span>👥</span> Users
      </div>
      <p class="info">Here are the statistics of the users in the system. 😊</p>
   </div>

   <!-- Backup Buttons Section -->
   <div class="backup-section">
      <a href="admin_users.php?download=csv">Download Users as CSV</a>
      <a href="admin_users.php?download=xml">Download Users as XML</a>
   </div>

   <div class="box-container">

      <?php
         $select_users = $conn->prepare("SELECT * FROM USERS");
         $select_users->execute();
         while($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)){
      ?>
      <div class="box" style="<?= ($fetch_users['ID'] == $admin_id) ? 'display:none' : ''; ?>">
         <img src="uploaded_img/<?= htmlspecialchars($fetch_users['IMAGE']); ?>" alt="">
         <p> User ID : <span><?= htmlspecialchars($fetch_users['ID']); ?></span></p>
         <p> Username : <span><?= htmlspecialchars($fetch_users['NAME']); ?></span></p>
         <p> Email : <span><?= htmlspecialchars($fetch_users['EMAIL']); ?></span></p>
         <p> User Type : <span style="color:<?= ($fetch_users['USER_TYPE'] == 'admin') ? 'orange' : ''; ?>"><?= htmlspecialchars($fetch_users['USER_TYPE']); ?></span></p>
         <a href="admin_users.php?delete=<?= $fetch_users['ID']; ?>" onclick="return confirm('Delete this user?');" class="delete-btn">Delete</a>
      </div>
      <?php
         }
      ?>
   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
