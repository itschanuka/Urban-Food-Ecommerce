<?php
@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
    header('location:login.php');
    exit;
}

if(isset($_POST['add_to_wishlist'])){

    $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
    $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
    $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
    $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

    $check_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE name = :name AND user_id = :user_id");
    $check_wishlist->execute([':name' => $p_name, ':user_id' => $user_id]);

    $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = :name AND user_id = :user_id");
    $check_cart->execute([':name' => $p_name, ':user_id' => $user_id]);

    if($check_wishlist->rowCount() > 0){
        $message[] = 'already added to wishlist!';
    } elseif($check_cart->rowCount() > 0){
        $message[] = 'already added to cart!';
    } else {
        $insert_wishlist = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(:user_id, :pid, :name, :price, :image)");
        $insert_wishlist->execute([
            ':user_id' => $user_id,
            ':pid' => $pid,
            ':name' => $p_name,
            ':price' => $p_price,
            ':image' => $p_image
        ]);
        $message[] = 'added to wishlist!';
    }

}

if(isset($_POST['add_to_cart'])){

    $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
    $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
    $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
    $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
    $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

    $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = :name AND user_id = :user_id");
    $check_cart->execute([':name' => $p_name, ':user_id' => $user_id]);

    if($check_cart->rowCount() > 0){
        $message[] = 'already added to cart!';
    } else {
        $check_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE name = :name AND user_id = :user_id");
        $check_wishlist->execute([':name' => $p_name, ':user_id' => $user_id]);

        if($check_wishlist->rowCount() > 0){
            $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE name = :name AND user_id = :user_id");
            $delete_wishlist->execute([':name' => $p_name, ':user_id' => $user_id]);
        }

        $insert_cart = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(:user_id, :pid, :name, :price, :quantity, :image)");
        $insert_cart->execute([
            ':user_id' => $user_id,
            ':pid' => $pid,
            ':name' => $p_name,
            ':price' => $p_price,
            ':quantity' => $p_qty,
            ':image' => $p_image
        ]);
        $message[] = 'added to cart!';
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>home page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/home.css">

</head>
<body>

<?php include 'header.php'; ?>

<section class="hero-banner">
    <div class="hero-content">
        <h1>
            🍽️ Hello, <span class="site-name">Food Mart</span> Family, <?= $_SESSION['user_name'] ?? 'Guest' ?>! 👋
        </h1>

        <p class="tagline">
            🥗 Fresh from the farm, crafted for your table. 🌾🍇<br>
            Explore organic veggies 🥕, artisanal breads 🍞, and premium meats 🥩 — all in one place! 🌟
        </p>

        <div class="features">
            <div class="feature-box">
                <div class="icon">🍞</div>
                <h3>Freshly Baked Delights</h3>
                <p>Warm pastries and rustic breads, baked daily to perfection.</p>
            </div>
            <div class="feature-box">
                <div class="icon">🥩</div>
                <h3>Top-Tier Meats</h3>
                <p>Handpicked, locally sourced, and crafted for the ultimate flavor.</p>
            </div>
            <div class="feature-box">
                <div class="icon">🥑</div>
                <h3>Organic Veggies</h3>
                <p>Harvested with love — vibrant, nutrient-rich, and farm-fresh.</p>
            </div>
        </div>

        <a href="shop.php" class="btn">
            🛒 Shop the Freshness Now!
        </a>

        <p class="reminder">
            🚚 Fast Delivery | 🌱 100% Organic | 🏆 Taste the Quality You Deserve!
        </p>
    </div>
</section>



<section class="stats-bar">
    <div class="stat"><strong>10K+</strong> Products Sold</div>
    <div class="stat"><strong>8K+</strong> Happy Customers</div>
    <div class="stat"><strong>100+</strong> Cities Served</div>
</section>


<section class="home-category">
    <h1 class="title">shop by category</h1>
    <div class="box-container">
        <div class="box">
            <img src="images/cat-1.png" alt="">
            <h3>fruits</h3>
            <p>Discover a variety of fresh fruits sourced directly from local farms to your doorstep.</p>
            <a href="category.php?category=fruits" class="btn">fruits</a>
        </div>
        <div class="box">
            <img src="images/cat-2.png" alt="">
            <h3>meat</h3>
            <p>High-quality meats carefully selected for freshness and taste, perfect for every meal.</p>
            <a href="category.php?category=meat" class="btn">meat</a>
        </div>
        <div class="box">
            <img src="images/cat-3.png" alt="">
            <h3>vegetables</h3>
            <p>Choose from a wide range of organic and garden-fresh vegetables for your healthy lifestyle.</p>
            <a href="category.php?category=vegetables" class="btn">vegetables</a>
        </div>
        <div class="box">
            <img src="images/cat-4.png" alt="">
            <h3>fish</h3>
            <p>Enjoy the finest selection of fresh fish caught and delivered with care for quality.</p>
            <a href="category.php?category=fish" class="btn">fish</a>
        </div>
    </div>
</section>


<section class="products">
    <h1 class="title">latest products</h1>
    <div class="box-container">
        <?php
            $select_products = $conn->prepare("SELECT * FROM PRODUCTS");
            $select_products->execute();

            while($row = $select_products->fetch(PDO::FETCH_ASSOC)){
        ?>
        <form action="" class="box" method="POST">
            <div class="price">$<span><?= htmlspecialchars($row['PRICE']) ?></span>/-</div>
            <a href="view_page.php?pid=<?= htmlspecialchars($row['ID']) ?>" class="fas fa-eye"></a>
            <img src="uploaded_img/<?= htmlspecialchars($row['IMAGE']) ?>" alt="">
            <div class="name"><?= htmlspecialchars($row['NAME']) ?></div>
            <input type="hidden" name="pid" value="<?= $row['ID'] ?>">
            <input type="hidden" name="p_name" value="<?= htmlspecialchars($row['NAME']) ?>">
            <input type="hidden" name="p_price" value="<?= $row['PRICE'] ?>">
            <input type="hidden" name="p_image" value="<?= htmlspecialchars($row['IMAGE']) ?>">
            <input type="number" min="1" value="1" name="p_qty" class="qty">
            <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
            <input type="submit" value="add to cart" class="btn" name="add_to_cart">
        </form>
        <?php
            }
            if ($select_products->rowCount() == 0) {
                echo '<p class="empty"></p>';
            }
        ?>
    </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>

