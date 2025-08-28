<?php
session_start();

$pid = intval($_POST['product_id'] ?? 0);

if($pid <= 0 || !isset($_SESSION['cart'][$pid])){
    echo json_encode(['status'=>'error','message'=>'Product not found in cart']);
    exit;
}

// Remove the product
unset($_SESSION['cart'][$pid]);

// Recalculate total
$total = 0;
foreach($_SESSION['cart'] as $item){
    $total += $item['price'] * $item['qty'];
}

// Return response
echo json_encode([
    'status' => 'success',
    'total' => $total,
    'cartCount' => count($_SESSION['cart'])
]);
