<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "Category deleted successfully!";
    } else {
        $_SESSION['flash_error'] = "Database error: " . $stmt->error;
    }
    $stmt->close();
}

header("Location: categories.php");
exit;
