<?php
session_start();
require_once '../includes/config.php';

// Ensure required parameters are present
if (!isset($_GET['token'], $_GET['order_id'], $_GET['amount'])) {
    die("Invalid request. Missing token, order_id, or amount.");
}

$token = $_GET['token'];
$order_id_ref = $_GET['order_id'];
$amount = $_GET['amount']; // Amount in paisa

// Convert paisa to rupees
$amount_rs = $amount / 100;

// Fetch order from DB
$stmt = $conn->prepare("SELECT id, payment_status FROM orders WHERE order_id=? LIMIT 1");
$stmt->bind_param("s", $order_id_ref);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Khalti secret key (sandbox)
$secret_key = "e7e919ef979c4c8cbcb0cf33f7e2f0db"; // Replace with your sandbox secret key

// Khalti verification endpoint (sandbox)
$verify_url = "https://khalti.com/api/v2/payment/verify/";

$data = array(
    "token" => $token,
    "amount" => intval($amount) // amount in paisa
);

$options = array(
    'http' => array(
        'header'  => "Authorization: Key $secret_key\r\n" .
                     "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($verify_url, false, $context);

if ($result === FALSE) {
    die("Payment verification failed. Try again.");
}

$response = json_decode($result, true);

// Check if payment is successful
if (isset($response['status']) && $response['status'] == "Completed") {
    // Update order status in DB
    $update_stmt = $conn->prepare("UPDATE orders SET payment_status='PAID', payment_method='KHALTI', updated_at=NOW() WHERE id=?");
    $update_stmt->bind_param("i", $order['id']);
    $update_stmt->execute();
    $update_stmt->close();

    echo "<h2>✅ Payment Successful!</h2>";
    echo "<p>Order ID: $order_id_ref</p>";
    echo "<p>Amount Paid: Rs. $amount_rs</p>";
    echo "<p><a href='/itstore'>Go back to Home</a></p>";
} else {
    echo "<h2>❌ Payment Failed!</h2>";
    echo "<p>Order ID: $order_id_ref</p>";
    echo "<p>Please try again.</p>";
    echo "<p><a href='/itstore/checkout.php'>Go back to Checkout</a></p>";
}
?>
