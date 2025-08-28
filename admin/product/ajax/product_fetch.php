<?php
include '../../../includes/config.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();

if($data){
    $data['images'] = json_decode($data['images'], true) ?: [];
    echo json_encode($data);
} else {
    echo json_encode([]);
}
?>
