<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('location:login.php');
    exit();
}

$cart_items = [];
$cart_total = 0;
$cart_products = [];
$message = [];

try {
    $select_cart = $conn->prepare("SELECT * FROM CART WHERE USER_ID = :user_id");
    $select_cart->execute([':user_id' => $user_id]);
    while ($row = $select_cart->fetch(PDO::FETCH_ASSOC)) {
        $cart_items[] = $row;
        if (isset($row['PRICE'], $row['QUANTITY'], $row['NAME'])) {
            $sub_total = $row['PRICE'] * $row['QUANTITY'];
            $cart_total += $sub_total;
            $cart_products[] = $row['NAME'] . ' (' . $row['QUANTITY'] . ')';
        }
    }
} catch (PDOException $e) {
    echo "Error fetching cart data: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {

    // Sanitize user input
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $phone_number = filter_var($_POST['number'], FILTER_SANITIZE_STRING); // fixed variable name
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
    $address = 'flat no. ' . $_POST['flat'] . ' ' . $_POST['street'] . ' ' . $_POST['city'] . ' ' . $_POST['state'] . ' ' . $_POST['country'] . ' - ' . $_POST['pin_code'];
    $address = filter_var($address, FILTER_SANITIZE_STRING);
    $placed_on = date('d-M-Y');
    $total_products = implode(', ', $cart_products);

    if ($cart_total == 0) {
        $message[] = 'Your cart is empty!';
    } else {
        try {
            // Check for duplicate order
            $check_order = $conn->prepare("SELECT * FROM ORDERS WHERE NAME = :name AND PHONE_NUMBER = :phone_number AND EMAIL = :email AND METHOD = :method AND ADDRESS = :address AND TOTAL_PRODUCTS = :products AND TOTAL_PRICE = :price");
            $check_order->execute([
                ':name' => $name,
                ':phone_number' => $phone_number,
                ':email' => $email,
                ':method' => $method,
                ':address' => $address,
                ':products' => $total_products,
                ':price' => $cart_total
            ]);

            if ($check_order->rowCount() > 0) {
                $message[] = 'Order already placed!';
            } else {
                // Insert new order
                $insert_order = $conn->prepare("INSERT INTO ORDERS (USER_ID, NAME, PHONE_NUMBER, EMAIL, METHOD, ADDRESS, TOTAL_PRODUCTS, TOTAL_PRICE, PLACED_ON, PAYMENT_STATUS) VALUES (:user_id, :name, :phone_number, :email, :method, :address, :products, :price, :placed_on, :payment_status)");
                $insert_order->execute([
                    ':user_id' => $user_id,
                    ':name' => $name,
                    ':phone_number' => $phone_number,
                    ':email' => $email,
                    ':method' => $method,
                    ':address' => $address,
                    ':products' => $total_products,
                    ':price' => $cart_total,
                    ':placed_on' => $placed_on,
                    ':payment_status' => 'pending'
                ]);

                // Clear the user's cart
                $delete_cart = $conn->prepare("DELETE FROM CART WHERE USER_ID = :user_id");
                $delete_cart->execute([':user_id' => $user_id]);

                $message[] = 'Order placed successfully!';
            }
        } catch (PDOException $e) {
            $message[] = 'Order failed: ' . $e->getMessage();
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
   <title>checkout</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="display-orders">
<?php if (empty($cart_items)): ?>
   <p class="empty">your cart is empty!</p>
<?php else: ?>

   <div class="grand-total">grand total : <span>$<?= number_format($cart_total, 2) ?>/-</span></div>
<?php endif; ?>
</section>

<section class="checkout-orders">

   <form action="" method="POST">

      <h3>place your order</h3>

      <div class="flex">
         <div class="inputBox">
            <span>your name :</span>
            <input type="text" name="name" placeholder="enter your name" class="box" required>
         </div>
         <div class="inputBox">
            <span>your number :</span>
            <input type="number" name="number" placeholder="enter your number" class="box" required>
         </div>
         <div class="inputBox">
            <span>your email :</span>
            <input type="email" name="email" placeholder="enter your email" class="box" required>
         </div>
         <div class="inputBox">
            <span>payment method :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">cash on delivery</option>
               <option value="credit card">credit card</option>
               <option value="paytm">paytm</option>
               <option value="paypal">paypal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>address line 01 :</span>
            <input type="text" name="flat" placeholder="e.g. flat number" class="box" required>
         </div>
         <div class="inputBox">
            <span>address line 02 :</span>
            <input type="text" name="street" placeholder="e.g. street name" class="box" required>
         </div>
         <div class="inputBox">
            <span>city :</span>
            <input type="text" name="city" placeholder="e.g. mumbai" class="box" required>
         </div>
         <div class="inputBox">
            <span>state :</span>
            <input type="text" name="state" placeholder="e.g. maharashtra" class="box" required>
         </div>
         <div class="inputBox">
            <span>country :</span>
            <input type="text" name="country" placeholder="e.g. India" class="box" required>
         </div>
         <div class="inputBox">
            <span>pin code :</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($cart_total > 0)?'':'disabled'; ?>" value="place order">

   </form>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
