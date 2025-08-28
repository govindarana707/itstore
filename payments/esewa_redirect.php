<?php
session_start();
require_once __DIR__ . '/../includes/config.php'; // DB connection

try {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
        throw new Exception("Your cart is empty or you are not logged in.");
    }

    $user_id = $_SESSION['user_id'];
    $cart = $_SESSION['cart'];

    // Fetch user info
    $stmt = $conn->prepare("SELECT name,email,phone,address FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $fullname = $user['name'] ?? 'Guest';
    $email    = $user['email'] ?? '';
    $phone    = $user['phone'] ?? '';
    $address  = $user['address'] ?? '';

    // Calculate total
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += $item['price'] * $item['qty'];
    }

    // Charges
    $product_service_charge = "0.00"; // must be string
    $product_delivery_charge = "0.00";
    $tax_amount = 0.00;

    $total_with_all = $total_amount + floatval($product_service_charge) + floatval($product_delivery_charge) + $tax_amount;

    $total_str = number_format((float)$total_with_all, 2, '.', '');
    $tax_str   = number_format((float)$tax_amount, 2, '.', '');

    // eSewa config
    $product_code = "EPAYTEST";
    $secret_key   = "8gBm/:&EnhH.1/q";
    $transaction_uuid = uniqid("TXN_");
    $order_unique_id  = "ORD_" . time();

    // Signature
    $signed_fields = "total_amount,transaction_uuid,product_code";
    $signature_string = "total_amount=$total_str,transaction_uuid=$transaction_uuid,product_code=$product_code";
    $signature = base64_encode(hash_hmac('sha256', $signature_string, $secret_key, true));

    // Success/failure URLs
    $success_url = "http://localhost/itstore/payments/esewa_success.php?order_id=" . urlencode($order_unique_id);
    $failure_url = "http://localhost/itstore/payments/esewa_failure.php?order_id=" . urlencode($order_unique_id);

    // Insert order as PENDING only **once**
    $stmt = $conn->prepare("INSERT INTO orders 
        (user_id, fullname, email, phone, address, order_id, transaction_uuid, total_amount, payment_method, payment_status, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ESEWA', 'PENDING', 'PENDING', NOW())");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param(
        "issssd",
        $user_id,
        $fullname,
        $email,
        $phone,
        $address,
        $order_unique_id,
        $transaction_uuid,
        $total_with_all
    );
    $stmt->execute();
    $stmt->close();

    // Insert order items
    foreach ($cart as $item) {
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $item_stmt->bind_param("iiid", $order_unique_id, $item['product_id'], $item['qty'], $item['price']);
        $item_stmt->execute();
        $item_stmt->close();
    }

    // Clear cart
    unset($_SESSION['cart']);
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Redirecting to eSewa...</title>
</head>
<body>
<form id="esewaForm" method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form">
    <input type="hidden" name="amount" value="<?= $total_str ?>">
    <input type="hidden" name="total_amount" value="<?= $total_str ?>">
    <input type="hidden" name="tax_amount" value="<?= $tax_str ?>">
    <input type="hidden" name="product_code" value="<?= $product_code ?>">
    <input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid ?>">
    <input type="hidden" name="signed_field_names" value="<?= $signed_fields ?>">
    <input type="hidden" name="signature" value="<?= $signature ?>">
    <input type="hidden" name="success_url" value="<?= $success_url ?>">
    <input type="hidden" name="failure_url" value="<?= $failure_url ?>">
    <input type="hidden" name="product_service_charge" value="<?= $product_service_charge ?>">
    <input type="hidden" name="product_delivery_charge" value="<?= $product_delivery_charge ?>">
    <button type="submit">Pay via eSewa</button>
</form>

<script>
setTimeout(() => { document.getElementById('esewaForm').submit(); }, 2000);
</script>
</body>
</html>
