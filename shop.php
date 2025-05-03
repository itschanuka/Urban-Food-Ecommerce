<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit();
}

// ADD TO WISHLIST
if(isset($_POST['add_to_wishlist'])){
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

   if($check_wishlist->fetch(PDO::FETCH_ASSOC)){
      $message[] = 'Already added to wishlist!';
   } elseif($check_cart->fetch(PDO::FETCH_ASSOC)){
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

// ADD TO CART
if(isset($_POST['add_to_cart'])){
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

   if($check_cart->fetch(PDO::FETCH_ASSOC)){
      $message[] = 'Already added to cart!';
   } else {
      $check_wishlist = $conn->prepare("SELECT * FROM WISHLIST WHERE NAME = :name AND USER_ID = :user_id");
      $check_wishlist->execute([
         ':name' => $p_name,
         ':user_id' => $user_id
      ]);

      if($check_wishlist->fetch(PDO::FETCH_ASSOC)){
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
   <title>Shop</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/shop.css">
   <link rel="stylesheet" href="css/header.css">

   <style>
      .filter-card {
         width: 200px;
         padding: 20px;
         background: #f9f9f9;
         border: 1px solid #ccc;
         border-radius: 8px;
         margin: 20px;
         position: sticky;
         top: 100px;
         height: fit-content;
      }
      .filter-card h3 {
         margin-bottom: 10px;
         font-size: 18px;
      }
      .filter-btn {
         background-color: #28a745;
         color: white;
         padding: 10px 15px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         margin-top: 10px;
      }
      .filter-btn:hover {
         background-color: #218838;
      }
      .reset-btn {
         background-color: #dc3545;
         color: white;
         padding: 10px 15px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         margin-top: 10px;
      }
      .reset-btn:hover {
         background-color: #c82333;
      }
      .view-option {
         display: flex;
         justify-content: space-between;
         margin-bottom: 20px;
      }
      .view-option button {
         padding: 8px 16px;
         border: none;
         cursor: pointer;
      }
      .view-option button:hover {
         background-color: #28a745;
         color: white;
      }
      .products-container {
         display: flex;
         gap: 20px;
      }
      .products .box-container {
         display: flex;
         flex-wrap: wrap;
         gap: 20px;
         flex: 1;
      }
      .box {
         width: 250px;
         background: #fff;
         border: 1px solid #ccc;
         border-radius: 8px;
         padding: 15px;
         text-align: center;
      }
      .box img {
         width: 100%;
         height: 200px;
         object-fit: cover;
      }
      .list-view .box {
         width: 100%;
         display: flex;
         align-items: center;
         padding: 15px;
         gap: 20px;
      }
      .list-view .box img {
         width: 100px;
         height: 100px;
         object-fit: cover;
      }
   </style>

</head>
<body>

<?php include 'header.php'; ?>

<section class="p-category">
   <a href="category.php?category=fruits">Fruits</a>
   <a href="category.php?category=vegitables">Vegetables</a>
   <a href="category.php?category=fish">Fish</a>
   <a href="category.php?category=meat">Meat</a>
</section>

<section class="announcement-bar">
   <p>🚨 Special Deal: Spend over $50 and get <strong>Free Delivery + a $5 Coupon</strong> on your next order! 🎁</p>
   <small>Automatically applied at checkout. Valid this week only!</small>
</section>

<section class="products">
   <h1 class="title">Products</h1>

   <div class="products-container">
      <div class="filter-card">
         <h3>View</h3>
         <div class="view-option">
            <button onclick="toggleView('grid')">Grid View</button>
            <button onclick="toggleView('list')">List View</button>
         </div>

         <h3>Sort By</h3>
         <select id="sort-products" onchange="sortProducts()">
            <option value="default">Select Sort</option>
            <option value="price-asc">Price: Low to High</option>
            <option value="price-desc">Price: High to Low</option>
            <option value="name-asc">Name: A-Z</option>
            <option value="name-desc">Name: Z-A</option>
         </select>

         <h3>Filter By Price</h3>
         <input type="number" id="min-price" placeholder="Min ($1)" value="1" min="1" max="150" style="width:100%;padding:8px;margin-bottom:10px;">
         <input type="number" id="max-price" placeholder="Max ($150)" value="150" min="1" max="150" style="width:100%;padding:8px;margin-bottom:10px;">
         <button class="filter-btn" onclick="filterProducts()">Apply Filter</button>

         <button class="reset-btn" onclick="resetFilters()">Reset</button>
      </div>

      <div class="box-container" id="products-list">

      <?php
         $select_products = $conn->prepare("SELECT * FROM PRODUCTS");
         $select_products->execute();

         while($row = $select_products->fetch(PDO::FETCH_ASSOC)){
      ?>
      <form action="" class="box" method="POST" data-price="<?= $row['PRICE']; ?>" data-name="<?= htmlspecialchars($row['NAME']); ?>">
         <div class="price">$<span><?= htmlspecialchars($row['PRICE']); ?></span>/-</div>
         <a href="view_page.php?pid=<?= $row['ID']; ?>" class="fas fa-eye"></a>
         <img src="uploaded_img/<?= htmlspecialchars($row['IMAGE']); ?>" alt="Product Image">
         <div class="name"><?= htmlspecialchars($row['NAME']); ?></div>
         <input type="hidden" name="pid" value="<?= $row['ID']; ?>">
         <input type="hidden" name="p_name" value="<?= htmlspecialchars($row['NAME']); ?>">
         <input type="hidden" name="p_price" value="<?= $row['PRICE']; ?>">
         <input type="hidden" name="p_image" value="<?= htmlspecialchars($row['IMAGE']); ?>">
         <input type="number" min="1" value="1" name="p_qty" class="qty">
         <input type="submit" value="Add to Wishlist" class="option-btn" name="add_to_wishlist">
         <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
      </form>
      <?php } ?>

      </div>
   </div>
</section>

<?php include 'footer.php'; ?>

<script>
function filterProducts() {
   const minPrice = parseFloat(document.getElementById('min-price').value) || 1;
   const maxPrice = parseFloat(document.getElementById('max-price').value) || 150;
   const products = document.querySelectorAll('#products-list .box');

   products.forEach(product => {
      const price = parseFloat(product.getAttribute('data-price'));
      if (price >= minPrice && price <= maxPrice) {
         product.style.display = 'block';
      } else {
         product.style.display = 'none';
      }
   });
}

function sortProducts() {
   const sortValue = document.getElementById('sort-products').value;
   const products = Array.from(document.querySelectorAll('#products-list .box'));
   
   if (sortValue === 'price-asc') {
      products.sort((a, b) => parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price')));
   } else if (sortValue === 'price-desc') {
      products.sort((a, b) => parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price')));
   } else if (sortValue === 'name-asc') {
      products.sort((a, b) => a.getAttribute('data-name').localeCompare(b.getAttribute('data-name')));
   } else if (sortValue === 'name-desc') {
      products.sort((a, b) => b.getAttribute('data-name').localeCompare(a.getAttribute('data-name')));
   }

   const productsList = document.getElementById('products-list');
   productsList.innerHTML = '';
   products.forEach(product => productsList.appendChild(product));
}

function toggleView(view) {
   const container = document.querySelector('.box-container');
   if (view === 'grid') {
      container.classList.remove('list-view');
   } else {
      container.classList.add('list-view');
   }
}

function resetFilters() {
   document.getElementById('min-price').value = 1;
   document.getElementById('max-price').value = 150;
   document.getElementById('sort-products').value = 'default';
   filterProducts();
}
</script>

</body>
</html>

