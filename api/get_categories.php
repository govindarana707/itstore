<?php include '../includes/config.php';
$r = mysqli_query($conn,"SELECT * FROM categories ORDER BY name"); $out=[]; while($c=mysqli_fetch_assoc($r)) $out[]=$c; header('Content-Type: application/json'); echo json_encode($out); ?>
