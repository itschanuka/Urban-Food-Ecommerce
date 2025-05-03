<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('location:login.php');
   exit();
}

// ADD TO WISHLIST
if (isset($_POST['add_to_wishlist'])) {
   $pid = $_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = $_POST['p_price'];
   $p_image = $_POST['p_image'];

   $check_wishlist = $conn->prepare("SELECT * FROM WISHLIST WHERE NAME = :name AND USER_ID = :user_id");
   $check_wishlist->execute([
      ':name' => $p_name,
      ':user_id' => $user_id
   ]);

   $check_cart = $conn->prepare("SELECT * FROM CART WHERE NAME = :name AND USER_ID = :user_id");
   $check_cart->execute([
      ':name' => $p_name,
      ':user_id' => $user_id
   ]);

   if ($check_wishlist->fetch(PDO::FETCH_ASSOC)) {
      $message[] = 'Already added to wishlist!';
   } elseif ($check_cart->fetch(PDO::FETCH_ASSOC)) {
      $message[] = 'Already added to cart!';
   } else {
      $insert = $conn->prepare("INSERT INTO WISHLIST (USER_ID, PID, NAME, PRICE, IMAGE) 
                                VALUES (:user_id, :pid, :name, :price, :image)");
      $insert->execute([
         ':user_id' => $user_id,
         ':pid' => $pid,
         ':name' => $p_name,
         ':price' => $p_price,
         ':image' => $p_image
      ]);
      $message[] = 'Added to wishlist!';
   }
}

// ADD TO CART
if (isset($_POST['add_to_cart'])) {
   $pid = $_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = $_POST['p_price'];
   $p_image = $_POST['p_image'];
   $p_qty = $_POST['p_qty'];

   $check_cart = $conn->prepare("SELECT * FROM CART WHERE NAME = :name AND USER_ID = :user_id");
   $check_cart->execute([
      ':name' => $p_name,
      ':user_id' => $user_id
   ]);

   if ($check_cart->fetch(PDO::FETCH_ASSOC)) {
      $message[] = 'Already added to cart!';
   } else {
      $check_wishlist = $conn->prepare("SELECT * FROM WISHLIST WHERE NAME = :name AND USER_ID = :user_id");
      $check_wishlist->execute([
         ':name' => $p_name,
         ':user_id' => $user_id
      ]);

      if ($check_wishlist->fetch(PDO::FETCH_ASSOC)) {
         $delete = $conn->prepare("DELETE FROM WISHLIST WHERE NAME = :name AND USER_ID = :user_id");
         $delete->execute([
            ':name' => $p_name,
            ':user_id' => $user_id
         ]);
      }

      $insert_cart = $conn->prepare("INSERT INTO CART (USER_ID, PID, NAME, PRICE, QUANTITY, IMAGE) 
                                     VALUES (:user_id, :pid, :name, :price, :quantity, :image)");
      $insert_cart->execute([
         ':user_id' => $user_id,
         ':pid' => $pid,
         ':name' => $p_name,
         ':price' => $p_price,
         ':quantity' => $p_qty,
         ':image' => $p_image
      ]);
      $message[] = 'Added to cart!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Product View</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/view_page.css">
   <link rel="stylesheet" href="css/header.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="quick-view">
   <h1 class="title">Product Details</h1>

   <div class="box-container">
   <?php
      if (isset($_GET['pid'])) {
         $pid = $_GET['pid'];
         $select_product = $conn->prepare("SELECT * FROM PRODUCTS WHERE ID = :pid");
         $select_product->execute([':pid' => $pid]);

         if ($product = $select_product->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <form action="" method="POST" class="box">
      <div class="price">$<span><?= htmlspecialchars($product['PRICE']) ?></span>/-</div>
      <img src="uploaded_img/<?= htmlspecialchars($product['IMAGE']) ?>" alt="">
      <div class="name"><?= htmlspecialchars($product['NAME']) ?></div>
      <div class="details"><?= htmlspecialchars($product['DETAILS']) ?></div>
      <input type="hidden" name="pid" value="<?= $product['ID'] ?>">
      <input type="hidden" name="p_name" value="<?= htmlspecialchars($product['NAME']) ?>">
      <input type="hidden" name="p_price" value="<?= $product['PRICE'] ?>">
      <input type="hidden" name="p_image" value="<?= htmlspecialchars($product['IMAGE']) ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="Add to Wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
   </form>
   <?php
         } else {
            echo '<p class="empty">Product not found!</p>';
         }
      } else {
         echo '<p class="empty">No product ID specified!</p>';
      }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>
