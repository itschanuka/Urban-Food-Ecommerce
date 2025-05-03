<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:login.php');
    exit;
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_message = $conn->prepare("DELETE FROM MESSAGE WHERE ID = :id");
    $delete_message->bindParam(':id', $delete_id);
    $delete_message->execute();
    header('location:admin_contacts.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Messages</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="messages">
   <h1 class="title">Messages</h1>

   <div class="box-container">

   <?php
      // Fetch messages
      $select_message = $conn->prepare("SELECT * FROM MESSAGE");
      $select_message->execute();

      if ($select_message->rowCount() > 0) {
         while ($fetch_message = $select_message->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <div class="box">
      <p> User ID : <span><?= htmlspecialchars($fetch_message['USER_ID'] ?? ''); ?></span> </p>
      <p> Name : <span><?= htmlspecialchars($fetch_message['NAME'] ?? ''); ?></span> </p>
      <p> Phone Number : <span><?= htmlspecialchars($fetch_message['PHONE_NUMBER'] ?? ''); ?></span> </p>
      <p> Email : <span><?= htmlspecialchars($fetch_message['EMAIL'] ?? ''); ?></span> </p>
      <p> Message : <span><?= nl2br(htmlspecialchars($fetch_message['MESSAGE'] ?? '')); ?></span> </p>
      <a href="admin_contacts.php?delete=<?= $fetch_message['ID']; ?>" onclick="return confirm('Delete this message?');" class="delete-btn">Delete</a>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">You have no messages!</p>';
      }
   ?>

   </div>
</section>

<script src="js/script.js"></script>

</body>
</html>
