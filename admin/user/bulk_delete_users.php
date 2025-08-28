<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

// Ensure admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('Location: ../login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD']=='POST' && !empty($_POST['user_ids'])){
    $ids = array_map('intval', $_POST['user_ids']);
    $placeholders = implode(',', array_fill(0,count($ids),'?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("DELETE FROM users WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);

    if($stmt->execute()){
        $_SESSION['flash_success'] = count($ids).' user(s) deleted successfully.';
    }else{
        $_SESSION['flash_error'] = 'Failed to delete users: '.$stmt->error;
    }
    $stmt->close();
}

header('Location: user.php');
exit;
