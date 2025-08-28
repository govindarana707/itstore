<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../include/admin_header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if ($name === '') {
        $_SESSION['flash_error'] = "Category name cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = "Category added successfully!";
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
    <h2 class="mb-4">Add New Category</h2>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add Category</button>
        <a href="categories.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../include/admin_footer.php'; ?>
