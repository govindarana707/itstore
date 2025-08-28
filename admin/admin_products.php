<?php include 'header_admin.php'; ?>
<h2>Products</h2>
<a href="product_add.php" class="btn btn-success mb-3">Add New Product</a>

<div class="table-responsive">
<table class="table table-striped">
  <thead>
    <tr><th>ID</th><th>Title</th><th>Category</th><th>Price</th><th>Image</th><th>Actions</th></tr>
  </thead>
  <tbody>
  <?php
  $res = mysqli_query($conn,"SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC");
  while($row = mysqli_fetch_assoc($res)){
      echo '<tr>
        <td>'.$row['id'].'</td>
        <td>'.htmlspecialchars($row['title']).'</td>
        <td>'.htmlspecialchars($row['category_name']).'</td>
        <td>Rs. '.number_format($row['price'],2).'</td>
        <td><img src="'.($row['image'] ? $row['image'] : '../assets/img/default.svg').'" width="50"></td>
        <td>
          <a class="btn btn-sm btn-primary" href="product_edit.php?id='.$row['id'].'">Edit</a>
          <a class="btn btn-sm btn-danger" href="product_delete.php?id='.$row['id'].'" onclick="return confirm(\'Delete this product?\')">Delete</a>
        </td>
      </tr>';
  }
  ?>
  </tbody>
</table>
</div>
<?php include 'footer_admin.php'; ?>
