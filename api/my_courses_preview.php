<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/../includes/config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['status'=>'success','html'=>'<p class="text-center p-3">Login to see your courses.</p>']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$res = $conn->prepare("SELECT p.id, p.title, p.images 
                       FROM products p
                       INNER JOIN order_items oi ON oi.product_id=p.id
                       INNER JOIN orders o ON o.id=oi.order_id
                       WHERE o.user_id=? AND o.payment_status='PAID'
                       GROUP BY p.id
                       ORDER BY o.created_at DESC LIMIT 5");
$res->bind_param("i", $user_id);
$res->execute();
$result = $res->get_result();

$html = '<div class="p-2">';
if($result->num_rows===0){
    $html .= '<p class="text-center p-3">No courses enrolled yet.</p>';
} else {
    while($row = $result->fetch_assoc()){
        $images = json_decode($row['images'], true);
        $img = (!empty($images[0]) && file_exists('../'.$images[0])) ? '../'.$images[0] : '../assets/img/default.svg';

        $html .= '<div class="item">';
        $html .= '<div class="d-flex align-items-center">';
        $html .= '<img src="'.htmlspecialchars($img).'" alt="">';
        $html .= '<div><strong>'.htmlspecialchars($row['title']).'</strong></div>';
        $html .= '</div>';
        $html .= '<a href="'.BASE_URL.'/product.php?id='.$row['id'].'" class="btn btn-sm btn-outline-primary mt-1 w-100">View</a>';
        $html .= '</div>';
    }
}
$html .= '</div>';

echo json_encode(['status'=>'success','html'=>$html]);
