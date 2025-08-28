<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
include('..\includes\header.php');

// --- Handle Cart Logic (Remove & Clear) ---
if (isset($_GET['remove'])) {
    $removeId = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$removeId])) {
        unset($_SESSION['cart'][$removeId]);
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// Fetch cart from session
$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .cart-card { border-radius: 15px; overflow: hidden; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .cart-card:hover { transform: translateY(-5px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
        .cart-img { width: 100%; height: 150px; object-fit: cover; border-bottom: 1px solid #eee; }
        .summary-box { border-radius: 12px; background: #fff; box-shadow: 0 3px 10px rgba(0,0,0,0.05); }
        .btn-action { border-radius: 30px; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4 fw-bold text-center">🛒 Your Shopping Cart</h2>

    <?php if (empty($cart)): ?>
        <div class="alert alert-info text-center p-4 rounded-3 shadow-sm">
            <h5 class="fw-bold">Your cart is empty</h5>
            <p class="mb-3">Looks like you haven’t added anything yet.</p>
            <a href="../index.php" class="btn btn-primary btn-lg rounded-pill">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="row g-4">
                    <?php foreach ($cart as $pid => $item): 
                        $subtotal = $item['price'] * $item['qty'];
                        $total += $subtotal;
                    ?>
                    <div class="col-md-6">
                        <div class="card cart-card shadow-sm h-100">
                            <img src="../<?= htmlspecialchars($item['image']) ?>" alt="" class="cart-img" />
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($item['title']) ?></h5>
                                <p class="card-text text-muted small mb-2">Qty: <?= (int)$item['qty'] ?></p>
                                <p class="mb-1"><strong>NPR <?= number_format($item['price'], 2) ?></strong> each</p>
                                <p class="text-success fw-bold mb-3">Subtotal: NPR <?= number_format($subtotal, 2) ?></p>
                                <a href="cart.php?remove=<?= $pid ?>" 
                                   class="btn btn-sm btn-outline-danger mt-auto btn-action"
                                   onclick="return confirm('Remove this item?')">Remove</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="p-4 summary-box">
                    <h4 class="fw-bold mb-3">Order Summary</h4>
                    <hr>
                    <p class="d-flex justify-content-between">
                        <span class="fw-semibold">Total Items:</span>
                        <span><?= count($cart) ?></span>
                    </p>
                    <p class="d-flex justify-content-between">
                        <span class="fw-semibold">Total Amount:</span>
                        <span class="fw-bold text-success">NPR <?= number_format($total, 2) ?></span>
                    </p>
                    <hr>
                    <div class="d-grid gap-2 mt-3">
                        <a href="../index.php" class="btn btn-outline-primary btn-action">Continue Shopping</a>
                        <a href="cart.php?clear=1" 
                           class="btn btn-outline-danger btn-action"
                           onclick="return confirm('Clear all items?')">Delete All</a>
                        <a href="checkout.php" class="btn btn-success btn-action">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
