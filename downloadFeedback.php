<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use MongoDB\Client;

// 1. Connect to MongoDB
$mongo = new Client("mongodb://localhost:27017");
$collection = $mongo->urbanfood->feedback;

// 2. Fetch the latest 5 feedbacks
$feedbacks = $collection->find([], [
    'sort' => ['created_at' => -1],
    'limit' => 5
]);

// 3. Prepare HTML content for the PDF
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

// 4. Configure dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// 5. Load HTML into dompdf and render
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 6. Output PDF as a download
$dompdf->stream("feedback_summary.pdf", ["Attachment" => true]);
exit;
