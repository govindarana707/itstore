<?php
session_start();
require_once __DIR__ . '/includes/config.php'; // Adjust path to your config

// Validate required GET parameters
if (!isset($_GET['token'], $_GET['amount'], $_GET['order_id'])) {
    die('Missing token, amount, or order_id.');
}

$token = $_GET['token'];
$amount = (int)$_GET['amount']; // in paisa
$order_id = $_GET['order_id'];
$fullname = $_GET['fullname'] ?? '';
$phone = $_GET['phone'] ?? '';
$address = $_GET['address'] ?? '';

// Get session cart
$user_id = $_SESSION['user_id'] ?? null;
$cart = $_SESSION['cart'] ?? [];

if (!$user_id || empty($cart)) {
    die('Session expired or cart is empty.');
}

// Khalti verification
$khalti_secret_key = 'YOUR_KHALTI_SECRET_KEY'; // Replace this
$verify_url = "https://khalti.com/api/<?php
session_start();
require_once __DIR__ . '/../includes/config.php'; // DB connection

$order_id = $_GET['order_id'] ?? '';
$order_id = explode('?', $order_id)[0]; // remove extra query params

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id=? LIMIT 1");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("❌ Order not found in database.");
}

// Update order if not already PAID
if ($order['payment_status'] !== 'PAID') {
    $stmt = $conn->prepare("UPDATE orders SET payment_status='PAID', status='PAID' WHERE order_id=? LIMIT 1");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $stmt->close();

    // Update order_items
    $stmt = $conn->prepare("UPDATE order_items SET payment_status='PAID', paid_at=NOW() WHERE order_id=?");
    $stmt->bind_param("i", $order['id']); // note: order_id in order_items is numeric (foreign key)
    $stmt->execute();
    $stmt->close();
}

echo "✅ Payment Successful! Order ID: " . htmlspecialchars($order_id);
echo '<br><a href="../my_courses.php">Go to My Courses</a>';
?>
v2/payment/verify/";

$data = [
    'token' => $token,
    'amount' => $amount
];

$curl = curl_init($verify_url);
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Key $khalti_secret_key",
        "Content-Type: application/json"
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if (curl_errno($curl)) {
    die("cURL Error: " . curl_error($curl));
}
curl_close($curl);

$result = json_decode($response, true);

// Handle response
if ($http_code === 200 && isset($result['idx'])) {
    try {
        $conn->begin_transaction();

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, fullname, phone, address, order_id, transaction_uuid, total_amount, payment_method, payment_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'khalti', 'Paid', NOW())");
        $txn_id = $result['idx'];
        $total_amount = $amount / 100; // convert paisa to Rs
        $stmt->bind_param("isssssd", $user_id, $fullname, $phone, $address, $order_id, $txn_id, $total_amount);
        $stmt->execute();
        $order_db_id = $stmt->insert_id;
        $stmt->close();

        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
        foreach ($cart as $item) {
            $product_id = $item['id'];
            $qty = $item['qty'];
            $price = $item['price'];
            $stmt->bind_param("iiii", $order_db_id, $product_id, $qty, $price);
            $stmt->execute();
        }
        $stmt->close();

        $conn->commit();
        unset($_SESSION['cart']);

        // Redirect to success page
        header("Location: payment_success.php?order_id=" . urlencode($order_id));
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("DB Error: " . $e->getMessage());
    }

} else {
    $error = $result['detail'] ?? 'Khalti verification failed';
    header("Location: payment_failed.php?order_id=" . urlencode($order_id) . "&message=" . urlencode($error));
    exit;
}
