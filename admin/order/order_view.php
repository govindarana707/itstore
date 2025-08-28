<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/config.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get order ID from URL
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}
$order_id = intval($_GET['id']);

// Fetch order info
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id=?
    LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Fetch order items (courses/products)
$stmt_items = $conn->prepare("
    SELECT oi.*, p.title as product_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id=?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$order_items = $stmt_items->get_result();
$stmt_items->close();
?>

<?php include __DIR__ . '/../include/admin_header.php'; ?>

<div class="container mt-4">
    <h2>Order Details</h2>
    
    <div class="card mb-4 p-3">
        <h4>Customer Information</h4>
        <p><strong>Name:</strong> <?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['user_email'] ?? '-') ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['user_phone'] ?? '-') ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
    </div>

    <div class="card mb-4 p-3">
        <h4>Order Information</h4>
        <p><strong>Order ID:</strong> <?= htmlspecialchars($order['order_id']) ?></p>
        <p><strong>Transaction UUID:</strong> <?= htmlspecialchars($order['transaction_uuid']) ?></p>
        <p><strong>Total Amount:</strong> Rs. <?= htmlspecialchars($order['total_amount']) ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
        <p><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>
        <p><strong>Order Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
        <p><strong>Created At:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
    </div>

    <div class="card p-3">
        <h4>Purchased Courses</h4>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Course ID</th>
                    <th>Course Title</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if($order_items && $order_items->num_rows > 0): ?>
                    <?php while($item = $order_items->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_id']) ?></td>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td>Rs. <?= htmlspecialchars($item['price']) ?></td>
                            <td>Rs. <?= htmlspecialchars($item['price'] * $item['quantity']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No items found for this order.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="orders.php" class="btn btn-primary mt-3">Back to Orders</a>
</div>

<?php include 'admin_footer.php'; ?>
