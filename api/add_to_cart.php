<?php
session_start();
require_once '../includes/config.php';

// Debugging (only enable in dev)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Accept both product_id and course_id
    $productId = intval($_POST['product_id'] ?? $_POST['course_id'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);

    if ($productId <= 0) {
        die("❌ Invalid Product ID");
    }

    // Fetch product from DB
    $stmt = $conn->prepare("SELECT id, title, price, images FROM products WHERE id = ?");
    if (!$stmt) {
        die("❌ SQL Error: " . $conn->error);
    }

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        die("❌ Product not found.");
    }

    // Decode images JSON and determine path
    $images = json_decode($product['images'], true);
    $imagePath = 'assets/img/default.png';
    if (!empty($images[0])) {
        $firstImage = $images[0];
        if (file_exists(__DIR__ . '/../' . $firstImage)) {
            $imagePath = $firstImage;
        }
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update product in cart
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$productId] = [
            'id'    => $product['id'],
            'title' => $product['title'],
            'price' => $product['price'],
            'qty'   => $qty,
            'image' => $imagePath
        ];
    }

    // ✅ Redirect to cart page
    header("Location: ../payments/cart.php");
    exit;
}

// Invalid request
die("❌ Invalid request.");
