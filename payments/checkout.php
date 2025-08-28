<?php
session_start();
require_once '../includes/config.php';
include('../includes/header.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    die("❌ Invalid access or empty cart.");
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];

// Fetch user details
$stmt = $conn->prepare("SELECT name,email,phone,address FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$fullname = $user['name'] ?? 'Guest';
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$address = $user['address'] ?? '';

// Ensure each cart item has a title and id
foreach ($cart as &$item) {
    if (!isset($item['id']) || !isset($item['title'])) {
        $stmt = $conn->prepare("SELECT id, title FROM products WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $item['id'] = $res['id'];
            $item['title'] = $res['title'];
        } else {
            $item['title'] = 'Unknown Product';
        }
        $stmt->close();
    }
}

// Generate order reference and transaction UUID
$order_ref = "ORD_" . time();
$transaction_uuid = uniqid("TXN_");

// Calculate totals
$total_amount = 0;
foreach ($cart as $item) $total_amount += $item['price'] * $item['qty'];
$product_service_charge = 0.00;
$product_delivery_charge = 0.00;
$tax_amount = 0.00;
$total_with_all = $total_amount + $product_service_charge + $product_delivery_charge + $tax_amount;
$total_str = number_format($total_with_all, 2, '.', '');
$tax_str = number_format($tax_amount, 2, '.', '');

// Insert order with payment_status='UNPAID'
$order_stmt = $conn->prepare("
    INSERT INTO orders
    (user_id, fullname, email, phone, address, order_id, transaction_uuid, total_amount, payment_status, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'UNPAID', 'PENDING', NOW())
");
$order_stmt->bind_param("isssssds", $user_id, $fullname, $email, $phone, $address, $order_ref, $transaction_uuid, $total_with_all);
if(!$order_stmt->execute()) die("Order insert failed: ".$order_stmt->error);

$numeric_order_id = $conn->insert_id;  // auto-increment primary key for order_items
$order_stmt->close();

// Insert order_items
foreach ($cart as $item) {
    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    if (!$stmt_item) die("Prepare failed: " . $conn->error);
    $stmt_item->bind_param("iiid", $numeric_order_id, $item['id'], $item['qty'], $item['price']);
    if (!$stmt_item->execute()) die("Order item insert failed: " . $stmt_item->error);
    $stmt_item->close();
}

// eSewa config (UNCHANGED, your original logic)
$product_code = "EPAYTEST";
$secret_key   = "8gBm/:&EnhH.1/q";
$signed_fields = "total_amount,transaction_uuid,product_code";
$signature_string = "total_amount=$total_str,transaction_uuid=$transaction_uuid,product_code=$product_code";
$signature = base64_encode(hash_hmac('sha256', $signature_string, $secret_key, true));

$success_url = "http://localhost/itstore/payments/esewa_success.php?order_id=" . urlencode($order_ref);
$failure_url = "http://localhost/itstore/payments/esewa_failure.php?order_id=" . urlencode($order_ref);

// Clear cart
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IT Store | Checkout & Payment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://khalti.com/static/khalti-checkout.js"></script>
<style>
body { background:#f0f2f5; font-family:'Segoe UI',sans-serif; }
.card { border-radius:20px; padding:30px; box-shadow:0 8px 25px rgba(0,0,0,0.12); animation:fadeIn 1s ease-in-out; }
h2 { font-weight:700; margin-bottom:20px; }
.table td, .table th { vertical-align:middle; }
.btn { border-radius:50px; padding:10px 25px; font-weight:600; transition:0.3s; }
.btn:hover { transform:translateY(-3px); box-shadow:0 4px 15px rgba(0,0,0,0.2); }
@keyframes fadeIn { 0%{opacity:0;transform:translateY(-20px);}100%{opacity:1;transform:translateY(0);} }
</style>
</head>
<body>
<div class="container py-5">

<h2 class="text-center mb-4">👤 Customer & Order Info</h2>
<div class="row justify-content-center mb-4">
<div class="col-md-8">
<div class="card bg-light text-dark">
<p><strong>Order ID:</strong> <?= htmlspecialchars($order_ref) ?></p>
<p><strong>Name:</strong> <?= htmlspecialchars($fullname) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
<p><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></p>
<p><strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
<h4 class="text-success">💰 Total: Rs. <?= htmlspecialchars($total_str) ?></h4>
</div>
</div>
</div>

<h2 class="text-center mb-3">🛍️ Order Summary</h2>
<div class="row justify-content-center mb-4">
<div class="col-md-8">
<table class="table table-borderless table-striped">
<thead class="table-light">
<tr>
<th>Product</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Total</th>
</tr>
</thead>
<tbody>
<?php foreach ($cart as $item): ?>
<tr>
<td><?= htmlspecialchars($item['title']) ?></td>
<td class="text-center"><?= $item['qty'] ?></td>
<td class="text-end"><?= number_format($item['price'],2) ?></td>
<td class="text-end"><?= number_format($item['price']*$item['qty'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<h2 class="text-center mb-3">💳 Choose Payment Method</h2>
<div class="row justify-content-center">
<div class="col-md-4 mb-3 text-center">
<form method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form">
<input type="hidden" name="amount" value="<?= $total_str ?>">
<input type="hidden" name="total_amount" value="<?= $total_str ?>">
<input type="hidden" name="tax_amount" value="<?= $tax_str ?>">
<input type="hidden" name="product_code" value="<?= $product_code ?>">
<input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid ?>">
<input type="hidden" name="signed_field_names" value="<?= $signed_fields ?>">
<input type="hidden" name="signature" value="<?= $signature ?>">
<input type="hidden" name="success_url" value="<?= $success_url ?>">
<input type="hidden" name="failure_url" value="<?= $failure_url ?>">
<input type="hidden" name="product_service_charge" value="0.00">
<input type="hidden" name="product_delivery_charge" value="0.00">
<button type="submit" class="btn w-100 p-0 border-0">
    <img src="../assets/img/esewa.png" alt="Pay via eSewa" style="width:200px;">
</button>
</form>
</div>

<div class="col-md-4 mb-3 text-center">
<button id="khaltiBtn" class="btn w-100 p-0 border-0">
    <img src="https://khalti.com/assets/img/khalti-logo.png" alt="Pay via Khalti" style="width:200px;">
</button>
</div>
</div>
</div>

<script>
var config = {
    publicKey: "688ef743783f443abf185c344d988453", // Sandbox key
    productIdentity: "<?= $order_ref ?>",
    productName: "IT Store Order",
    productUrl: "<?= $ngrok_url ?>/itstore/payments/checkout.php",
    eventHandler: {
        onSuccess(payload) {
            window.location.href = "khalti_verify.php?order_id=<?= $order_ref ?>&token=" 
                + payload.token + "&amount=<?= intval($total_with_all*100) ?>";
        },
        onError(error) { 
            console.log("Khalti error:", error);
            alert("Payment failed! Please try again."); 
        },
        onClose() { console.log("Khalti widget closed"); }
    },
    paymentPreference: ["KHALTI","EBANKING","MOBILE_BANKING","CONNECT_IPS","SCT"]
};

var checkout = new KhaltiCheckout(config);
document.getElementById("khaltiBtn").addEventListener("click", function () {
    checkout.show({amount: <?= intval($total_with_all*100) ?>});
});
</script>
</body>
</html>
