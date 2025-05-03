<?php
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/error.log");

require 'vendor/autoload.php';

$client     = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->urbanfood->feedback;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = [
        'name'       => $_POST['name'],
        'email'      => $_POST['email'] ?? '',
        'type'       => $_POST['type'],
        'message'    => $_POST['message'],
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    $collection->insertOne($feedback);
}

header("Location: feedback.php");
exit;
