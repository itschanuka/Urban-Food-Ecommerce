<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit;
}

if(isset($_POST['add_product'])){
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
   $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
   $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);

   $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select_products = $conn->prepare("SELECT * FROM products WHERE name = :name");
   $select_products->execute([':name' => $name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exists!';
   }else{
      $insert_products = $conn->prepare("INSERT INTO products(name, category, details, price, image) VALUES(:name, :category, :details, :price, :image)");
      $insert_products->execute([ ':name' => $name, ':category' => $category, ':details' => $details, ':price' => $price, ':image' => $image ]);
      
      if($insert_products){
         if($image_size > 2000000){
            $message[] = 'image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'new product added!';
         }
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $select_delete_image = $conn->prepare("SELECT image FROM products WHERE id = :id");
   $select_delete_image->execute([':id' => $delete_id]);
   $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
   if ($fetch_delete_image && !empty($fetch_delete_image['IMAGE'])) {
      unlink('uploaded_img/'.$fetch_delete_image['IMAGE']);
   }

   $delete_products = $conn->prepare("DELETE FROM products WHERE id = :id");
   $delete_products->execute([':id' => $delete_id]);

   $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE pid = :pid");
   $delete_wishlist->execute([':pid' => $delete_id]);

   $delete_cart = $conn->prepare("DELETE FROM cart WHERE pid = :pid");
   $delete_cart->execute([':pid' => $delete_id]);

   header('location:admin_products.php');
   exit;
}

if (isset($_POST['download_xml'])) {
   // Fetch product data from the database
   $products = $conn->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);

   // Create a new SimpleXMLElement object
   $xml = new SimpleXMLElement('<products/>');

   // Add product data to XML
   foreach ($products as $product) {
       $product_elem = $xml->addChild('product');
       $product_elem->addChild('id', $product['ID']);
       $product_elem->addChild('name', $product['NAME']);
       $product_elem->addChild('price', $product['PRICE']);
       $product_elem->addChild('category', $product['CATEGORY']);
   }

   // Output XML as a downloadable file
   Header('Content-type: text/xml');
   Header('Content-Disposition: attachment; filename="products_list.xml"');
   echo $xml->asXML();
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Products</title>

   <!-- Font Awesome CDN Link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS File Link -->
   <link rel="stylesheet" href="css/admin_style.css">

   <style>

      /* Table container */
.table-container {
   max-width: 100%;
   overflow-x: auto;
   margin-top: 20px;
}

/* Table Styling */
table {
   width: 100%;
   border-collapse: collapse;
   margin: 10px 0;
   font-family: 'Arial', sans-serif;
   background-color: #f9f9f9;
   box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Table Header */
th {
   background-color: #4CAF50;
   color: white;
   padding: 12px 15px;
   text-align: left;
   font-size: 16px;
   text-transform: uppercase;
   letter-spacing: 1px;
}

/* Table Body */
td {
   padding: 12px 15px;
   text-align: left;
   font-size: 14px;
   border-bottom: 1px solid #ddd;
}

/* Hover effect on table rows */
tr:hover {
   background-color: #f1f1f1;
}

/* Style for empty state */
.empty {
   text-align: center;
   font-size: 18px;
   color: #999;
}

/* Table Row Highlight (for better readability) */
tbody tr:nth-child(odd) {
   background-color: #f7f7f7;
}

tbody tr:nth-child(even) {
   background-color: #ffffff;
}

/* Responsive Design: Ensure table is scrollable on smaller screens */
@media (max-width: 768px) {
   table {
      width: 100%;
      font-size: 12px;
   }

   th, td {
      padding: 10px;
   }
}

      </style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<!-- Add a button to download the database table as XML -->
<section class="download-section">
   <h1 class="title">Download Products</h1>
   <form action="" method="POST">
      <input type="submit" name="download_xml" class="btn" value="Download XML">
   </form>
</section>

<section class="add-products">

   <h1 class="title">Add New Product</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
         <input type="text" name="name" class="box" required placeholder="Enter Product Name">
         <select name="category" class="box" required>
            <option value="" selected disabled>Select Category</option>
            <option value="vegetables">Vegetables</option>
            <option value="fruits">Fruits</option>
            <option value="meat">Meat</option>
            <option value="fish">Fish</option>
         </select>
         </div>
         <div class="inputBox">
         <input type="number" min="0" name="price" class="box" required placeholder="Enter Product Price">
         <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png">
         </div>
      </div>
      <textarea name="details" class="box" required placeholder="Enter Product Details" cols="30" rows="10"></textarea>
      <input type="submit" class="btn" value="Add Product" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="title">Products Added</h1>

   <div class="box-container">

   <?php
      $show_products = $conn->prepare("SELECT * FROM products");
      $show_products->execute();
      $results = $show_products->fetchAll(PDO::FETCH_ASSOC);
      if(count($results) > 0){
         foreach($results as $fetch_products){  
   ?>
   <div class="box">
      <div class="price">$<?= $fetch_products['PRICE']; ?>/-</div>
      <img src="uploaded_img/<?= $fetch_products['IMAGE']; ?>" alt="">
      <div class="name"><?= $fetch_products['NAME']; ?></div>
      <div class="cat"><?= $fetch_products['CATEGORY']; ?></div>
      <div class="details"><?= $fetch_products['DETAILS']; ?></div>
      <div class="flex-btn">
         <a href="admin_products.php?delete=<?= $fetch_products['ID']; ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
      </div>
   </div>
   <?php
      }
   }else{
      echo '<p class="empty">No products added yet!</p>';
   }
   ?>

   </div>

</section>

<!-- Show additional product details from the PL/SQL procedure -->
<section class="show-product-details">
   <h1 class="title">Product Details from Procedure</h1>

   <div class="table-container">
      <table>
         <thead>
            <tr>
               <th>Product Name</th>
               <th>Price</th>
               <th>Category</th>
            </tr>
         </thead>
         <tbody>
            <?php
               // Execute the procedure to populate product details
               $stmt = $conn->prepare("BEGIN get_product_details; END;");
               $stmt->execute();

               // Now fetch product details from the products table
               $select_details = $conn->prepare("SELECT name, price, category FROM products");
               $select_details->execute();
               $products = $select_details->fetchAll(PDO::FETCH_ASSOC);

               // Check if products exist and display them
               if (!empty($products)) {
                   foreach ($products as $product) {
            ?>
            <tr>
               <td><?= htmlspecialchars($product['NAME']); ?></td>
               <td><?= htmlspecialchars($product['PRICE']); ?></td>
               <td><?= htmlspecialchars($product['CATEGORY']); ?></td>
            </tr>
            <?php
                   }
               } else {
                   echo '<tr><td colspan="3" class="empty">No products found.</td></tr>';
               }
            ?>
         </tbody>
      </table>
   </div>
</section>



<script src="js/script.js"></script>

</body>
</html>
