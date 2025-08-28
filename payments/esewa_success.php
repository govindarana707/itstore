<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

try {
    if (!isset($_GET['order_id'])) throw new Exception("Missing order ID.");

    $order_id = explode('?', $_GET['order_id'])[0];

    // Fetch order
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id=? LIMIT 1");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) throw new Exception("Order not found.");

    // Update payment status and method if not already paid
    if ($order['payment_status'] !== 'PAID') {
        $payment_method = 'Esewa';

        $stmt = $conn->prepare("UPDATE orders SET payment_status='PAID', status='PAID', payment_method=? WHERE id=?");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("si", $payment_method, $order['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $payment_method = $order['payment_method']; // already PAID
    }

    // Insert order_items if not already (optional)
    $stmtCheck = $conn->prepare("SELECT COUNT(*) as cnt FROM order_items WHERE order_id=?");
    $stmtCheck->bind_param("i", $order['id']);
    $stmtCheck->execute();
    $check = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if ($check['cnt'] == 0) {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            throw new Exception("Cart is empty. Cannot insert order items.");
        }

        foreach ($_SESSION['cart'] as $item) {
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmtItem->bind_param("iiid", $order['id'], $item['product_id'], $item['qty'], $item['price']);
            $stmtItem->execute();
            $stmtItem->close();
        }
    }

    // Clear cart
    if (isset($_SESSION['cart'])) unset($_SESSION['cart']);

} catch (Exception $e) {
    if (isset($_SESSION['cart'])) unset($_SESSION['cart']);
    $errorMessage = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>eSewa Payment Status</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #f0f2f5;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    font-family: 'Segoe UI', sans-serif;
}
.card {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    text-align: center;
    animation: fadeIn 1s ease-in-out;
}
h2 { font-weight: 700; margin-bottom: 20px; }
.success-icon { font-size: 80px; color: #28a745; animation: pop 0.5s ease forwards; }
.error-icon { font-size: 80px; color: #dc3545; animation: shake 0.5s ease forwards; }
.btn { border-radius: 50px; padding: 10px 30px; font-weight: 600; margin: 10px; transition:0.3s; }
.btn:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
@keyframes pop { 0% { transform: scale(0); } 100% { transform: scale(1); } }
@keyframes shake { 0% { transform: translateX(0); } 25% { transform: translateX(-10px); } 50% { transform: translateX(10px); } 75% { transform: translateX(-10px); } 100% { transform: translateX(0); } }
@keyframes fadeIn { 0% { opacity: 0; transform: translateY(-20px); } 100% { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body>
<div class="card">
    <?php if(isset($errorMessage)): ?>
        <div class="error-icon">❌</div>
        <h2>Payment Failed!</h2>
        <p><?= htmlspecialchars($errorMessage) ?></p>
    <?php else: ?>
        <div class="success-icon">✅</div>
        <h2>Payment Successful!</h2>
        <p>Order ID: <strong><?= htmlspecialchars($order['order_id']) ?></strong></p>
        <p>Payment Method: <strong><?= htmlspecialchars($payment_method) ?></strong></p>
        <p>Thank you for your purchase. Your order is confirmed.</p>
    <?php endif; ?>

    <div>
        <a href="../my_courses.php" class="btn btn-success">My Courses</a>
        <a href="../index.php" class="btn btn-primary">Home</a>
        <?php if(!isset($errorMessage)): ?>
        <a href="../invoice/generate_invoice.php?order_id=<?= urlencode($order['order_id']) ?>" class="btn btn-warning">View Invoice</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
