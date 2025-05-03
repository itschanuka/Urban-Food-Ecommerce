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
require 'vendor/autoload.php'; // Load MongoDB PHP driver

use MongoDB\Client;

// -------------------------------------------
// MONGODB CONNECTION
// -------------------------------------------
$mongoError = false;
$client = null;
$messages = [];

try {
    $client = new Client("mongodb://localhost:27017");
    $collection = $client->urbanfood->contact_messages;

    // Fetch all messages from MongoDB
    $messages = $collection->find([], [
        'sort' => ['created_at' => -1]  // Sort by date (most recent first)
    ])->toArray();
} catch (Exception $e) {
    $mongoError = true;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin - Contact Messages</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        /* Add your CSS styles here */
    </style>
</head>
<body>

    <div class="main-container">
        <h2>Submitted Contact Messages</h2>

        <?php if ($mongoError): ?>
            <div class="error-box">
                Oops! We couldn't fetch the messages at this time. Please try again later.
            </div>
        <?php else: ?>
            <?php if (empty($messages)): ?>
                <div class="error-box">No messages found.</div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-card">
                        <strong><?= htmlspecialchars($message['name']) ?></strong>
                        <em>(<?= htmlspecialchars($message['email']) ?>)</em><br>
                        <strong>Phone:</strong> <?= htmlspecialchars($message['phone']) ?><br>
                        <strong>Reason:</strong> <?= htmlspecialchars($message['reason']) ?><br>
                        <small><?= $message['created_at']->toDateTime()->format('Y-m-d H:i') ?></small>
                        <p><strong>Message:</strong><br><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer>
        &copy; <?= date('Y') ?> UrbanFood — Empowering Local Farmers 🌱
    </footer>

</body>
</html>
