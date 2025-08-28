<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

try {
    if (!isset($_SESSION['user_id'])) throw new Exception("User not logged in");
    if (empty($_SESSION['cart'])) throw new Exception("Your cart is empty");

    $user_id = $_SESSION['user_id'];
    $cart = $_SESSION['cart'];

    // Fetch user details
    $stmt = $conn->prepare("SELECT name, phone, address FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $fullname = $user['name'] ?? 'Guest';
    $phone = $user['phone'] ?? '';
    $address = $user['address'] ?? '';

    // Calculate total
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += ($item['qty'] ?? 1) * ($item['price'] ?? 0);
    }

    $total_amount = round($total_amount, 2);

    // Generate unique order & transaction ID
    $order_id = "ORD_" . time();
    $transaction_uuid = uniqid("TXN_");

    // Insert order into DB with PENDING status
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, fullname, phone, address, order_id, transaction_uuid, total_amount, payment_method, payment_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'eSewa', 'PENDING', NOW())
    ");
    $stmt->bind_param("isssssd", $user_id, $fullname, $phone, $address, $order_id, $transaction_uuid, $total_amount);
    $stmt->execute();
    $stmt->close();

    // Insert each cart item
    foreach ($cart as $item) {
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['qty'], $item['price']);
        $item_stmt->execute();
        $item_stmt->close();
    }

    // Clear cart session
    unset($_SESSION['cart']);

    // Redirect to eSewa payment
    ?>
    <form id="esewaForm" method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form">
        <input type="hidden" name="amount" value="<?= $total_amount ?>">
        <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
        <input type="hidden" name="product_code" value="EPAYTEST">
        <input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid ?>">
        <input type="hidden" name="success_url" value="http://localhost/itstore/payments/esewa_success.php?order_id=<?= $order_id ?>">
        <input type="hidden" name="failure_url" value="http://localhost/itstore/payments/esewa_failure.php?order_id=<?= $order_id ?>">
        <button type="submit">Redirecting to eSewa...</button>
    </form>
    <script>document.getElementById('esewaForm').submit();</script>
    <?php

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
