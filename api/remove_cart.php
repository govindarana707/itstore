<?php
session_start();
require_once '../includes/helpers.php';

$pid = intval($_POST['product_id'] ?? 0);
if(!$pid || !isset($_SESSION['cart'][$pid])){
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit;
}

// Remove product
unset($_SESSION['cart'][$pid]);

// Calculate new total
$total = 0;
foreach($_SESSION['cart'] as $item){
    $total += $item['qty'] * $item['price'];
}

echo json_encode(['status'=>'success','total'=>$total]);
