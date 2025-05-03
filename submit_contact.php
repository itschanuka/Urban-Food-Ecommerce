<?php
// submit_contact.php

require 'vendor/autoload.php'; // Load Composer's MongoDB library

try {
    // Connect to MongoDB
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    $db = $mongoClient->urbanfood; // Database name (urbanfood)
    $contactsCollection = $db->contacts; // Collection name (contacts)

    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Sanitize and collect the form data
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $phone = htmlspecialchars(trim($_POST['phone']));
        $subject = htmlspecialchars(trim($_POST['subject']));
        $reason = htmlspecialchars(trim($_POST['reason']));

        // Basic validation
        if (empty($name) || empty($email) || empty($phone) || empty($reason)) {
            throw new Exception("Please fill in all required fields.");
        }

        // Insert into MongoDB
        $insertResult = $contactsCollection->insertOne([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'reason' => $reason,
            'submitted_at' => new MongoDB\BSON\UTCDateTime()
        ]);

        if ($insertResult->getInsertedCount() == 1) {
            // Redirect to a thank you page or show success message
            echo "<script>alert('Thank you! Your message has been sent successfully.'); window.location.href='contact.html';</script>";
        } else {
            throw new Exception("Failed to send message. Please try again.");
        }
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
}
?>
