
<?php include 'header.php'; if(!is_logged_in()){ header('Location: ../auth/login.php'); exit; }
$uid = current_user_id(); $orders = mysqli_query($conn,"SELECT * FROM orders WHERE user_id=$uid ORDER BY id DESC");
?>
<h3>My Orders</h3>
<table class="table"><thead><tr><th>#</th><th>Total</th><th>Payment</th><th>Delivery</th><th>Placed</th></tr></thead><tbody>
<?php while($o=mysqli_fetch_assoc($orders)): ?><tr>
<td>#<?= $o['id'] ?></td><td>Rs. <?= number_format($o['total'],2) ?></td>
<td><?= $o['payment_method'] ?> / <?= $o['payment_status'] ?></td>
<td><?= $o['delivery_status'] ?></td><td><?= $o['created_at'] ?></td></tr>
<?php endwhile; ?></tbody></table>
<?php include 'footer.php'; ?>
