<?php
session_start();
include '../../includes/config.php';

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body { background-color: #e3f2fd; }
        .card { border-radius: 12px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn-primary { background-color: #0d6efd; border: none; }
        .btn-primary:hover { background-color: #0b5ed7; }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25); }
        img.thumbnail { width:80px; margin-right:5px; margin-bottom:5px; border-radius:4px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card p-4">
        <h2 class="mb-4 text-primary">Add New Product</h2>
        <form id="addProductForm" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" placeholder="Product Title" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Product Description" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price" class="form-control" placeholder="Product Price" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Images</label>
                <input type="file" name="images[]" id="images" multiple class="form-control" required>
                <div id="imagePreview" class="mt-2 d-flex flex-wrap"></div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="saveBtn">Save Product</button>
            <a href="products.php" class="btn btn-secondary w-100 mt-2">Back</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function(){
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

    // AJAX submit
    $('#addProductForm').on('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        $('#saveBtn').text('Saving...');
        $.ajax({
            url:'ajax/product_save.php', // Same save script as edit
            type:'POST',
            data:formData,
            processData:false,
            contentType:false,
            success:function(resp){
                $('#saveBtn').text('Save Product');
                $('#addProductForm')[0].reset();
                $('#imagePreview').html('');
                Swal.fire('Success', resp, 'success').then(()=>{
                    window.location.href = 'products.php';
                });
            },
            error:function(){
                $('#saveBtn').text('Save Product');
                Swal.fire('Error','Something went wrong','error');
            }
        });
    });
});
</script>
</body>
</html>
