<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

$wishlist = $_SESSION['wishlist'] ?? [];
$count = count($wishlist);
$html = '';

if($wishlist){
    foreach($wishlist as $pid => $item){
        $imgPath = !empty($item['image']) && file_exists(__DIR__.'/../images/'.$item['image']) 
                    ? '../images/'.$item['image'] 
                    : '../assets/img/default.svg';

        $html .= '<div class="item align-items-center d-flex justify-content-between">';
        $html .= '<div class="d-flex align-items-center">';
        $html .= '<img src="'.$imgPath.'" alt="Product">';
        $html .= '<div><strong>'.htmlspecialchars($item['title']).'</strong><br>Rs. '.number_format($item['price'],2).'</div>';
        $html .= '</div>';
        $html .= '<a href="#" class="remove-wishlist-item text-danger" data-product-id="'.$pid.'"><i class="fa fa-trash"></i></a>';
        $html .= '</div>';
    }
    $html .= '<div class="text-center py-2"><a href="wishlist.php" class="btn btn-sm btn-primary w-100">View Wishlist</a></div>';
} else {
    $html = '<p class="text-center p-3">Your wishlist is empty</p>';
}

echo json_encode(['status'=>'success','html'=>$html,'count'=>$count]);
