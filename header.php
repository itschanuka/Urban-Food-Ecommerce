<?php

if (isset($message)) {
   foreach ($message as $msg) {
      echo '
      <div class="message">
         <span>' . $msg . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">

   <div class="flex">

      <a href="" class="logo">Urban Food<span>.</span></a>

      <nav class="navbar">
         <a href="home.php">home</a>
         <a href="shop.php">shop</a>
         <a href="orders.php">orders</a>
         <a href="contact.php">contact</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <a href="search_page.php" class="fas fa-search"></a>
         <?php
            // Count the number of items in the cart and wishlist
            $count_cart_items = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = :user_id");
            $count_cart_items->execute([':user_id' => $user_id]);
            $cart_items = $count_cart_items->fetchColumn();

            $count_wishlist_items = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = :user_id");
            $count_wishlist_items->execute([':user_id' => $user_id]);
            $wishlist_items = $count_wishlist_items->fetchColumn();
         ?>
         <a href="wishlist.php"><i class="fas fa-heart"></i><span>(<?= $wishlist_items; ?>)</span></a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $cart_items; ?>)</span></a>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $select_profile->execute([':id' => $user_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="uploaded_img/<?= $fetch_profile['IMAGE']; ?>" alt="">
         <p><?= $fetch_profile['NAME']; ?></p>
         <a href="user_profile_update.php" class="btn">update profile</a>
         <a href="logout.php" class="delete-btn">logout</a>
         <div class="flex-btn">
            <a href="login.php" class="option-btn">login</a>
            <a href="register.php" class="option-btn">register</a>
         </div>
      </div>

   </div>

</header>
