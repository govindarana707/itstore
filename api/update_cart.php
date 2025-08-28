<?php
session_start();
include '../includes/config.php';
include '../includes/helpers.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD']!=='POST'){
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit;
}

$pid = intval($_POST['product_id'] ?? 0);
$qty = intval($_POST['qty'] ?? 0);

if(!isset($_SESSION['cart'][$pid])){
    echo json_encode(['status'=>'error','message'=>'Product not in cart']);
    exit;
}

if($qty<=0){
    unset($_SESSION['cart'][$pid]);
} else {
    $_SESSION['cart'][$pid]['qty'] = $qty;
}

// Recalculate totals
$total = 0;
foreach($_SESSION['cart'] as $item){
    $total += $item['qty']*$item['price'];
}
$subtotal = $qty>0 ? $_SESSION['cart'][$pid]['qty']*$_SESSION['cart'][$pid]['price'] : 0;
$cartCount = array_sum(array_column($_SESSION['cart'],'qty'));

echo json_encode(['status'=>'success','subtotal'=>number_format($subtotal,2),'total'=>number_format($total,2),'cartCount'=>$cartCount]);
