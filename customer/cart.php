
<?php include 'header.php';
$cart = $_SESSION['cart'] ?? [];
if(isset($_POST['update'])){
  foreach($cart as $id=>$item){
    $new = max(1,(int)($_POST['qty'][$id] ?? $item['qty']));
    $_SESSION['cart'][$id]['qty'] = $new;
  }
  flash('Cart updated'); header('Location: cart.php'); exit;
}
if(isset($_GET['remove'])){ unset($_SESSION['cart'][$_GET['remove']]); flash('Item removed'); header('Location: cart.php'); exit; }
$total = 0; foreach($cart as $i) $total += $i['qty'] * $i['price'];
?>
<h3>Cart</h3>
<?php if(!$cart): ?><p>Your cart is empty.</p><?php else: ?>
<form method="post">
<table class="table align-middle">
  <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
  <tbody>
  <?php foreach($cart as $id=>$item): $sub=$item['qty']*$item['price']; ?>
    <tr>
      <td><?= htmlspecialchars($item['name']) ?></td>
      <td>Rs. <?= number_format($item['price'],2) ?></td>
      <td style="max-width:120px"><input type="number" name="qty[<?= $id ?>]" class="form-control" min="1" value="<?= $item['qty'] ?>"></td>
      <td>Rs. <?= number_format($sub,2) ?></td>
      <td><a class="btn btn-sm btn-outline-danger" href="?remove=<?= $id ?>"><i class="fa fa-times"></i></a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<div class="d-flex justify-content-between">
  <a class="btn btn-secondary" href="index.php">Continue Shopping</a>
  <div>
    <strong class="me-3">Total: Rs. <?= number_format($total,2) ?></strong>
    <button name="update" class="btn btn-warning">Update</button>
    <a class="btn btn-primary" href="checkout.php">Checkout</a>
  </div>
</div>
</form>
<?php endif; ?>
<?php include 'footer.php'; ?>
