<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - IT Store</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-OHflHx0K7HeF6U2YxZ1YglgZwHHh5/YPqC3rMHr1x/8z58yML3+kO+tbqHD9T8s3J+gQFX3Lv3v/rXw1YbB1qw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom admin CSS -->
<style>
body { background-color:#f4f6f8; font-family: 'Segoe UI', sans-serif; }
.table .btn { margin-right:5px; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="../index.php">IT Store Admin</a>
  </div>
</nav>
<div class="container my-4">
