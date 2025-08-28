<?php
session_start();
include '../../includes/config.php';
include '../include/admin_header.php'; 

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body { background-color: #e3f2fd; }
        .card { border-radius: 12px; border:none; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
        .btn-primary { background-color:#0d6efd; border:none; }
        .btn-primary:hover { background-color:#0b5ed7; }
        .btn-success { background-color:#198754; border:none; }
        .btn-success:hover { background-color:#157347; }
        .btn-danger { background-color:#dc3545; border:none; }
        .btn-danger:hover { background-color:#bb2d3b; }
        table thead { background-color:#0d6efd; color:#fff; }
        table tbody tr:hover { background-color:#cfe2ff; }
        img.thumbnail { width:50px; height:50px; object-fit:cover; border-radius:4px; margin-right:5px; }
        .modal-lg { max-width: 900px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card p-4">
        <h2 class="mb-4 text-primary">Product Management</h2>
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add New Product</button>
        <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search Products...">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Images</th>
                        <th>Rating Avg</th>
                        <th>Rating Count</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productBody"></tbody>
            </table>
        </div>
        <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h5 class="modal-title text-primary" id="modalTitle">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="product_id">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label>Price</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Category</label>
                <select name="category_id" id="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Images</label>
                <input type="file" name="images[]" id="images" multiple class="form-control">
                <div id="imagePreview" class="mt-2 d-flex flex-wrap"></div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="saveBtn">Save Product</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

<script>
// Load products
function loadProducts(query=''){
    $.ajax({
        url:'ajax/products_list_full.php',
        type:'GET',
        data:{search:query},
        success:function(data){
            $('#productBody').html(data);
        }
    });
}

$(document).ready(function(){
    loadProducts();

    $('#searchInput').on('input', function(){
        loadProducts($(this).val());
    });

    // Image preview
    $('#images').on('change', function(){
        $('#imagePreview').html('');
        for(let i=0;i<this.files.length;i++){
            let reader = new FileReader();
            reader.onload = function(e){
                $('#imagePreview').append('<img src="'+e.target.result+'" class="thumbnail">');
            }
            reader.readAsDataURL(this.files[i]);
        }
    });

    // Submit Add/Edit form
    $('#productForm').on('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        $('#saveBtn').text('Saving...');
        $.ajax({
            url:'ajax/product_save.php',
            type:'POST',
            data: formData,
            contentType:false,
            processData:false,
            success:function(resp){
                $('#saveBtn').text('Save Product');
                $('#productForm')[0].reset();
                $('#imagePreview').html('');
                $('#addModal').modal('hide');
                loadProducts();
                Swal.fire('Success', resp, 'success');
            },
            error:function(){
                $('#saveBtn').text('Save Product');
                Swal.fire('Error','Something went wrong','error');
            }
        });
    });

    // Edit product click
    $(document).on('click','.editBtn', function(){
        let id = $(this).data('id');
        $.ajax({
            url:'ajax/product_fetch.php',
            type:'GET',
            data:{id:id},
            dataType:'json',
            success:function(data){
                $('#modalTitle').text('Edit Product');
                $('#product_id').val(data.id);
                $('#title').val(data.title);
                $('#description').val(data.description);
                $('#price').val(data.price);
                $('#category_id').val(data.category_id);

                $('#imagePreview').html('');
                if(data.images){
                    data.images.forEach(img=>{
                        let filename = img.split('/').pop();
                        $('#imagePreview').append('<img src="../uploads/'+filename+'" class="thumbnail">');
                    });
                }

                $('#addModal').modal('show');
            }
        });
    });

    // Delete product
    $(document).on('click','.deleteBtn', function(){
        let id = $(this).data('id');
        Swal.fire({
            title:'Are you sure?',
            text:"You won't be able to revert this!",
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#3085d6',
            cancelButtonColor:'#d33',
            confirmButtonText:'Yes, delete it!'
        }).then((result)=>{
            if(result.isConfirmed){
                $.ajax({
                    url:'ajax/product_delete.php',
                    type:'POST',
                    data:{id:id},
                    success:function(resp){
                        loadProducts();
                        Swal.fire('Deleted!', resp,'success');
                    }
                });
            }
        });
    });
});
</script>
</body>
</html>
