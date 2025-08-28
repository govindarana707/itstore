<?php
session_start();
$cartCount = count($_SESSION['cart'] ?? []);
$wishlistCount = count($_SESSION['wishlist'] ?? []);
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">IT Store</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item position-relative">
          <a class="nav-link" href="cart.php">
            <i class="fa fa-shopping-cart"></i> Cart
            <span id="cart-count" class="badge bg-danger position-absolute top-0 start-100 translate-middle p-1 rounded-circle">
              <?= $cartCount ?>
            </span>
          </a>
        </li>
        <li class="nav-item position-relative">
          <a class="nav-link" href="wishlist.php">
            <i class="fa fa-heart"></i> Wishlist
            <span id="wishlist-count" class="badge bg-warning text-dark position-absolute top-0 start-100 translate-middle p-1 rounded-circle">
              <?= $wishlistCount ?>
            </span>
          </a>
        </li>
        <li class="nav-item dropdown">
          <?php if(isset($_SESSION['user_id'])): ?>
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
            <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
          <?php else: ?>
          <a class="nav-link" href="login.php">Login</a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script>
$(document).ready(function(){
    window.updateCartCount = function(count){ $('#cart-count').text(count); };
    window.updateWishlistCount = function(count){ $('#wishlist-count').text(count); };
});
</script>
