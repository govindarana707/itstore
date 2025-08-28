<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/config.php';


// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Fetch categories
$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Categories</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4">Categories</h2>

    <?php if($flashSuccess): ?>
    <script>Swal.fire({icon:'success',title:'Success',text:'<?= addslashes($flashSuccess) ?>'});</script>
    <?php endif; ?>

    <?php if($flashError): ?>
    <script>Swal.fire({icon:'error',title:'Error',text:'<?= addslashes($flashError) ?>'});</script>
    <?php endif; ?>

    <a href="add_category.php" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Add Category</a>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($categories)): ?>
            <?php foreach($categories as $cat): ?>
            <tr>
                <td><?= $cat['id'] ?></td>
                <td><?= htmlspecialchars($cat['name'] ?? '-') ?></td>
                <td>
                    <a href="edit_category.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <a href="delete_category.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3" class="text-center">No categories found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
