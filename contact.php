<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Contact</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      body {
         font-family: 'Arial', sans-serif;
         background-color: #e0f7fa; /* Light blue background */
         margin: 0;
         padding: 0;
      }

      .header-section {
         background:rgb(19, 156, 197); /* Dark blue header */
         color: white;
         padding: 40px 0;
         text-align: center;
         box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      }

      .header-section h1 {
         font-size: 3rem;
         margin: 0;
      }

      .card-container {
         display: flex;
         justify-content: center;
         gap: 2rem;
         flex-wrap: wrap;
         padding: 3rem;
         margin-top: 2rem;
      }

      .card {
         background: #ffffff; /* White background for the cards */
         border-radius: 12px;
         box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
         padding: 2rem;
         width: 280px;
         text-align: center;
         transition: transform 0.3s, box-shadow 0.3s;
         text-decoration: none;
         color:rgb(41, 138, 228); /* Dark blue text */
         border: 2px solid #b2dfdb; /* Light blue border */
      }

      .card:hover {
         transform: translateY(-12px);
         box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
      }

      .card i {
         font-size: 4rem;
         margin-bottom: 1rem;
         color:rgb(46, 128, 236); /* Dark blue icon */
      }

      .card h3 {
         font-size: 1.6rem;
         margin-bottom: 1rem;
         font-weight: bold;
      }

      .card p {
         font-size: 1.1rem;
         color:rgb(38, 139, 223);
      }

      .extra-info {
         background:rgb(26, 112, 211); /* Dark blue background for the info section */
         color: white;
         padding: 40px;
         text-align: center;
         margin-top: 3rem;
         border-radius: 10px;
         box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      }

      .extra-info h2 {
         font-size: 2.5rem;
         margin-bottom: 1rem;
      }

      .extra-info p {
         font-size: 1.2rem;
         max-width: 700px;
         margin: 0 auto;
      }

   </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="header-section">
   <h1>We're Here to Help! 🤝</h1>
   <p>Your feedback, questions, and reviews matter to us. Choose an option below and let's stay connected!</p>
</section>

<section class="contact">
   <div class="card-container">
      <a href="feedbackpack.php" class="card">
         <i class="fas fa-comment-dots"></i>
         <h3>Feedback ✨</h3>
         <p>Share your thoughts and help us improve. Your feedback is priceless!</p>
      </a>

      <a href="contactp.php" class="card">
         <i class="fas fa-phone-alt"></i>
         <h3>Contact Us 📞</h3>
         <p>Have questions or need support? We’re just a message away!</p>
      </a>

      <a href="productreview.php" class="card">
         <i class="fas fa-star"></i>
         <h3>Product Review ⭐</h3>
         <p>Tell us what you think about our products! Your review helps others.</p>
      </a>
   </div>
</section>

<section class="extra-info">
   <h2>Why Choose Us?</h2>
   <p>We aim to provide top-notch service and quality products. Whether you're looking for feedback, want to contact us, or share a product review, we strive to ensure your experience is smooth and beneficial. Your trust is what drives us to keep improving!</p>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
