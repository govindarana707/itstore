<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/config.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get filters
$search = $_POST['search'] ?? '';
$statusFilter = $_POST['status'] ?? '';
$paymentFilter = $_POST['payment'] ?? '';

// Build query
$where = "1";
$params = [];
$types = "";

if($search) {
    $where .= " AND (o.order_id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $likeSearch = "%$search%";
    $params = array_merge($params, [$likeSearch, $likeSearch, $likeSearch]);
    $types .= "sss";
}

if($statusFilter) {
    $where .= " AND o.payment_status=?";
    $params[] = $statusFilter;
    $types .= "s";
}

if($paymentFilter) {
    $where .= " AND o.payment_method=?";
    $params[] = $paymentFilter;
    $types .= "s";
}

// Fetch orders
$query = "SELECT o.*, u.name as user_name, u.email as user_email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id=u.id
          WHERE $where
          ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);
if($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();

// Export as CSV
if(isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=orders_'.date('Ymd_His').'.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID','Order ID','User','Email','Total Amount','Payment Method','Status','Created At']);

    while($o = $orders->fetch_assoc()) {
        fputcsv($output, [
            $o['id'],
            $o['order_id'],
            $o['user_name'] ?? 'Guest',
            $o['user_email'] ?? '-',
            $o['total_amount'],
            $o['payment_method'],
            $o['payment_status'],
            $o['created_at']
        ]);
    }
    fclose($output);
    exit;
}

// Export as Excel (simple HTML table as Excel)
if(isset($_POST['export_excel'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=orders_".date('Ymd_His').".xls");

    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Order ID</th><th>User</th><th>Email</th><th>Total Amount</th><th>Payment Method</th><th>Status</th><th>Created At</th></tr>";

    while($o = $orders->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".$o['id']."</td>";
        echo "<td>".$o['order_id']."</td>";
        echo "<td>".($o['user_name'] ?? 'Guest')."</td>";
        echo "<td>".($o['user_email'] ?? '-')."</td>";
        echo "<td>".$o['total_amount']."</td>";
        echo "<td>".$o['payment_method']."</td>";
        echo "<td>".$o['payment_status']."</td>";
        echo "<td>".$o['created_at']."</td>";
        echo "</tr>";
    }

    echo "</table>";
    exit;
}
