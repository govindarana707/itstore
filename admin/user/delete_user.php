<?php
session_start();

// Include database config
require_once __DIR__ . '/../../includes/config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Validate ID
$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) {
    die("Invalid user ID.");
}

// Delete user
$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
if (mysqli_stmt_execute($stmt)) {
    header('Location: user.php?msg=User+deleted+successfully');
    exit;
} else {
    die("Error deleting user: " . mysqli_error($conn));
}
?>
