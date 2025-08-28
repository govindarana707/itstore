<?php include 'header_admin.php'; ?>
<h2>Users</h2>
<div class="table-responsive">
<table class="table table-striped">
<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Admin</th><th>Actions</th></tr></thead>
<tbody>
<?php
$res = mysqli_query($conn,"SELECT * FROM users ORDER BY id DESC");
while($u=mysqli_fetch_assoc($res)){
    echo '<tr>
      <td>'.$u['id'].'</td>
      <td>'.htmlspecialchars($u['name']).'</td>
      <td>'.htmlspecialchars($u['email']).'</td>
      <td>'.($u['is_admin']?'Yes':'No').'</td>
      <td>
        <a class="btn btn-sm btn-primary" href="user_edit.php?id='.$u['id'].'">Edit</a>
        <a class="btn btn-sm btn-danger" href="user_delete.php?id='.$u['id'].'" onclick="return confirm(\'Delete this user?\')">Delete</a>
      </td>
    </tr>';
}
?>
</tbody>
</table>
</div>
<?php include 'footer_admin.php'; ?>
