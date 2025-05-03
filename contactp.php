<?php
// -------------------------------------------
// ERROR REPORTING & LOGGING SETUP
// -------------------------------------------
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/error.log");

// -------------------------------------------
// REQUIREMENTS
// -------------------------------------------
require 'vendor/autoload.php'; // Load MongoDB & Dompdf PHP drivers

use MongoDB\Client;

// -------------------------------------------
// MONGODB CONNECTION
// -------------------------------------------
$mongoError = false;
$client = null;

try {
    $client = new Client("mongodb://localhost:27017");
    $contactCollection = $client->urbanfood->contact_us; // DIFFERENT COLLECTION
} catch (Exception $e) {
    $mongoError = true;
}

// -------------------------------------------
// HANDLE FORM SUBMISSIONS
// -------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'contact') {
        $contact = [
            'name'       => $_POST['name'],
            'email'      => $_POST['email'],
            'phone'      => $_POST['phone'],
            'reason'     => $_POST['reason'],
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        $contactCollection->insertOne($contact);

        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Contact Us – UrbanFood</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #e6f4ea;
      margin: 0;
      padding: 0;
    }

    .main-container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    h2 {
      text-align: center;
      color: #2e7d32;
      margin-bottom: 20px;
    }

    input, textarea {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      font-size: 1rem;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    textarea {
      min-height: 100px;
      resize: vertical;
    }

    button {
      width: 100%;
      padding: 12px;
      margin-top: 15px;
      font-weight: bold;
      font-size: 1rem;
      background-color: #4caf50;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    button:hover {
      background-color: #388e3c;
    }

    .error-box, .success-box {
      background: #ffeaea;
      color: #a94442;
      padding: 15px;
      border: 1px solid #f5c6cb;
      border-radius: 6px;
      margin-top: 20px;
      text-align: center;
    }

    .success-box {
      background: #e8f5e9;
      color: #2e7d32;
      border: 1px solid #c8e6c9;
    }

    footer {
      text-align: center;
      font-size: 0.9rem;
      color: #7d8b7f;
      padding-top: 30px;
    }

    .feedback-btn {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 10px 20px;
      background-color: #1e88e5;
      color: white;
      font-size: 1rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      z-index: 1000;
    }

    .feedback-btn:hover {
      background-color: #1565c0;
    }
  </style>
</head>
<body>

  <!-- Back to Feedback Button -->
  <a href="contact.php" class="feedback-btn">Back to contact</a>

  <div class="main-container">
    <h2>Contact Us</h2>

    <?php if ($mongoError): ?>
      <div class="error-box">
          Oops! Our contact system is currently offline. Please try again later.
      </div>
    <?php elseif (isset($_GET['success'])): ?>
      <div class="success-box">
          Thank you! Your message has been sent successfully.
      </div>
    <?php endif; ?>

    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
      <input type="hidden" name="action" value="contact" />
      <input type="text" name="name" placeholder="Your Full Name" required />
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="text" name="phone" placeholder="Phone Number" required />
      <textarea name="reason" placeholder="Reason for Contacting Us..." required></textarea>
      <button type="submit">Submit</button>
    </form>

  </div>

  <footer>
    &copy; <?= date('Y') ?> UrbanFood — Empowering Local Farmers 🌱
  </footer>

</body>
</html>
