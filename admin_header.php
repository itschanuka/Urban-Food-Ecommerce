<?php
if (isset($message)) {
   foreach ($message as $msg) {
      echo '
      <div class="message">
         <span>' . htmlspecialchars($msg) . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">

   <div class="flex">

      <a href="admin_page.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="admin_page.php">home</a>
         <a href="admin_products.php">products</a>
         <a href="admin_orders.php">orders</a>
         <a href="admin_users.php">users</a>
         <a href="contactadmin.php">messages</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
         try {
            $select_profile = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $select_profile->bindParam(':id', $admin_id, PDO::PARAM_INT);
            $select_profile->execute();
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

            if ($fetch_profile) {
         ?>
               <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['IMAGE']) ?>" alt="Profile Image">
               <p><?= htmlspecialchars($fetch_profile['NAME']) ?></p>
               <a href="admin_update_profile.php" class="btn">Update Profile</a>
               <a href="logout.php" class="delete-btn">Logout</a>
         <?php
            } else {
               echo '<p>User not found</p>';
            }
         } catch (PDOException $e) {
            echo '<p>Error fetching profile: ' . htmlspecialchars($e->getMessage()) . '</p>';
         }
         ?>
         <div class="flex-btn">
            <a href="login.php" class="option-btn">Login</a>
            <a href="register.php" class="option-btn">Register</a>
         </div>
      </div>

   </div>

</header>
