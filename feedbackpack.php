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
use Dompdf\Dompdf;
use Dompdf\Options;

// -------------------------------------------
// MONGODB CONNECTION
// -------------------------------------------
$mongoError = false;
$client = null;
$latestFeedbacks = [];

try {
    $client = new Client("mongodb://localhost:27017");
    $collection = $client->urbanfood->feedback;
    $latestFeedbacks = $collection->find([], [
        'sort' => ['created_at' => -1],
        'limit' => 5
    ])->toArray();
} catch (Exception $e) {
    $mongoError = true;
}

// -------------------------------------------
// HANDLE FORM SUBMISSIONS
// -------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Insert new feedback
    if (isset($_POST['action']) && $_POST['action'] === 'insert') {
        $feedback = [
            'name'       => $_POST['name'],
            'email'      => $_POST['email'] ?? '',
            'type'       => $_POST['type'],
            'message'    => $_POST['message'],
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        $collection->insertOne($feedback);

        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // 2. Download feedback PDF
    if (isset($_POST['action']) && $_POST['action'] === 'download') {
        $feedbacks = $collection->find([], [
            'sort' => ['created_at' => -1],
            'limit' => 5
        ]);

        $html = "
          <h2 style='text-align:center; color: #4caf50;'>UrbanFood - Recent Feedback Summary</h2>
          <hr>
        ";

        foreach ($feedbacks as $fb) {
            $name = htmlspecialchars($fb['name']);
            $email = !empty($fb['email']) ? htmlspecialchars($fb['email']) : "N/A";
            $type = htmlspecialchars($fb['type']);
            $message = nl2br(htmlspecialchars($fb['message']));
            $date = $fb['created_at']->toDateTime()->format('Y-m-d H:i');

            $html .= "
              <p>
                <strong>Name:</strong> {$name}<br>
                <strong>Email:</strong> {$email}<br>
                <strong>Type:</strong> {$type}<br>
                <strong>Date:</strong> {$date}<br>
                <strong>Message:</strong><br>{$message}
              </p>
              <hr>
            ";
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("feedback_summary.pdf", ["Attachment" => true]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Customer Feedback – UrbanFood</title>
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

  <!-- Contact Button at Top Right Corner -->
  <a href="contact.php" class="contact-btn">Back to Contact</a>

  <div class="main-container">
    <h2>Share Your Feedback</h2>

    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
      <input type="hidden" name="action" value="insert" />
      <input type="text" name="name" placeholder="Your Full Name" required />
      <input type="email" name="email" placeholder="Email Address (optional)" />
      <select name="type" required>
        <option value="">Select Feedback Type</option>
        <option value="Suggestion">Suggestion</option>
        <option value="Complaint">Complaint</option>
        <option value="Praise">Praise</option>
        <option value="Other">Other</option>
      </select>
      <textarea name="message" placeholder="Write your message here..." required></textarea>
      <button type="submit">Send Feedback</button>
    </form>

    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" style="text-align: right; margin-top: 20px;">
      <input type="hidden" name="action" value="download" />
      <button type="submit" style="background-color: #1e88e5;">Download Feedback as PDF</button>
    </form>

    <h3>Recent Submissions</h3>

    <?php if ($mongoError): ?>
      <div class="error-box">
          Oops! Our feedback system is currently offline. Please try again later.
      </div>
    <?php else: ?>
      <?php if (!empty($latestFeedbacks)): ?>
        <?php foreach ($latestFeedbacks as $entry): ?>
          <div class="feedback-card">
            <strong><?= htmlspecialchars($entry['name']) ?></strong>
            <?php if (!empty($entry['email'])): ?>
              <em> (<?= htmlspecialchars($entry['email']) ?>)</em>
            <?php endif; ?>
            <div><?= nl2br(htmlspecialchars($entry['message'])) ?></div>
            <small>
              <?= $entry['created_at']->toDateTime()->format('Y-m-d H:i') ?> —
              <?= htmlspecialchars($entry['type']) ?>
            </small>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="error-box">No feedback found.</div>
      <?php endif; ?>
    <?php endif; ?>

  </div>

  <footer>
    &copy; <?= date('Y') ?> UrbanFood — Empowering Local Farmers 🌱
  </footer>

</body>
</html>
