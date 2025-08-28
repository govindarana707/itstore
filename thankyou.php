<?php include 'includes/header.php'; ?>
<h2>Thank you! Your order was received.</h2>
<p>Order ID: <?php echo intval($_GET['order_id'] ?? 0); ?></p>
<?php include 'includes/footer.php'; ?>
