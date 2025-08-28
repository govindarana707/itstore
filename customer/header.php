
<?php require_once '../includes/config.php'; require_once '../includes/helpers.php'; ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ecom2</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head><body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">Ecom2</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="orders.php">My Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item me-3"><a class="btn btn-outline-light" href="cart.php"><i class="fa fa-shopping-cart"></i> Cart (<?= array_sum(array_column($_SESSION['cart'] ?? [], 'qty')) ?>)</a></li>
        <?php if(is_logged_in()): ?>
          <li class="nav-item"><a class="btn btn-warning" href="../auth/logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-primary" href="../auth/login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container py-4"><?php show_flash(); ?>
