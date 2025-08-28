<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

// Admin login check
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// Include header
include __DIR__ . '/../include/admin_header.php';

// Search/filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Base query
$query = "SELECT o.*, u.name as user_name, u.email as user_email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id=u.id
          WHERE 1";

// Append search
if ($search) $query .= " AND (o.order_id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
$params = [];
$types = '';
if ($search) {
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
    $types = 'sss';
}

// Append status filter
if ($status_filter) {
    $query .= " AND o.payment_status=?";
    $params[] = $status_filter;
    $types .= 's';
}

// Append date filter
if ($date_filter) {
    $query .= " AND DATE(o.created_at)=?";
    $params[] = $date_filter;
    $types .= 's';
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>

<div class="container mt-4">
    <h2>All Orders</h2>

    <!-- Filter Form -->
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search order/user/email" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="PAID" <?= $status_filter=='PAID'?'selected':'' ?>>PAID</option>
                <option value="PENDING" <?= $status_filter=='PENDING'?'selected':'' ?>>PENDING</option>
                <option value="FAILED" <?= $status_filter=='FAILED'?'selected':'' ?>>FAILED</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date_filter) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Export Buttons -->
    <div class="mb-3 d-flex justify-content-end gap-2">
        <form method="post" action="export_orders.php">
            <button type="submit" name="export_csv" class="btn btn-success btn-sm">
                <i class="fa fa-file-csv"></i> Export CSV
            </button>
            <button type="submit" name="export_excel" class="btn btn-primary btn-sm">
                <i class="fa fa-file-excel"></i> Export Excel
            </button>
        </form>
    </div>

    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Order ID</th>
                <th>User Name</th>
                <th>Email</th>
                <th>Total Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Invoice</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($orders && $orders->num_rows > 0): ?>
                <?php while($o = $orders->fetch_assoc()):
                    // Check invoice
                    $stmt_inv = $conn->prepare("SELECT invoice_file FROM invoices WHERE order_id=? LIMIT 1");
                    $stmt_inv->bind_param("i", $o['id']);
                    $stmt_inv->execute();
                    $inv = $stmt_inv->get_result()->fetch_assoc();
                    $stmt_inv->close();
                ?>
                <tr>
                    <td><?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['order_id']) ?></td>
                    <td><?= htmlspecialchars($o['user_name'] ?? 'Guest') ?></td>
                    <td><?= htmlspecialchars($o['user_email'] ?? '-') ?></td>
                    <td>Rs. <?= number_format($o['total_amount'],2) ?></td>
                    <td><?= htmlspecialchars($o['payment_method'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($o['payment_status']) ?></td>
                    <td>
                        <?php if($inv && $inv['invoice_file']): ?>
                            <a href="../../invoices/<?= $inv['invoice_file'] ?>" target="_blank" class="btn btn-sm btn-warning">View</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?= $o['created_at'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderModal<?= $o['id'] ?>">View</button>
                        <a href="order_delete.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this order?')">Delete</a>
                    </td>
                </tr>

                <!-- Order Modal -->
                <div class="modal fade" id="orderModal<?= $o['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Order Details - <?= htmlspecialchars($o['order_id']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php
                                $stmt_items = $conn->prepare("SELECT oi.*, p.title FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
                                $stmt_items->bind_param("i", $o['id']);
                                $stmt_items->execute();
                                $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
                                $stmt_items->close();
                                ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($items as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['title']) ?></td>
                                                <td><?= $item['quantity'] ?></td>
                                                <td><?= number_format($item['price'],2) ?></td>
                                                <td><?= number_format($item['price']*$item['quantity'],2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="text-center">No orders found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
