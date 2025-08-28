<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../include/admin_header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    $_SESSION['flash_error'] = "Invalid category ID.";
    header("Location: categories.php");
    exit;
}

// Fetch category
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

if (!$category) {
    $_SESSION['flash_error'] = "Category not found.";
    header("Location: categories.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if ($name === '') {
        $_SESSION['flash_error'] = "Category name cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = "Category updated successfully!";
            $stmt->close();
            header("Location: categories.php");
            exit;
        } else {
            $_SESSION['flash_error'] = "Database error: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>

<div class="container py-4">
    <h2 class="mb-4">Edit Category</h2>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Update Category</button>
        <a href="categories.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../include/admin_footer.php'; ?>
