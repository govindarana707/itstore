<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "includes/config.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if(empty($user_id) || empty($name) || empty($address)){
        echo json_encode(['status'=>'error','message'=>'Required fields missing.']);
        exit;
    }

    $sql = "UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if(!$stmt){
        echo json_encode(['status'=>'error','message'=>'SQL Prepare Error: '.$conn->error]);
        exit;
    }

    $stmt->bind_param("sssi", $name, $phone, $address, $user_id);

    if($stmt->execute()){
        echo json_encode(['status'=>'success','message'=>'Profile updated successfully.','updated'=>['name'=>$name,'phone'=>$phone,'address'=>$address]]);
    } else {
        echo json_encode(['status'=>'error','message'=>'SQL Execute Error: '.$stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status'=>'error','message'=>'Invalid request method.']);
}
?>
