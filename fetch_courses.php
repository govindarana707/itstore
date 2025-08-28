<?php
session_start();
require_once 'includes/config.php';

$search = $_GET['search'] ?? '';
$priceOrder = $_GET['price'] ?? '';

$sql = "SELECT id, title, price, images, description FROM products WHERE 1";

// Search filter
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND title LIKE '%$search%'";
}

// Price sorting
if ($priceOrder === 'low') {
    $sql .= " ORDER BY price ASC";
} elseif ($priceOrder === 'high') {
    $sql .= " ORDER BY price DESC";
} else {
    $sql .= " ORDER BY created_at DESC";
}

$res = $conn->query($sql);

$courses = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $images = json_decode($row['images'], true);
        $row['image_exists'] = !empty($images[0]) && file_exists($images[0]);
        $row['images'] = $images;
        // Add inCart flag
        $row['inCart'] = isset($_SESSION['cart'][$row['id']]);
        $courses[] = $row;
    }
}

// Return JSON
header('Content-Type: application/json');
echo json_encode($courses);
exit;
