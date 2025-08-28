<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/config.php';

// Ensure admin is logged in
if(!isset($_SESSION['admin_id'])){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$field = $_POST['field'] ?? '';
$value = trim($_POST['value'] ?? '');

$allowedFields = ['name','phone','email','address'];

if($id <= 0 || !in_array($field, $allowedFields)){
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit;
}

// Update user
$stmt = $conn->prepare("UPDATE users SET $field=? WHERE id=?");
$stmt->bind_param('si',$value,$id);
if($stmt->execute()){
    echo json_encode(['status'=>'success']);
}else{
    echo json_encode(['status'=>'error','message'=>$stmt->error]);
}
$stmt->close();
?>
