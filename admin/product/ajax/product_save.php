<?php
include '../../../includes/config.php';

$id = intval($_POST['id'] ?? 0);
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$price = floatval($_POST['price'] ?? 0);
$category_id = intval($_POST['category_id'] ?? 0);

// Upload folder (absolute path)
$target_dir = __DIR__ . '/../../uploads/';
if(!is_dir($target_dir)){
    mkdir($target_dir, 0777, true); // ensure writable
}

$images = [];

// Handle file uploads if any
if(!empty($_FILES['images']['tmp_name'][0])){
    foreach($_FILES['images']['tmp_name'] as $key => $tmp_name){
        $filename = $target_dir . time() . '_' . $_FILES['images']['name'][$key];
        if(move_uploaded_file($tmp_name, $filename)){
            $images[] = 'admin/uploads/' . time() . '_' . $_FILES['images']['name'][$key];
        }
    }
}

if($id > 0){
    // Edit product
    if(empty($images)){
        // Keep existing images if no new images uploaded
        $existing = $conn->query("SELECT images FROM products WHERE id=$id")->fetch_assoc();
        $images = json_decode($existing['images'], true) ?: [];
    }

    $images_json = json_encode($images);
    $stmt = $conn->prepare("UPDATE products SET title=?, description=?, price=?, category_id=?, images=? WHERE id=?");
    $stmt->bind_param("ssdssi", $title, $description, $price, $category_id, $images_json, $id);
    if($stmt->execute()){
        echo "Product updated successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

} else {
    // Add new product
    $images_json = json_encode($images);
    $stmt = $conn->prepare("INSERT INTO products (title, description, price, category_id, images) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssdss", $title, $description, $price, $category_id, $images_json);
    if($stmt->execute()){
        echo "Product added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
