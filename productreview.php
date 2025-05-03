<?php
// -------------------------------------------
// ERROR REPORTING & LOGGING SETUP
// -------------------------------------------
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/error.log");

// -------------------------------------------
// MONGODB CONNECTION
// -------------------------------------------
require 'vendor/autoload.php'; // Load MongoDB PHP driver

$mongoError = false;
$latestReviews = [];

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $collection = $client->urbanfood->productreview;
} catch (Exception $e) {
    $mongoError = true;
}

// -------------------------------------------
// HANDLE AJAX FORM SUBMISSION
// -------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    header('Content-Type: application/json');

    if ($mongoError) {
        echo json_encode(['success' => false, 'error' => 'Database connection error']);
        exit;
    }

    $email = $_POST['email'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $rating = (int)($_POST['rating'] ?? 0);
    $description = $_POST['description'] ?? '';

    if (empty($email) || empty($product_name) || empty($rating) || empty($description)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    try {
        $collection->insertOne([
            'email' => $email,
            'product_name' => $product_name,
            'rating' => $rating,
            'description' => $description,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------
// FETCH REVIEWS FOR PAGE LOAD
// -------------------------------------------
if (!$mongoError) {
    try {
        $latestReviews = $collection->find([], [
            'sort' => ['created_at' => -1],
            'limit' => 5
        ])->toArray();
    } catch (Exception $e) {
        $mongoError = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Product Reviews – UrbanFood</title>
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

    h2, h3 {
      text-align: center;
      color: #2e7d32;
      margin-bottom: 20px;
    }

    input, select, textarea {
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

    .feedback-card {
      background-color: #f1f8e9;
      padding: 15px 20px;
      border-radius: 10px;
      margin-top: 15px;
      border-left: 5px solid #81c784;
    }

    .feedback-card strong {
      color: #1b5e20;
    }

    .feedback-card em {
      font-style: normal;
      color: #555;
    }

    .feedback-card small {
      color: #777;
      display: block;
      margin-top: 8px;
    }

    .error-box {
      background: #ffeaea;
      color: #a94442;
      padding: 15px;
      border: 1px solid #f5c6cb;
      border-radius: 6px;
      margin-top: 20px;
      text-align: center;
    }

    .success-box {
      background: #e6ffea;
      color: #2e7d32;
      padding: 15px;
      border: 1px solid #c8e6c9;
      border-radius: 6px;
      margin-top: 20px;
      text-align: center;
    }

    footer {
      text-align: center;
      font-size: 0.9rem;
      color: #7d8b7f;
      padding-top: 30px;
    }

    .contact-btn {
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

    .contact-btn:hover {
      background-color: #1565c0;
    }
  </style>
</head>
<body>

  <!-- Contact Button -->
  <a href="contact.php" class="contact-btn">Back to Contact</a>

  <div class="main-container">
    <h2>Submit Your Product Review</h2>

    <div id="message"></div>

    <form id="reviewForm">
      <input type="hidden" name="action" value="submit_review">
      <input type="email" name="email" placeholder="Your Email" required />
      <input type="text" name="product_name" placeholder="Product Name" required />
      <select name="rating" required>
        <option value="">Select Rating</option>
        <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
        <option value="4">⭐⭐⭐⭐ - Good</option>
        <option value="3">⭐⭐⭐ - Average</option>
        <option value="2">⭐⭐ - Poor</option>
        <option value="1">⭐ - Very Bad</option>
      </select>
      <textarea name="description" placeholder="Write your review here..." required></textarea>
      <button type="submit">Submit Review</button>
    </form>

    <h3>Recent Reviews</h3>

    <?php if ($mongoError): ?>
      <div class="error-box">
          Sorry, our review system is currently unavailable. Please try again later.
      </div>
    <?php else: ?>
      <?php if (!empty($latestReviews)): ?>
        <?php foreach ($latestReviews as $review): ?>
          <div class="feedback-card">
            <strong><?= htmlspecialchars($review['product_name']) ?></strong> 
            <em>by <?= htmlspecialchars($review['email']) ?></em>
            <div>
              <?= str_repeat('⭐', (int)$review['rating']) ?> (<?= (int)$review['rating'] ?>/5)
            </div>
            <div><?= nl2br(htmlspecialchars($review['description'])) ?></div>
            <small><?= $review['created_at']->toDateTime()->format('Y-m-d H:i') ?></small>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="error-box">No reviews found.</div>
      <?php endif; ?>
    <?php endif; ?>

  </div>

  <footer>
    &copy; <?= date('Y') ?> UrbanFood — Quality You Can Trust 🌿
  </footer>

  <!-- AJAX Form Submit Script -->
  <script>
  document.getElementById('reviewForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    try {
      const response = await fetch('', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();
      const messageDiv = document.getElementById('message');

      if (result.success) {
        messageDiv.innerHTML = '<div class="success-box">Review submitted successfully! Please refresh to see it.</div>';
        form.reset();
      } else {
        messageDiv.innerHTML = '<div class="error-box">' + (result.error || 'Something went wrong') + '</div>';
      }
    } catch (error) {
      document.getElementById('message').innerHTML = '<div class="error-box">Error submitting the review.</div>';
    }
  });
  </script>

</body>
</html>
