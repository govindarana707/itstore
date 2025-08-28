
<?php include 'header.php';
$slug = $_GET['slug'] ?? '';
$res = mysqli_query($conn,"SELECT * FROM products WHERE slug='".mysqli_real_escape_string($conn,$slug)."' LIMIT 1");
if(!$prod = mysqli_fetch_assoc($res)){ echo "<p>Product not found.</p>"; include 'footer.php'; exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $qty = max(1, (int)($_POST['qty'] ?? 1));
  $_SESSION['cart'][$prod['id']] = [
    'id'=>$prod['id'],'name'=>$prod['name'],'price'=>$prod['price'],
    'qty'=>($_SESSION['cart'][$prod['id']]['qty'] ?? 0) + $qty
  ];
  flash('Added to cart'); header('Location: cart.php'); exit;
}
?>
<div class="row">
  <div class="col-md-5"><img class="img-fluid rounded" src="<?= $prod['image'] ? '../assets/images/'.$prod['image'] : 'https://via.placeholder.com/600x400' ?>"></div>
  <div class="col-md-7">
    <h3><?= htmlspecialchars($prod['name']) ?></h3>
    <p class="text-muted">Rs. <?= number_format($prod['price'],2) ?></p>
    <p><?= nl2br(htmlspecialchars($prod['description'])) ?></p>
    <form method="post" class="d-flex gap-2">
      <input type="number" name="qty" class="form-control" value="1" min="1" style="max-width:120px">
      <button class="btn btn-primary"><i class="fa fa-cart-plus"></i> Add to Cart</button>
    </form>
  </div>
</div>
<?php include 'footer.php'; ?>
