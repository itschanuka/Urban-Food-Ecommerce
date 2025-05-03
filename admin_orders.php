<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
   header('location:login.php');
   exit();
}

$admin_id = $_SESSION['admin_id'];

// Handle updating payment status
if (isset($_POST['update_order'])) {
   $order_id = $_POST['order_id'];
   $update_payment = filter_var($_POST['update_payment'], FILTER_SANITIZE_STRING);

   $update_orders = $conn->prepare("UPDATE ORDERS SET PAYMENT_STATUS = ? WHERE ID = ?");
   $update_orders->execute([$update_payment, $order_id]);
   $message[] = 'Payment status has been updated!';
}

// Handle deleting an order
if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_orders = $conn->prepare("DELETE FROM ORDERS WHERE ID = ?");
   $delete_orders->execute([$delete_id]);
   header('location:admin_orders.php');
   exit();
}

// Handle downloading orders as CSV
if (isset($_POST['download_csv'])) {
    // Fetch all orders from the database
    $select_orders = $conn->prepare("SELECT * FROM ORDERS ORDER BY PLACED_ON DESC");
    $select_orders->execute();
    $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

    if ($orders && count($orders) > 0) {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=orders.csv');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Write the column headers
        fputcsv($output, ['Order ID', 'User ID', 'Name', 'Email', 'Phone Number', 'Address', 'Total Products', 'Total Price', 'Payment Method', 'Payment Status', 'Placed On']);

        // Write the data rows
        foreach ($orders as $order) {
            fputcsv($output, [
                $order['ID'],
                $order['USER_ID'],
                $order['NAME'],
                $order['EMAIL'],
                $order['PHONE_NUMBER'],
                $order['ADDRESS'],
                $order['TOTAL_PRODUCTS'],
                '$' . $order['TOTAL_PRICE'] . '/-',
                $order['METHOD'],
                $order['PAYMENT_STATUS'],
                $order['PLACED_ON']
            ]);
        }

        // Close the output stream
        fclose($output);
        exit();
    } else {
        echo 'No orders found to download.';
    }
}

// Handle downloading orders as XML
if (isset($_POST['download_xml'])) {
    // Fetch all orders from the database
    $select_orders = $conn->prepare("SELECT * FROM ORDERS ORDER BY PLACED_ON DESC");
    $select_orders->execute();
    $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

    if ($orders && count($orders) > 0) {
        // Set headers for XML download
        header('Content-Type: text/xml');
        header('Content-Disposition: attachment;filename=orders.xml');

        // Start XML document
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><orders></orders>');

        // Loop through each order and add to XML
        foreach ($orders as $order) {
            $order_node = $xml->addChild('order');
            $order_node->addChild('Order_ID', $order['ID']);
            $order_node->addChild('User_ID', $order['USER_ID']);
            $order_node->addChild('Name', $order['NAME']);
            $order_node->addChild('Email', $order['EMAIL']);
            $order_node->addChild('Phone_Number', $order['PHONE_NUMBER']);
            $order_node->addChild('Address', $order['ADDRESS']);
            $order_node->addChild('Total_Products', $order['TOTAL_PRODUCTS']);
            $order_node->addChild('Total_Price', '$' . $order['TOTAL_PRICE'] . '/-');
            $order_node->addChild('Payment_Method', $order['METHOD']);
            $order_node->addChild('Payment_Status', $order['PAYMENT_STATUS']);
            $order_node->addChild('Placed_On', $order['PLACED_ON']);
        }

        // Output the XML
        echo $xml->asXML();
        exit();
    } else {
        echo 'No orders found to download.';
    }
}

$total_pending = 0;
$total_completed = 0;

try {
    $stmt = $conn->prepare("BEGIN get_total_order_amounts(:pending, :completed); END;");
    $stmt->bindParam(':pending', $total_pending, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 32);
    $stmt->bindParam(':completed', $total_completed, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 32);
    $stmt->execute();
} catch (PDOException $e) {
    echo '<p class="empty">Error fetching totals: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/admin_style.css">
   <style>

      /* Card container for download buttons */
.download-card {
   background-color: #f9f9f9;
   border-radius: 8px;
   padding: 20px;
   margin-bottom: 30px;
   box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
   text-align: center;
}

.download-card h2 {
   font-size: 24px;
   margin-bottom: 15px;
   color: #333;
}

.download-btn-container {
   display: flex;
   justify-content: center;
   gap: 20px;
}

.download-btn {
   background-color:rgb(18, 31, 211);
   color: white;
   padding: 10px 20px;
   border: none;
   border-radius: 5px;
   cursor: pointer;
   font-size: 16px;
   transition: background-color 0.3s ease;
}

.download-btn:hover {
   background-color:rgb(3, 55, 151);
}

.box-container {
   display: grid;
   grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
   gap: 20px;
}

.totals-card {
   background: linear-gradient(135deg, #f0f8ff, #dbe9f4);
   border-radius: 12px;
   padding: 25px;
   margin-top: 40px;
   box-shadow: 0 8px 20px rgba(0,0,0,0.1);
   text-align: center;
}

.totals-card h2 {
   font-size: 28px;
   color: #333;
   margin-bottom: 20px;
}

.totals-info {
   display: flex;
   justify-content: center;
   gap: 30px;
   flex-wrap: wrap;
}

.totals-box {
   background: #fff;
   padding: 20px 30px;
   border-radius: 10px;
   box-shadow: 0 6px 12px rgba(0,0,0,0.08);
   width: 220px;
   transition: transform 0.3s;
}

.totals-box:hover {
   transform: translateY(-5px);
}

.totals-box h3 {
   font-size: 20px;
   margin-bottom: 10px;
   color: #555;
}

.totals-box p {
   font-size: 24px;
   color: #222;
   font-weight: bold;
}

.pending {
   border-top: 4px solid #f39c12;
}

.completed {
   border-top: 4px solid #27ae60;
}

      </style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="placed-orders">
   <h1 class="title">Placed Orders</h1>

   <!-- Download Card Section -->
   <div class="download-card">
      <h2>Download Orders</h2>
      <div class="download-btn-container">
         <form method="POST" style="display:inline;">
            <button type="submit" name="download_csv" class="download-btn">Download Orders (CSV)</button>
         </form>
         <form method="POST" style="display:inline;">
            <button type="submit" name="download_xml" class="download-btn">Download Orders (XML)</button>
         </form>
      </div>
   </div>

   <div class="box-container">
      <?php
      // Fetch all orders from the database
      $select_orders = $conn->prepare("SELECT * FROM ORDERS ORDER BY PLACED_ON DESC");
      $select_orders->execute();
      $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

      if ($orders && count($orders) > 0) {
         foreach ($orders as $order) {
      ?>
      <div class="box">
         <p> User ID: <span><?= htmlspecialchars($order['USER_ID']) ?></span> </p>
         <p> Placed on: <span><?= htmlspecialchars($order['PLACED_ON']) ?></span> </p>
         <p> Name: <span><?= htmlspecialchars($order['NAME']) ?></span> </p>
         <p> Email: <span><?= htmlspecialchars($order['EMAIL']) ?></span> </p>
         <p> Phone Number: <span><?= htmlspecialchars($order['PHONE_NUMBER']) ?></span> </p>
         <p> Address: <span><?= htmlspecialchars($order['ADDRESS']) ?></span> </p>
         <p> Total Products: <span><?= htmlspecialchars($order['TOTAL_PRODUCTS']) ?></span> </p>
         <p> Total Price: <span>$<?= htmlspecialchars($order['TOTAL_PRICE']) ?>/-</span> </p>
         <p> Payment Method: <span><?= htmlspecialchars($order['METHOD']) ?></span> </p>
         <form action="" method="POST">
            <input type="hidden" name="order_id" value="<?= $order['ID']; ?>">
            <select name="update_payment" class="drop-down" required>
               <option value="" disabled selected><?= htmlspecialchars($order['PAYMENT_STATUS']) ?></option>
               <option value="pending">Pending</option>
               <option value="completed">Completed</option>
            </select>
            <div class="flex-btn">
               <input type="submit" name="update_order" class="option-btn" value="Update">
               <a href="admin_orders.php?delete=<?= $order['ID']; ?>" class="delete-btn" onclick="return confirm('Delete this order?');">Delete</a>
            </div>
         </form>
      </div>
      <?php
         }
      } else {
         echo '<p class="empty">No orders placed yet!</p>';
      }
      ?>
   </div>
</section>

<div class="totals-card">
    <h2>Orders Summary</h2>
    <div class="totals-info">
        <div class="totals-box pending">
            <h3>Pending Orders</h3>
            <p>$<?= number_format($total_pending, 2) ?> /-</p>
        </div>
        <div class="totals-box completed">
            <h3>Completed Orders</h3>
            <p>$<?= number_format($total_completed, 2) ?> /-</p>
        </div>
    </div>
</div>


<script src="js/script.js"></script>

</body>
</html>
