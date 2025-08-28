<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) die("Invalid user ID.");

// Fetch user data
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
if (!$user) die("User not found.");

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $updateStmt = mysqli_prepare($conn, "UPDATE users SET name=?, phone=?, email=?, address=? WHERE id=?");
    mysqli_stmt_bind_param($updateStmt, "ssssi", $name, $phone, $email, $address, $user_id);
    if (mysqli_stmt_execute($updateStmt)) {
        $_SESSION['flash_success'] = "User updated successfully!";
        header("Location: edit_user.php?id=$user_id");
        exit;
    } else {
        $flashError = "Error updating user: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<style>
    [contenteditable]:focus { outline: 2px solid #007BFF; }
</style>
</head>
<body>
<div class="container mt-5">
    <h2>Edit User</h2>

    <!-- Flash Messages -->
    <?php if($flashSuccess): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if($flashError): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <form method="post" id="editUserForm">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" id="address" class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Update User</button>
        <a href="user.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
$(document).ready(function(){
    // Inline editing auto-save (optional advanced)
    $('#editUserForm input, #editUserForm textarea').on('blur', function(){
        let field = $(this).attr('name');
        let value = $(this).val().trim();
        let userId = <?= $user_id ?>;

        $.post('ajax_update_user.php', {id:userId, field:field, value:value}, function(resp){
            if(resp.status === 'error'){
                alert('Update failed: '+resp.message);
            }
        }, 'json');
    });
});
</script>
</body>
</html>
