<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit;
}

// DELETE SINGLE ITEM
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_cart_item = $conn->prepare("DELETE FROM cart WHERE id = :DELETE_ID");
    $delete_cart_item->bindParam(':DELETE_ID', $delete_id);
    $delete_cart_item->execute();
    header('location:cart.php');
    exit;
}

// DELETE ALL ITEMS
if (isset($_GET['delete_all'])) {
    $delete_all_items = $conn->prepare("DELETE FROM cart WHERE user_id = :USER_ID");
    $delete_all_items->bindParam(':USER_ID', $user_id);
    $delete_all_items->execute();
    header('location:cart.php');
    exit;
}

// UPDATE QUANTITY
if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $p_qty = $_POST['p_qty'];
    $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);
    $update_qty = $conn->prepare("UPDATE cart SET quantity = :QUANTITY WHERE id = :CART_ID");
    $update_qty->bindParam(':QUANTITY', $p_qty);
    $update_qty->bindParam(':CART_ID', $cart_id);
    $update_qty->execute();
    $message[] = 'Cart quantity updated';
}

// CALL PL/SQL PROCEDURE TO CALCULATE TOTAL
$total = 0;
$stmt = $conn->prepare("BEGIN calculate_cart_total(:USER_ID, :TOTAL); END;");
$stmt->bindParam(':USER_ID', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':TOTAL', $total, PDO::PARAM_INT, 10);
$stmt->execute();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Isolated CSS for the calculated total box */
        .calculated-total {
            background: #f9fafb; /* Soft light grey background */
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            transition: all 0.3s ease;
        }

        .calculated-total:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .total-info {
            display: flex;
            align-items: center;
        }

        .emoji {
            font-size: 35px;
            margin-right: 15px;
            color: #28a745; /* Green color for the emoji */
        }

        .total-text .title {
            font-size: 18px;
            color: #333;
            font-weight: bold;
        }

        .total-text .amount {
            font-size: 24px;
            color: #ff5733; /* Contrasting red-orange color */
            font-weight: 600;
        }

        .total-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 15px;
        }

        .total-actions i {
            font-size: 28px;
            color: #007bff; /* Blue for calculator icon */
            cursor: pointer;
        }

        .refresh-btn {
            padding: 8px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .refresh-btn:hover {
            background-color: #218838; /* Darker green on hover */
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="shopping-cart">

    <h1 class="title">Products Added</h1>

    <div class="box-container">
        <?php
        $grand_total = 0;
        $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = :USER_ID");
        $select_cart->bindParam(':USER_ID', $user_id);
        $select_cart->execute();

        // Fetching all cart items
        $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC); 

        if ($cart_items) {
            foreach ($cart_items as $fetch_cart) {
                ?>
                <form action="" method="POST" class="box">
                    <a href="cart.php?delete=<?= $fetch_cart['ID']; ?>" class="fas fa-times"
                       onclick="return confirm('Delete this from cart?');"></a>
                    <a href="view_page.php?pid=<?= $fetch_cart['PID']; ?>" class="fas fa-eye"></a>
                    <img src="uploaded_img/<?= $fetch_cart['IMAGE']; ?>" alt="Product Image">
                    <div class="name"><?= $fetch_cart['NAME']; ?></div>
                    <div class="price">$<?= $fetch_cart['PRICE']; ?>/-</div>
                    <input type="hidden" name="cart_id" value="<?= $fetch_cart['ID']; ?>">
                    <div class="flex-btn">
                        <input type="number" min="1" value="<?= $fetch_cart['QUANTITY']; ?>" class="qty" name="p_qty">
                        <input type="submit" value="Update" name="update_qty" class="option-btn">
                    </div>
                    <div class="sub-total">
                        Sub total : <span>$<?= $sub_total = ($fetch_cart['PRICE'] * $fetch_cart['QUANTITY']); ?>/-</span>
                    </div>
                </form>
                <?php
                $grand_total += $sub_total;
            }
        } else {
            echo '<p class="empty">Your cart is empty</p>';
        }
        ?>
    </div>

    <div class="cart-total">
        <p>Grand total : <span>$<?= $grand_total; ?>/-</span></p>
        <a href="shop.php" class="option-btn">Continue Shopping</a>
        <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Delete All</a>
        <a href="checkout.php" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
    </div>

    <!-- Calculated Total Section -->
    <div class="calculated-total">
        <div class="total-info">
            <i class="emoji">🛒</i>
            <div class="total-text">
                <p class="title">Calculated Total (using procedure)</p>
                <p class="amount"><span>$<?= $total; ?>/-</span></p>
            </div>
        </div>
        <div class="total-actions">
            <i class="fas fa-calculator"></i>
            <button class="refresh-btn" onclick="window.location.reload();">Refresh</button>
        </div>
    </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
