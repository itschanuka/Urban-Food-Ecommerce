<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit();
}

// ADD TO WISHLIST
if(isset($_POST['add_to_wishlist'])){
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

   if($check_wishlist->fetch(PDO::FETCH_ASSOC)){
      $message[] = 'Already added to wishlist!';
   } elseif($check_cart->fetch(PDO::FETCH_ASSOC)){
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

// REMOVE FROM WISHLIST
if(isset($_GET['remove_id'])){
   $remove_id = $_GET['remove_id'];
   $delete = $conn->prepare("DELETE FROM WISHLIST WHERE ID = :id AND USER_ID = :user_id");
   $delete->execute([
      ':id' => $remove_id,
      ':user_id' => $user_id
   ]);
   $message[] = 'Removed from wishlist!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Wishlist</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/components.css">
   <style>
      /* Wishlist Section Styles */
.wishlist {
    padding: 30px;
    background-color: #f9f9f9;
    color: #333;
}

.wishlist .title {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 30px;
    font-weight: bold;
    color: #333;
    text-transform: uppercase;
}

.wishlist .box-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    justify-content: center;
}

.wishlist .box {
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
}

.wishlist .box:hover {
    transform: scale(1.05);
}

.wishlist .box img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.wishlist .box .name {
    padding: 15px;
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    text-align: center;
}

.wishlist .box .price {
    padding: 10px 15px;
    font-size: 1.1rem;
    font-weight: 500;
    color: #e74c3c;
    text-align: center;
}

.wishlist .box .price span {
    font-size: 1.3rem;
    color: #2c3e50;
}

.wishlist .box .fas {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 1.5rem;
    color: #333;
    background-color: rgba(255, 255, 255, 0.7);
    border-radius: 50%;
    padding: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.wishlist .box .fas:hover {
    background-color: #f39c12;
}

.wishlist .box .btn {
    display: block;
    width: 100%;
    padding: 12px;
    text-align: center;
    background-color: #e74c3c;
    color: white;
    font-size: 1rem;
    font-weight: bold;
    text-transform: uppercase;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.wishlist .box .btn:hover {
    background-color: #c0392b;
}

.wishlist .box .btn:active {
    transform: scale(0.98);
}

      
      </style>

</head>
<body>

<?php include 'header.php'; ?>

<section class="wishlist">
   <h1 class="title">Your Wishlist</h1>
   <div class="box-container">
      <?php
      // Fetch Wishlist Products
      $select_wishlist = $conn->prepare("SELECT * FROM WISHLIST WHERE USER_ID = :user_id");
      $select_wishlist->execute([':user_id' => $user_id]);

      while($row = $select_wishlist->fetch(PDO::FETCH_ASSOC)){
         $product_price = $row['PRICE']; // Directly fetch price from wishlist table
      ?>
      <div class="box">
         <img src="uploaded_img/<?= htmlspecialchars($row['IMAGE']); ?>" alt="">
         <div class="name"><?= htmlspecialchars($row['NAME']); ?></div>
         <div class="price" style="color: black;">$<span><?= htmlspecialchars($product_price); ?></span>/-</div>
         <a href="view_page.php?pid=<?= $row['PID']; ?>" class="fas fa-eye"></a>
         <a href="wishlist.php?remove_id=<?= $row['ID']; ?>" class="btn">Remove</a>
      </div>
      <?php } ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>
