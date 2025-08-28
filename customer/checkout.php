
<?php include 'header.php';
if(!is_logged_in()){ flash('Login to checkout','danger'); header('Location: ../auth/login.php'); exit; }
$cart = $_SESSION['cart'] ?? []; if(!$cart){ flash('Your cart is empty','danger'); header('Location: cart.php'); exit; }
$total = 0; foreach($cart as $i) $total += $i['qty']*$i['price'];

if($_SERVER['REQUEST_METHOD']==='POST'){
  $method = $_POST['payment_method'] ?? 'cod';
  mysqli_begin_transaction($conn);
  try{
    mysqli_query($conn,"INSERT INTO orders (user_id,total,payment_method,payment_status) VALUES (".current_user_id().",$total,'$method','pending')");
    $order_id = mysqli_insert_id($conn);
    foreach($cart as $it){
      $pid=(int)$it['id']; $qty=(int)$it['qty']; $price=(float)$it['price'];
      mysqli_query($conn,"INSERT INTO order_items (order_id,product_id,qty,price) VALUES ($order_id,$pid,$qty,$price)");
    }
    mysqli_query($conn,"INSERT INTO payments (order_id,gateway,amount,status) VALUES ($order_id,'$method',$total,'pending')");
    mysqli_query($conn,"INSERT INTO notifications(type,message) VALUES('order','New order #$order_id placed')");
    mysqli_commit($conn);
    if($method==='cod'){
      mysqli_query($conn,"UPDATE orders SET payment_status='success' WHERE id=$order_id");
      mysqli_query($conn,"UPDATE payments SET status='success', transaction_id='COD-'.time() WHERE order_id=$order_id");
      unset($_SESSION['cart']); flash('Order placed successfully (COD).'); header('Location: orders.php'); exit;
    } else {
      header("Location: ../payments/{$method}_start.php?order_id=$order_id"); exit;
    }
  } catch(Throwable $e){ mysqli_rollback($conn); flash('Checkout error: '.$e->getMessage(),'danger'); }
}
?>
<h3>Checkout</h3>
<p>Total payable: <strong>Rs. <?= number_format($total,2) ?></strong></p>
<form method="post" class="mt-3">
  <label class="form-label">Payment Method</label>
  <select class="form-select mb-3" name="payment_method">
    <option value="cod">Cash on Delivery</option>
    <option value="khalti">Khalti</option>
    <option value="esewa">eSewa</option>
    <option value="card">Card</option>
  </select>
  <button class="btn btn-primary">Pay & Place Order</button>
</form>
<?php include 'footer.php'; ?>
