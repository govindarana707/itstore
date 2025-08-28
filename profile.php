<?php
session_start();
require_once "includes/config.php";
include "includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$sql = "SELECT id, name, email, phone, address, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Fetch orders
$sql = "
    SELECT o.id AS order_id, o.order_id AS invoice_order_id, o.total_amount, o.status, o.payment_method, o.created_at,
           oi.product_id, oi.quantity, oi.price, p.title
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orderResult = $stmt->get_result();
$orders = [];
while ($row = $orderResult->fetch_assoc()) {
    $orders[$row['order_id']]['info'] = [
        'order_id' => $row['invoice_order_id'], // for invoice link
        'total_amount' => $row['total_amount'],
        'status' => $row['status'],
        'payment_method' => $row['payment_method'],
        'created_at' => $row['created_at']
    ];
    $orders[$row['order_id']]['items'][] = [
        'title' => $row['title'],
        'quantity' => $row['quantity'],
        'price' => $row['price']
    ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container py-4">

<h2 class="mb-4">My Profile</h2>

<div class="alert-container mb-3"></div>

<!-- Personal Info -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">Personal Information</h5>
        <p><strong>Name:</strong> <span id="profileName"><?= htmlspecialchars($user['name']); ?></span></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
        <p><strong>Phone:</strong> <span id="profilePhone"><?= htmlspecialchars($user['phone']); ?></span></p>
        <p><strong>Address:</strong> <span id="profileAddress"><?= nl2br(htmlspecialchars($user['address'])); ?></span></p>
        <p><strong>Joined:</strong> <?= date("M d, Y", strtotime($user['created_at'])); ?></p>

        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
    </div>
</div>

<!-- Order History Accordion -->
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title">Order History</h5>
        <?php if ($orders): ?>
            <div class="accordion" id="ordersAccordion">
                <?php foreach ($orders as $order_id => $order): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $order_id; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $order_id; ?>">
                                Order #<?= $order_id; ?> - <?= date("M d, Y H:i", strtotime($order['info']['created_at'])); ?>
                            </button>
                        </h2>
                        <div id="collapse<?= $order_id; ?>" class="accordion-collapse collapse" data-bs-parent="#ordersAccordion">
                            <div class="accordion-body">
                                <p>Status: <span class="badge bg-info"><?= htmlspecialchars($order['info']['status']); ?></span></p>
                                <p>Payment: <?= htmlspecialchars($order['info']['payment_method']); ?> | Total: <strong>NPR <?= number_format($order['info']['total_amount'], 2); ?></strong></p>
                                <ul>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li><?= htmlspecialchars($item['title']); ?> (x<?= $item['quantity']; ?>) - NPR <?= number_format($item['price'], 2); ?></li>
                                    <?php endforeach; ?>
                                </ul>

                                <!-- View Invoice Button -->
                                <?php if($order['info']['status'] === 'PAID'): ?>
                                    <a href="invoice/generate_invoice.php?order_id=<?= urlencode($order['info']['order_id']); ?>" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        View Invoice
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted mt-2 d-block">Invoice not available</span>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editProfileForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?= $user_id; ?>">
                    <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required></div>
                    <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>"></div>
                    <div class="mb-3"><label>Address</label><textarea name="address" class="form-control" required><?= htmlspecialchars($user['address']); ?></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save changes</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="changePasswordForm">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?= $user_id; ?>">
                    <div class="mb-3"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="mb-3"><label>New Password</label><input type="password" name="new_password" class="form-control" required></div>
                    <div class="mb-3"><label>Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-warning">Update Password</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {

    function showAlert(status, message) {
        var alertClass = status === 'success' ? 'alert-success' : 'alert-danger';
        var alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                         </div>`;
        $('.alert-container').html(alertHtml);
    }

    // Edit Profile AJAX
    $('#editProfileForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'update_profile.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $('#editProfileModal').modal('hide');
                showAlert(res.status,res.message);
                if(res.status === 'success') {
                    $('#profileName').text(res.updated.name);
                    $('#profilePhone').text(res.updated.phone);
                    $('#profileAddress').html(res.updated.address.replace(/\n/g,'<br>'));
                }
            },
            error: function(xhr) {
                showAlert('error','Something went wrong. ' + xhr.responseText);
            }
        });
    });

    // Change Password AJAX
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'change_password.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $('#changePasswordModal').modal('hide');
                showAlert(res.status,res.message);
            },
            error: function(xhr) {
                showAlert('error','Something went wrong. ' + xhr.responseText);
            }
        });
    });

});
</script>
</body>
</html>
