<?php include 'header_admin.php'; ?>
<h2>Orders</h2>
<div class="table-responsive">
<table class="table table-striped">
<thead><tr><th>ID</th><th>Payer</th><th>Total</th><th>Method</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>
<?php
$res = mysqli_query($conn,"SELECT * FROM orders ORDER BY created_at DESC");
while($o=mysqli_fetch_assoc($res)){
    echo '<tr>
      <td>'.$o['id'].'</td>
      <td>'.htmlspecialchars($o['payer_name']).'<br><small>'.htmlspecialchars($o['payer_email']).'</small></td>
      <td>Rs. '.number_format($o['total'],2).'</td>
      <td>'.$o['payment_method'].'</td>
      <td>'.$o['payment_status'].'</td>
      <td>'.$o['created_at'].'</td>
      <td><a class="btn btn-sm btn-primary" href="order_view.php?id='.$o['id'].'">View</a></td>
    </tr>';
}
?>
</tbody>
</table>
</div>
<?php include 'footer_admin.php'; ?>
