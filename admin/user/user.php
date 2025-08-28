<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Search and Pagination
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Count total users
$countQuery = "SELECT COUNT(*) as total FROM users WHERE 1=1";
$paramsCount = [];
if(!empty($search)){
    $countQuery .= " AND (name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $paramsCount[] = $search_param;
    $paramsCount[] = $search_param;
}
$stmtCount = mysqli_prepare($conn,$countQuery);
if(!empty($paramsCount)){
    $types = str_repeat('s', count($paramsCount));
    mysqli_stmt_bind_param($stmtCount, $types, ...$paramsCount);
}
mysqli_stmt_execute($stmtCount);
$resultCount = mysqli_stmt_get_result($stmtCount);
$totalUsers = mysqli_fetch_assoc($resultCount)['total'];
$totalPages = ceil($totalUsers / $limit);

// Fetch users
$query = "SELECT * FROM users WHERE 1=1";
$params = [];
if(!empty($search)){
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = $search_param;
    $params[] = $search_param;
}
$query .= " ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;

$stmt = mysqli_prepare($conn, $query);
if(!empty($params)){
    $types = str_repeat('s', count($params)-2) . 'ii';
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        [contenteditable]:focus { outline: 2px solid #007BFF; }
        mark { background-color: yellow; color: black; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">User List</h2>

    <!-- Flash Messages -->
    <?php if($flashSuccess): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if($flashError): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <!-- Search Form -->
    <form class="row g-3 mb-4" method="get">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by Name or Email" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="user.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- Bulk Actions Form -->
    <form method="post" action="bulk_delete_users.php" id="bulkForm">
        <button type="submit" class="btn btn-danger mb-3" onclick="return confirm('Are you sure you want to delete selected users?')">Delete Selected</button>

        <!-- User Table -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><input type="checkbox" name="user_ids[]" value="<?= $row['id'] ?>"></td>
                        <td><?= $row['id'] ?></td>
                        <td contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="name"><?= htmlspecialchars($row['name']) ?></td>
                        <td contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="phone"><?= htmlspecialchars($row['phone']) ?></td>
                        <td contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="email"><?= htmlspecialchars($row['email']) ?></td>
                        <td contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="address"><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <button type="button" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No users found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for($i=1; $i<=$totalPages; $i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="user.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script>
function confirmDelete(userId){
    if(confirm('Are you sure to delete this user?')){
        window.location.href='delete_user.php?id='+userId;
    }
}

// Select/Deselect All
$('#selectAll').click(function(){
    $('input[name="user_ids[]"]').prop('checked', this.checked);
});

// Inline Editing via AJAX
$('.editable').on('blur', function(){
    let id = $(this).data('id');
    let field = $(this).data('field');
    let value = $(this).text().trim();

    $.post('ajax_update_user.php', {id:id, field:field, value:value}, function(resp){
        if(resp.status === 'error'){
            alert('Update failed: '+resp.message);
        }
    }, 'json');
});

// Highlight search term
<?php if(!empty($search)): ?>
let term = "<?= addslashes($search) ?>";
$('td.editable').each(function(){
    let regex = new RegExp('('+term+')','gi');
    $(this).html($(this).text().replace(regex,'<mark>$1</mark>'));
});
<?php endif; ?>
</script>
</body>
</html>
