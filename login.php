<?php

@include 'config.php';

session_start();

if (isset($_POST['submit'])) {

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_EMAIL);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $sql = "SELECT * FROM USERS WHERE EMAIL = :email AND PASSWORD = :password";
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':email', $email);
   $stmt->bindParam(':password', $pass);
   $stmt->execute();
   
   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($row) {
      $userType = strtoupper(trim($row['USER_TYPE']));

      if ($userType === 'ADMIN') {
         $_SESSION['admin_id'] = $row['ID'];
         header('Location: admin_page.php');
         exit;
      } elseif ($userType === 'USER') {
         $_SESSION['user_id'] = $row['ID'];
         header('Location: home.php');
         exit;
      } else {
         $message[] = 'No user type matched!';
      }
   } else {
      $message[] = 'Incorrect email or password!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Urban Food E-Commerce | Login</title>

   <!-- Font awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
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
      <h1>FOOD MART 🛒</h1>
      <p>Freshness at Your Doorstep - Fruits 🍎, Vegetables 🥦, Seafood 🐟 and More!</p>
      <a href="#loginForm" class="scroll-down"><i class="fas fa-arrow-down"></i></a>
   </div>
</section>


<!-- Login Section -->
<section id="loginForm" class="form-section">
   <div class="bubbles">
      <!-- Multiple Bubbles -->
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

   <form action="" method="POST" class="login-form">
      <h3>Login to FOOD MART 🥑</h3>

      <div class="input-group">
         <i class="fas fa-envelope"></i>
         <input type="email" name="email" placeholder="Email Address 📧" required>
      </div>

      <div class="input-group">
         <i class="fas fa-lock"></i>
         <input type="password" name="pass" placeholder="Password 🔒" required>
      </div>

      <input type="submit" value="Login Now 🚀" class="btn" name="submit">

      <p class="register-text">New here? <a href="register.php">Register Now</a></p>

   </form>
</section>

</body>
</html>
