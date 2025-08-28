<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/config.php';
require_once __DIR__.'/helpers.php';

// Define BASE_URL for universal paths
define('BASE_URL', 'http://localhost/itstore'); // adjust to your domain or localhost path

// Fetch categories
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// User info
$userLoggedIn = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;
$userName = '';
$userEmail = '';

if($userLoggedIn && $userId){
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $userName = $user['name'] ?? '';
    $userEmail = $user['email'] ?? '';
}

// Cart count
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IT Store</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<style>
/* --- Header Styles --- */
.header-topbar { background:#131921; color:#fff; font-size:14px; padding:4px 15px; }
.header-topbar a { color:#fff; text-decoration:none; margin-left:15px; transition:0.2s; }
.header-topbar a:hover { text-decoration:underline; }

.header-navbar { background:#fff; box-shadow:0 3px 6px rgba(0,0,0,0.1); padding:10px 15px; }
.navbar-brand img { height:45px; margin-right:8px; }
.navbar-brand span { font-weight:700; font-size:20px; color:#111; }

.search-bar input { width:100%; border-radius:30px 0 0 30px; border:1px solid #ccc; padding:10px 15px; }
.search-bar button { border-radius:0 30px 30px 0; border:1px solid #ccc; background:#ff9900; color:#fff; padding:10px 20px; }

.category-dropdown .dropdown-menu { width:250px; border-radius:6px; }
.category-dropdown .dropdown-item:hover { background:#f8f9fa; }

.nav-icons a { position:relative; color:#111; margin-left:20px; font-size:18px; transition:0.2s; }
.nav-icons a:hover { color:#ff9900; }
.nav-icons .badge { position:absolute; top:-5px; right:-10px; font-size:12px; }

.dropdown-cart, .dropdown-mycourses {
    width:320px; background:#fff; border:1px solid #ddd; border-radius:8px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1); position:absolute; top:45px; right:0; display:none; z-index:1000;
}
.dropdown-cart.active, .dropdown-mycourses.active { display:block; }
.dropdown-cart .item, .dropdown-mycourses .item { display:flex; justify-content:space-between; padding:10px; border-bottom:1px solid #eee; }
.dropdown-cart .item:last-child, .dropdown-mycourses .item:last-child { border-bottom:none; }
.dropdown-cart .item img, .dropdown-mycourses .item img { width:50px; height:50px; object-fit:cover; border-radius:5px; margin-right:10px; }

@media (max-width:768px){
    .search-bar input { padding:8px; }
    .search-bar button { padding:8px 12px; }
}
</style>
</head>
<body>

<!-- Topbar -->
<div class="header-topbar d-flex justify-content-between align-items-center">
    <div>📞 +977-9800000000 | ✉ info@itstore.com</div>
    <div>
        <?php if($userLoggedIn): ?>
            <a href="<?= BASE_URL ?>/profile.php">Hello, <?= htmlspecialchars($userName) ?></a> |
            <a href="<?= BASE_URL ?>/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php">Login</a> |
            <a href="<?= BASE_URL ?>/register.php">Register</a>
        <?php endif; ?>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg header-navbar sticky-top">
    <div class="container d-flex align-items-center">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo">
            <span>IT Store</span>
        </a>

        <!-- Category Dropdown -->
        <div class="dropdown category-dropdown me-3">
            <button class="btn btn-outline-dark dropdown-toggle" type="button" id="categoryMenu" data-bs-toggle="dropdown">
                Categories
            </button>
            <ul class="dropdown-menu p-2" aria-labelledby="categoryMenu">
                <?php foreach($categories as $cat): ?>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Search Bar -->
        <form class="d-flex flex-grow-1 search-bar" action="<?= BASE_URL ?>/index.php" method="get">
            <input type="search" name="q" class="form-control" placeholder="Search courses, tutorials..." aria-label="Search">
            <button class="btn"><i class="fa fa-search"></i></button>
        </form>

        <!-- User Icons -->
        <div class="d-flex nav-icons align-items-center position-relative">

            <?php if($userLoggedIn): ?>
                <!-- My Courses -->
                <div class="position-relative ms-3">
                    <a href="<?= BASE_URL ?>/my_courses.php" class="text-dark"><i class="fa fa-graduation-cap"></i></a>
                    <div class="dropdown-mycourses">
                        <p class="text-center p-3">Loading your courses...</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Cart -->
            <div class="position-relative ms-3">
                <a href="<?= BASE_URL ?>/payments/cart.php" class="text-dark"><i class="fa fa-shopping-cart"></i></a>
                <?php if($cartCount>0): ?>
                    <span class="badge bg-danger rounded-pill"><?= $cartCount ?></span>
                <?php endif; ?>
                <div class="dropdown-cart">
                    <p class="text-center p-3">Cart preview</p>
                </div>
            </div>

            <!-- Profile -->
            <div class="ms-3">
                <?php if($userLoggedIn): ?>
                    <a href="<?= BASE_URL ?>/profile.php" class="text-dark"><i class="fa fa-user-circle"></i></a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="text-dark"><i class="fa fa-sign-in-alt"></i></a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</nav>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Dynamic dropdowns -->
<script>
$(document).ready(function(){
    $('.nav-icons > div').hover(function(){
        $(this).find('.dropdown-cart, .dropdown-mycourses').addClass('active');
    }, function(){
        $(this).find('.dropdown-cart, .dropdown-mycourses').removeClass('active');
    });

    // Load cart preview dynamically
    $.getJSON('<?= BASE_URL ?>/api/cart_preview.php', function(resp){
        if(resp.status === 'success') $('.dropdown-cart').html(resp.html);
    });

    // Load my courses dynamically
    <?php if($userLoggedIn): ?>
    $.getJSON('<?= BASE_URL ?>/api/my_courses_preview.php', function(resp){
        if(resp.status === 'success') $('.dropdown-mycourses').html(resp.html);
    });
    <?php endif; ?>
});
</script>
</body>
</html>
