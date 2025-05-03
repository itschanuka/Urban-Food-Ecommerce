<?php

include 'config.php';

if (isset($_POST['submit'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = md5($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;

   $select = $conn->prepare("SELECT * FROM USERS WHERE EMAIL = :email");
   $select->bindParam(':email', $email);
   $select->execute();

   if ($select->fetch(PDO::FETCH_ASSOC)) {
      $message[] = 'User email already exists!';
   } else {
      if ($pass != $cpass) {
         $message[] = 'Confirm password not matched!';
      } else {
         $insert = $conn->prepare("INSERT INTO USERS (NAME, EMAIL, PASSWORD, IMAGE) VALUES (:name, :email, :password, :image)");
         $insert->bindParam(':name', $name);
         $insert->bindParam(':email', $email);
         $insert->bindParam(':password', $pass);
         $insert->bindParam(':image', $image);

         if ($insert->execute()) {
            if ($image_size > 2000000) {
               $message[] = 'Image size is too large!';
            } else {
               move_uploaded_file($image_tmp_name, $image_folder);
               $message[] = 'Registered successfully!';
               header('location:login.php');
               exit;
            }
         }
      }
   }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/login.css">

</head>
<body>

<?php

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>
   
   <section class="home">
   <img src="images/foodmart.jpg" alt="Food Mart" class="home-img">
   <div class="home-content">
      <h1>Urban Food E-Commerce  🛒</h1>
      <p>Freshness at Your Doorstep - Fruits 🍎, Vegetables 🥦, Seafood 🐟 and More!</p>
      <a href="#RegisterForm" class="scroll-down"><i class="fas fa-arrow-down"></i></a>
   </div>
</section>


<!-- Login Section -->
<section class="form-section" id="RegisterForm">


<div class="bubbles">
   <div class="bubble"></div>
   <div class="bubble"></div>
   <div class="bubble"></div>
   <div class="bubble"></div>
   <div class="bubble"></div>
   <div class="bubble"></div>
   <div class="bubble"></div>
   <div class="bubble"></div>
   <div class="bubble"></div>
</div>

<form action="" enctype="multipart/form-data" method="POST" class="login-form">
   <h3>Register Now</h3>

   <div class="input-group">
      <i class="fas fa-user"></i>
      <input type="text" name="name" placeholder="Enter your name" required>
   </div>

   <div class="input-group">
      <i class="fas fa-envelope"></i>
      <input type="email" name="email" placeholder="Enter your email" required>
   </div>

   <div class="input-group">
      <i class="fas fa-lock"></i>
      <input type="password" name="pass" placeholder="Enter your password" required>
   </div>

   <div class="input-group">
      <i class="fas fa-lock"></i>
      <input type="password" name="cpass" placeholder="Confirm your password" required>
   </div>

   <div class="input-group">
      <i class="fas fa-image"></i>
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" required>
   </div>

   <input type="submit" value="Register Now" class="btn" name="submit">

   <div class="register-text">
      <p>Already have an account? <a href="login.php">Login now</a></p>
   </div>
</form>

</section>



</body>
</html>