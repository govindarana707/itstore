<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get status filter from query
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10; // orders per page
$offset = ($page - 1) * $limit;

// Count total orders for pagination
$countQuery = "SELECT COUNT(*) as total FROM orders WHERE 1=1";
$paramsCount = [];
if (!empty($status)) {
    $countQuery .= " AND payment_status = ?";
    $paramsCount[] = $status;
}
if (!empty($search)) {
    $countQuery .= " AND (fullname LIKE ? OR order_id LIKE ?)";
    $search_param = "%$search%";
    $paramsCount[] = $search_param;
    $paramsCount[] = $search_param;
}

$stmtCount = mysqli_prepare($conn, $countQuery);
if (!empty($paramsCount)) {
    $types = str_repeat('s', count($paramsCount));
    mysqli_stmt_bind_param($stmtCount, $types, ...$paramsCount);
}
mysqli_stmt_execute($stmtCount);
$resultCount = mysqli_stmt_get_result($stmtCount);
$totalOrders = mysqli_fetch_assoc($resultCount)['total'];
$totalPages = ceil($totalOrders / $limit);

// Fetch orders
$query = "SELECT o.*, u.name as user_name, u.email as user_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1";
$params = [];
if (!empty($status)) {
    $query .= " AND o.payment_status = ?";
    $params[] = $status;
}
if (!empty($search)) {
    $query .= " AND (o.fullname LIKE ? OR o.order_id LIKE ?)";
    $params[] = $search_param;
    $params[] = $search_param;
}
$query .= " ORDER BY o.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    $types = str_repeat('s', count($params) - 2) . 'ii';
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Orders List</h2>

    <!-- Filter & Search -->
    <form class="row g-3 mb-4" method="get">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="PENDING" <?= $status=='PENDING'?'selected':'' ?>>Pending</option>
                <option value="SUCCESS" <?= $status=='SUCCESS'?'selected':'' ?>>Success</option>
                <option value="FAILED" <?= $status=='FAILED'?'selected':'' ?>>Failed</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search by Name or Order ID" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="orders.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- Orders Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Order ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Total Amount</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['order_id']) ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['user_email']) ?></td>
                    <td><?= number_format($row['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['payment_method']) ?></td>
                    <td><?= $row['payment_status'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center">No orders found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for($i=1; $i<=$totalPages; $i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="orders.php?page=<?= $i ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
</body>
</html>
