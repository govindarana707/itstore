<?php
require_once __DIR__ . '/../../includes/config.php';
$status = $_GET['status'] ?? '';

$where = $status ? "WHERE LOWER(payment_status)='".strtolower($status)."'" : '';
$chart_result = $conn->query("
    SELECT DATE_FORMAT(created_at,'%b %Y') AS month, 
           COUNT(*) AS orders_count, 
           COALESCE(SUM(total_amount),0) AS revenue
    FROM orders
    $where
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY created_at ASC
");
$months = $orders = $revenue = [];
while($row = $chart_result->fetch_assoc()){
    $months[] = $row['month'];
    $orders[] = $row['orders_count'];
    $revenue[] = $row['revenue'];
}
echo json_encode(['months'=>$months,'orders'=>$orders,'revenue'=>$revenue]);
