<?php
session_start();
include 'includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];
$course_id = intval($data['course_id'] ?? 0);
$remove = $data['remove'] ?? false;

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course.']);
    exit;
}

if ($remove) {
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id=? AND course_id=?");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'Removed from wishlist.']);
} else {
    // Check if exists
    $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id=? AND course_id=?");
    $check->bind_param("ii", $user_id, $course_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        // Remove if already exists (toggle)
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id=? AND course_id=?");
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist.']);
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Added to wishlist.']);
    }
}
