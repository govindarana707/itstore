<?php
session_start();
$product_id = intval($_POST['product_id'] ?? 0);
if(!$product_id){ echo json_encode(['status'=>'error','message'=>'Invalid']); exit; }
if(!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];

if(in_array($product_id,$_SESSION['wishlist'])){
    $_SESSION['wishlist'] = array_diff($_SESSION['wishlist'], [$product_id]);
    $status = 'removed';
    $message = 'Removed from wishlist';
} else {
    $_SESSION['wishlist'][] = $product_id;
    $status = 'added';
    $message = 'Added to wishlist';
}

$count = count($_SESSION['wishlist']);
echo json_encode(['status'=>$status,'message'=>$message,'count'=>$count]);
