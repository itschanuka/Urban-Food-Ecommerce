<?php

@include 'config.php'; // Including configuration file for database connection

session_start(); // Starting session to maintain user state

$user_id = $_SESSION['user_id']; // Fetching the user ID from the session

// Redirect to login page if the user is not logged in
if (!isset($user_id)) {
   header('location:login.php');
   exit;
}

// Add to Wishlist
if (isset($_POST['add_to_wishlist'])) {
   // Sanitizing inputs
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

   // Check if the product is already in the wishlist
   $check_wishlist_query = $conn->prepare("SELECT * FROM wishlist WHERE name = :p_name AND user_id = :user_id");
   $check_wishlist_query->bindParam(':p_name', $p_name);
   $check_wishlist_query->bindParam(':user_id', $user_id);
   $check_wishlist_query->execute();
   $in_wishlist = $check_wishlist_query->fetch(PDO::FETCH_ASSOC);

   // Check if the product is in the cart
   $check_cart_query = $conn->prepare("SELECT * FROM cart WHERE name = :p_name AND user_id = :user_id");
   $check_cart_query->bindParam(':p_name', $p_name);
   $check_cart_query->bindParam(':user_id', $user_id);
   $check_cart_query->execute();
   $in_cart = $check_cart_query->fetch(PDO::FETCH_ASSOC);

   if ($in_wishlist) {
      $message[] = 'Already added to wishlist!';
   } elseif ($in_cart) {
      $message[] = 'Already added to cart!';
   } else {
      // Add product to wishlist
      $insert_wishlist = $conn->prepare("INSERT INTO wishlist (user_id, pid, name, price, image) VALUES (:user_id, :pid, :name, :price, :image)");
      $insert_wishlist->bindParam(':user_id', $user_id);
      $insert_wishlist->bindParam(':pid', $pid);
      $insert_wishlist->bindParam(':name', $p_name);
      $insert_wishlist->bindParam(':price', $p_price);
      $insert_wishlist->bindParam(':image', $p_image);
      $insert_wishlist->execute();
      $message[] = 'Added to wishlist!';
   }
}

// Add to Cart
if (isset($_POST['add_to_cart'])) {
   // Sanitizing inputs
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
   $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

   // Check if the product is already in the cart
   $check_cart_query = $conn->prepare("SELECT * FROM cart WHERE name = :p_name AND user_id = :user_id");
   $check_cart_query->bindParam(':p_name', $p_name);
   $check_cart_query->bindParam(':user_id', $user_id);
   $check_cart_query->execute();
   $in_cart = $check_cart_query->fetch(PDO::FETCH_ASSOC);

   if ($in_cart) {
      $message[] = 'Already added to cart!';
   } else {
      // Check if product is in the wishlist and remove it
      $check_wishlist_query = $conn->prepare("SELECT * FROM wishlist WHERE name = :p_name AND user_id = :user_id");
      $check_wishlist_query->bindParam(':p_name', $p_name);
      $check_wishlist_query->bindParam(':user_id', $user_id);
      $check_wishlist_query->execute();
      $in_wishlist = $check_wishlist_query->fetch(PDO::FETCH_ASSOC);

      if ($in_wishlist) {
         $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE name = :p_name AND user_id = :user_id");
         $delete_wishlist->bindParam(':p_name', $p_name);
         $delete_wishlist->bindParam(':user_id', $user_id);
         $delete_wishlist->execute();
      }

      // Add product to cart
      $insert_cart = $conn->prepare("INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (:user_id, :pid, :name, :price, :qty, :image)");
      $insert_cart->bindParam(':user_id', $user_id);
      $insert_cart->bindParam(':pid', $pid);
      $insert_cart->bindParam(':name', $p_name);
      $insert_cart->bindParam(':price', $p_price);
      $insert_cart->bindParam(':qty', $p_qty);
      $insert_cart->bindParam(':image', $p_image);
      $insert_cart->execute();
      $message[] = 'Added to cart!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Category</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/header.css">
   <link rel="stylesheet" href="css/category.css">

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

<section class="products">
   <h1 class="title">Product Categories</h1>
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

   <div class="box-container">
   <?php
      $category_name = $_GET['category']; // Get category from the URL
      // Fetch products based on category
      $select_products = $conn->prepare("SELECT * FROM products WHERE category = :category");
      $select_products->bindParam(':category', $category_name);
      $select_products->execute();

      // Check if products are available
      $has_results = false;
      while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
         $has_results = true;
   ?>
   <form action="" class="box" method="POST">
      <div class="price">$<span><?= htmlspecialchars($fetch_products['PRICE']); ?></span>/-</div>
      <a href="view_page.php?pid=<?= htmlspecialchars($fetch_products['ID']); ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_products['IMAGE']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_products['NAME']); ?></div>
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_products['ID']); ?>">
      <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['NAME']); ?>">
      <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['PRICE']); ?>">
      <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['IMAGE']); ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="Add to Wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
   </form>
   <?php } if (!$has_results) {
         echo '<p class="empty">No products available!</p>';
      }
   ?>
   </div>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
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
