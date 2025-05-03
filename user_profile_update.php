<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

// Fetch current user profile
$fetch_profile = $conn->prepare("SELECT * FROM USERS WHERE ID = ?");
$fetch_profile->execute([$user_id]);
$fetch_profile = $fetch_profile->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['update_profile'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);

   $update_profile = $conn->prepare("UPDATE USERS SET NAME = ?, EMAIL = ? WHERE ID = ?");
   $update_profile->execute([$name, $email, $user_id]);

   // Handle image
   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;
   $old_image = $_POST['old_image'];

   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'Image size is too large!';
      }else{
         $update_image = $conn->prepare("UPDATE USERS SET IMAGE = ? WHERE ID = ?");
         $update_image->execute([$image, $user_id]);
         if($update_image){
            move_uploaded_file($image_tmp_name, $image_folder);
            if(file_exists('uploaded_img/'.$old_image) && $old_image != $image){
               unlink('uploaded_img/'.$old_image);
            }
            $message[] = 'Image updated successfully!';
         }
      }
   }

   // Handle password change
   $old_pass_db = $_POST['old_pass']; // Stored hash from DB
   $update_pass = md5($_POST['update_pass']);
   $new_pass = md5($_POST['new_pass']);
   $confirm_pass = md5($_POST['confirm_pass']);

   if(!empty($_POST['update_pass']) && !empty($_POST['new_pass']) && !empty($_POST['confirm_pass'])){
      if($update_pass != $old_pass_db){
         $message[] = 'Old password not matched!';
      } elseif($new_pass != $confirm_pass){
         $message[] = 'Confirm password not matched!';
      } else {
         $update_pass_query = $conn->prepare("UPDATE USERS SET PASSWORD = ? WHERE ID = ?");
         $update_pass_query->execute([$confirm_pass, $user_id]);
         $message[] = 'Password updated successfully!';
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
   <title>Update User Profile</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/components.css">
   <style>
/* General page styles */
body {
   font-family: 'Poppins', sans-serif;
   background-color: #f2f5fa;
   margin: 0;
   padding: 0;
}

section.update-profile {
   background-color: #fff;
   border-radius: 15px;
   box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
   max-width: 900px;
   margin: 50px auto;
   padding: 40px 60px;
   font-size: 16px;
   color: #333;
   transition: all 0.3s ease-in-out;
}

section.update-profile:hover {
   transform: scale(1.01); /* Smooth zoom-in effect */
}

.update-profile .title {
   text-align: center;
   font-size: 32px;
   color: #333;
   margin-bottom: 40px;
   font-weight: 600;
}

/* Message box styles */
.message {
   background-color: #ffe5b4;
   color: #8a6d3b;
   padding: 15px;
   margin-bottom: 20px;
   border-radius: 8px;
   text-align: center;
   font-weight: 500;
}

/* Input Box styling */
.inputBox {
   margin-bottom: 30px;
   padding: 20px;
   background: #f7f7f7;
   border-radius: 12px;
   border: 1px solid #e0e0e0;
   width: 100%;
   transition: all 0.3s ease;
}

.inputBox:hover {
   border-color: #007bff;
}

.inputBox input {
   width: 100%;
   padding: 15px;
   border-radius: 8px;
   border: 1px solid #ddd;
   background-color: #fafafa;
   margin-top: 10px;
   font-size: 16px;
   transition: all 0.3s ease;
}

.inputBox input:focus {
   border-color: #007bff;
   outline: none;
   background-color: #eaf2ff;
}

.inputBox span {
   font-weight: 600;
   color: #333;
   margin-bottom: 10px;
   display: block;
   font-size: 14px;
}

/* Profile Picture styling */
form img {
   display: block;
   margin: 0 auto;
   border-radius: 50%;
   width: 120px;
   height: 120px;
   object-fit: cover;
   border: 3px solid #007bff;
   margin-bottom: 30px;
}

/* Button and Link styles */
.flex-btn {
   display: flex;
   justify-content: space-between;
   margin-top: 40px;
   gap: 20px;
}

.flex-btn .btn, .flex-btn .option-btn {
   padding: 15px 30px;
   border-radius: 10px;
   text-align: center;
   font-size: 16px;
   cursor: pointer;
   transition: background-color 0.3s, transform 0.2s;
}

.flex-btn .btn {
   background: linear-gradient(135deg, #007bff, #00b8d4);
   color: white;
   border: none;
}

.flex-btn .btn:hover {
   background: linear-gradient(135deg, #005fa3, #008c9e);
   transform: translateY(-3px);
}

.flex-btn .option-btn {
   background-color: #f1f1f1;
   color: #007bff;
   text-decoration: none;
   border: 1px solid #007bff;
}

.flex-btn .option-btn:hover {
   background-color: #f7f7f7;
   color: #005fa3;
   transform: translateY(-3px);
}

/* Flexbox for form layout */
.flex {
   display: flex;
   justify-content: space-between;
   gap: 40px;
   margin-bottom: 40px;
}

.flex .inputBox {
   flex: 1;
}

/* Date and Time Card styling */
.datetime-card {
   width: 100%;
   margin-top: 40px;
   background: linear-gradient(145deg, #f7f7f7, #ffffff);
   border-radius: 12px;
   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
   padding: 30px;
   text-align: center;
   font-family: 'Poppins', sans-serif;
}

.datetime-card h2 {
   font-size: 26px;
   color: #007bff;
   margin-bottom: 20px;
   font-weight: 600;
}

.datetime {
   display: flex;
   flex-direction: column;
   gap: 15px;
}

.datetime div {
   font-size: 18px;
   color: #555;
}

#current-time {
   font-size: 30px;
   font-weight: bold;
   color: #222;
   letter-spacing: 0.5px;
}

#greeting {
   margin-top: 10px;
   font-size: 20px;
   color: #888;
}

</style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="update-profile">

   <h1 class="title">Update Profile</h1>

   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '<div class="message">'.$msg.'</div>';
      }
   }
   ?>

<section class="datetime-card">
   <h2>📅 Date & Time</h2>
   <div class="datetime">
      <div id="current-day">--</div>
      <div id="current-date">--</div>
      <div id="current-time">--</div>
      <div id="greeting">--</div>
   </div>
</section>





   <form action="" method="POST" enctype="multipart/form-data">
      <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['IMAGE']); ?>" alt="">
      <div class="flex">
         <div class="inputBox">
            <span>Username :</span>
            <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['NAME']); ?>" placeholder="Update username" required class="box">
            <span>Email :</span>
            <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['EMAIL']); ?>" placeholder="Update email" required class="box">
            <span>Update Picture :</span>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_profile['IMAGE']); ?>">
         </div>
         <div class="inputBox">
            <input type="hidden" name="old_pass" value="<?= htmlspecialchars($fetch_profile['PASSWORD']); ?>">
            <span>Old Password :</span>
            <input type="password" name="update_pass" placeholder="Enter previous password" class="box">
            <span>New Password :</span>
            <input type="password" name="new_pass" placeholder="Enter new password" class="box">
            <span>Confirm Password :</span>
            <input type="password" name="confirm_pass" placeholder="Confirm new password" class="box">
         </div>
      </div>
      <div class="flex-btn">
         <input type="submit" class="btn" value="Update Profile" name="update_profile">
         <a href="home.php" class="option-btn">Go Back</a>
      </div>
   </form>

</section>




<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
<script>
function updateDateTime() {
   const now = new Date();
   
   // Format day
   const days = ["Sunday 🌞", "Monday 📚", "Tuesday 🚀", "Wednesday 🧠", "Thursday 💼", "Friday 🎉", "Saturday 🛌"];
   const dayName = days[now.getDay()];
   
   // Format date
   const options = { year: 'numeric', month: 'long', day: 'numeric' };
   const date = now.toLocaleDateString(undefined, options);
   
   // Format time
   let hours = now.getHours();
   let minutes = now.getMinutes();
   let seconds = now.getSeconds();
   const ampm = hours >= 12 ? 'PM' : 'AM';
   hours = hours % 12 || 12; // convert 0 to 12
   minutes = minutes < 10 ? '0' + minutes : minutes;
   seconds = seconds < 10 ? '0' + seconds : seconds;
   const time = `${hours}:${minutes}:${seconds} ${ampm}`;
   
   // Greeting based on time
   let greeting = '';
   if (now.getHours() < 12) {
      greeting = "🌞 Good Morning!";
   } else if (now.getHours() < 18) {
      greeting = "☀️ Good Afternoon!";
   } else {
      greeting = "🌙 Good Evening!";
   }

   document.getElementById('current-day').textContent = dayName;
   document.getElementById('current-date').textContent = date;
   document.getElementById('current-time').textContent = time;
   document.getElementById('greeting').textContent = greeting;
}

setInterval(updateDateTime, 1000);
updateDateTime(); // Initial call
</script>

</body>
</html>
