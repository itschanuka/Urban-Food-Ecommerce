<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('location:login.php');
    exit();
}

// Add to wishlist
if (isset($_POST['add_to_wishlist'])) {
    $pid = $_POST['pid'];
    $p_name = $_POST['p_name'];
    $p_price = $_POST['p_price'];
    $p_image = $_POST['p_image'];

    $check_wishlist = $conn->prepare("SELECT * FROM WISHLIST WHERE NAME = :name AND USER_ID = :user_id");
    $check_wishlist->execute([
        ':name' => $p_name,
        ':user_id' => $user_id
    ]);

    $check_cart = $conn->prepare("SELECT * FROM CART WHERE NAME = :name AND USER_ID = :user_id");
    $check_cart->execute([
        ':name' => $p_name,
        ':user_id' => $user_id
    ]);

    if ($check_wishlist->fetch(PDO::FETCH_ASSOC)) {
        $message[] = 'Already added to wishlist!';
    } elseif ($check_cart->fetch(PDO::FETCH_ASSOC)) {
        $message[] = 'Already added to cart!';
    } else {
        $insert = $conn->prepare("INSERT INTO WISHLIST (USER_ID, PID, NAME, PRICE, IMAGE)
                                    VALUES (:user_id, :pid, :name, :price, :image)");
        $insert->execute([
            ':user_id' => $user_id,
            ':pid' => $pid,
            ':name' => $p_name,
            ':price' => $p_price,
            ':image' => $p_image
        ]);
        $message[] = 'Added to wishlist!';
    }
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $pid = $_POST['pid'];
    $p_name = $_POST['p_name'];
    $p_price = $_POST['p_price'];
    $p_image = $_POST['p_image'];
    $p_qty = $_POST['p_qty'];

    $check_cart = $conn->prepare("SELECT * FROM CART WHERE NAME = :name AND USER_ID = :user_id");
    $check_cart->execute([
        ':name' => $p_name,
        ':user_id' => $user_id
    ]);

    if ($check_cart->fetch(PDO::FETCH_ASSOC)) {
        $message[] = 'Already added to cart!';
    } else {
        $check_wishlist = $conn->prepare("SELECT * FROM WISHLIST WHERE NAME = :name AND USER_ID = :user_id");
        $check_wishlist->execute([
            ':name' => $p_name,
            ':user_id' => $user_id
        ]);

        if ($check_wishlist->fetch(PDO::FETCH_ASSOC)) {
            $delete = $conn->prepare("DELETE FROM WISHLIST WHERE NAME = :name AND USER_ID = :user_id");
            $delete->execute([
                ':name' => $p_name,
                ':user_id' => $user_id
            ]);
        }

        $insert_cart = $conn->prepare("INSERT INTO CART (USER_ID, PID, NAME, PRICE, QUANTITY, IMAGE)
                                        VALUES (:user_id, :pid, :name, :price, :quantity, :image)");
        $insert_cart->execute([
            ':user_id' => $user_id,
            ':pid' => $pid,
            ':name' => $p_name,
            ':price' => $p_price,
            ':quantity' => $p_qty,
            ':image' => $p_image
        ]);
        $message[] = 'Added to cart!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="search-form">
    <form action="" method="POST">
        <input type="text" class="box" name="search_box" placeholder="Search products...">
        <input type="submit" name="search_btn" value="Search" class="btn">
    </form>
</section>

<section class="products" style="padding-top: 0;">
    <div class="box-container">

    <?php
if (isset($_POST['search_btn'])) {
    $search_box = trim($_POST['search_box']);
    $search_words = explode(' ', $search_box);
    $conditions = [];
    $params = [];

    foreach ($search_words as $key => $word) {
        if (!empty(trim($word))) {
            $conditions[] = "LOWER(NAME) LIKE LOWER(:word{$key}) || '%'";
            $params[":word{$key}"] = trim($word);
        }
    }

    $sql = "SELECT * FROM PRODUCTS";
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" OR ", $conditions);
    }

    $select_products = $conn->prepare($sql);
    $select_products->execute($params);
    $products = $select_products->fetchAll(PDO::FETCH_ASSOC);

    if ($products && count($products) > 0) {
        foreach ($products as $row) {
?>

<form action="" class="box" method="POST">
    <div class="price">$<span><?= htmlspecialchars($row['PRICE']); ?></span>/-</div>
    <a href="view_page.php?pid=<?= $row['ID']; ?>" class="fas fa-eye"></a>
    <img src="uploaded_img/<?= htmlspecialchars($row['IMAGE']); ?>" alt="">
    <div class="name"><?= htmlspecialchars($row['NAME']); ?></div>
    <input type="hidden" name="pid" value="<?= $row['ID']; ?>">
    <input type="hidden" name="p_name" value="<?= htmlspecialchars($row['NAME']); ?>">
    <input type="hidden" name="p_price" value="<?= $row['PRICE']; ?>">
    <input type="hidden" name="p_image" value="<?= htmlspecialchars($row['IMAGE']); ?>">
    <input type="number" min="1" value="1" name="p_qty" class="qty">
    <input type="submit" value="Add to Wishlist" class="option-btn" name="add_to_wishlist">
    <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
</form>

<?php
        }
    } else {
        echo '<p class="empty">No products found!</p>';
    }
}
?>


    </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>