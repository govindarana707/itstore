<?php
session_start();
require_once '../includes/config.php';

$pid = $_GET['pid'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if ($pid && $user_id) {
    $stmt = $conn->prepare("UPDATE orders SET status='FAILED', payment_status='FAILED' WHERE order_id=? AND user_id=?");
    $stmt->bind_param("si", $pid, $user_id);
    $stmt->execute();
    $stmt->close();
}

echo "❌ Payment failed or cancelled. Order status updated.";
