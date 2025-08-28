<?php
session_start();
require_once 'includes/config.php'; // DB connection

if (!isset($_SESSION['user_id'])) {
    die("❌ You must be logged in to view order details.");
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? '';

if (!$order_id) {
    die("❌ Invalid access. No order specified.");
}

// Fetch order for this user
$stmt = $conn->prepare("
    SELECT o.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone, u.address AS user_address
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.order_id = ? AND o.user_id = ?
    LIMIT 1
");
$stmt->bind_param("si", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("❌ Order not found or access denied.");
}

// Fetch order items with product info
$item_stmt = $conn->prepare("
    SELECT oi.quantity, oi.price, p.title, p.description
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$item_stmt->bind_param("i", $order['id']); // use numeric id from orders table
$item_stmt->execute();
$items = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$item_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Details - IT Store</title>
<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>
</head>
<body>

<h2>Order Details: <?= htmlspecialchars($order['order_id']) ?></h2>
<p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?> | 
   <strong>Payment:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>
<p><strong>Customer:</strong> <?= htmlspecialchars($order['user_name']) ?> (<?= htmlspecialchars($order['user_email']) ?>)</p>
<p><strong>Phone:</strong> <?= htmlspecialchars($order['user_phone']) ?></p>
<p><strong>Address:</strong> <?= htmlspecialchars($order['user_address']) ?></p>
<p><strong>Total Amount:</strong> Rs <?= number_format($order['total_amount'], 2) ?></p>

<h3>Items in this Order:</h3>
<?php if ($items): ?>
<table>
    <tr>
        <th>Product</th>
        <th>Description</th>
        <th>Quantity</th>
        <th>Price (Rs)</th>
        <th>Subtotal (Rs)</th>
    </tr>
    <?php foreach ($items as $item): ?>
    <tr>
        <td><?= htmlspecialchars($item['title']) ?></td>
        <td><?= htmlspecialchars($item['description']) ?></td>
        <td><?= $item['quantity'] ?></td>
        <td><?= number_format($item['price'], 2) ?></td>
        <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No items found for this order.</p>
<?php endif; ?>

<br><a href="../index.php">← Back to Home</a>

</body>
</html>
