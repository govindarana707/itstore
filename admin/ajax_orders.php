<?php
session_start();
include '../includes/config.php';
include '../includes/helpers.php';

// Protect admin page
if(!is_admin()){
    http_response_code(403);
    exit('Access denied');
}

// Get status filter from AJAX request
$status = $_GET['status'] ?? 'all';
$status_sql = '';
$params = [];

if($status !== 'all'){
    $status = mysqli_real_escape_string($conn, $status);
    $status_sql = "WHERE payment_status='$status'";
}

// Fetch recent 20 orders
$query = "SELECT o.id, o.user_id, u.name AS user_name, o.total, o.payment_status, o.created_at 
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id
          $status_sql
          ORDER BY o.created_at DESC
          LIMIT 20";

$res = mysqli_query($conn, $query);

if(mysqli_num_rows($res) == 0){
    echo '<div class="alert alert-info text-center">No orders found for this status.</div>';
    exit;
}

echo '<table class="table table-striped table-bordered">';
echo '<thead class="table-light">
        <tr>
            <th>Order ID</th>
            <th>User</th>
            <th>Total</th>
            <th>Payment Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
      </thead><tbody>';

while($row = mysqli_fetch_assoc($res)){
    $status_badge = '';
    switch($row['payment_status']){
        case 'success': $status_badge = '<span class="badge bg-success">Paid</span>'; break;
        case 'pending': $status_badge = '<span class="badge bg-warning text-dark">Pending</span>'; break;
        case 'failed': $status_badge = '<span class="badge bg-danger">Failed</span>'; break;
        case 'refunded': $status_badge = '<span class="badge bg-secondary">Refunded</span>'; break;
        default: $status_badge = '<span class="badge bg-info">'.htmlspecialchars($row['payment_status']).'</span>'; break;
    }

    echo '<tr>
            <td>#'.$row['id'].'</td>
            <td>'.htmlspecialchars($row['user_name'] ?? 'Guest').'</td>
            <td>Rs. '.number_format($row['total'],2).'</td>
            <td>'.$status_badge.'</td>
            <td>'.date('d M Y, H:i', strtotime($row['created_at'])).'</td>
            <td>
                <a href="order_view.php?id='.$row['id'].'" class="btn btn-sm btn-primary">View</a>
            </td>
          </tr>';
}

echo '</tbody></table>';
?>
