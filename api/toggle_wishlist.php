<?php
session_start();

$pid = intval($_POST['product_id'] ?? 0);
if($pid <= 0){
    echo json_encode(['status'=>'error','message'=>'Invalid product ID']);
    exit;
}

if(!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];

if(isset($_SESSION['wishlist'][$pid])){
    unset($_SESSION['wishlist'][$pid]);
    $status = 'removed';
    $message = 'Removed from wishlist';
} else {
    $_SESSION['wishlist'][$pid] = true;
    $status = 'added';
    $message = 'Added to wishlist';
}

echo json_encode([
    'status' => $status,
    'message' => $message,
    'wishlistCount' => count($_SESSION['wishlist'])
]);
