<?php
include '../../../includes/config.php';

$id = intval($_POST['id'] ?? 0);

if($id > 0){
    // Delete images from server
    $res = $conn->query("SELECT images FROM products WHERE id=$id")->fetch_assoc();
    $images = json_decode($res['images'], true) ?: [];
    foreach($images as $img){
        $filename = '../../../' . $img;
        if(file_exists($filename)){
            unlink($filename);
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "Product deleted successfully!";
} else {
    echo "Invalid product ID.";
}
?>
