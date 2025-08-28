<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch summary stats
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;
$total_categories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'] ?? 0;

// Fetch recent orders
$recent_orders = $conn->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");

// Fetch recent products
$recent_products = $conn->query("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
    LIMIT 5
");

// Fetch recent users
$recent_users = $conn->query("
    SELECT id, name, email, created_at
    FROM users
    ORDER BY id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; }
.header { display:flex; justify-content:space-between; align-items:center; padding:15px 30px; background:#fff; border-bottom:1px solid #ddd; }
.stats { display:flex; flex-wrap:wrap; gap:20px; margin:20px 0; }
.card { flex:1; min-width:180px; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:0.3s; cursor:pointer; }
.card:hover { transform:translateY(-5px); box-shadow:0 6px 18px rgba(0,0,0,0.15); }
.card h2 { font-size:18px; margin-bottom:10px; }
.card p { font-size:24px; font-weight:bold; }
.card a.button { text-decoration:none; color:white; padding:5px 12px; border-radius:6px; background:#0d6efd; display:inline-block; margin-top:8px; }
.table thead th { background:#0d6efd; color:white; }
.table-hover tbody tr:hover { background:#e9f5ff; }
.tab-content { margin-top:20px; }
</style>
</head>
<body>

<div class="header">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></h1>
    <a class="btn btn-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="container">

    <!-- Summary Stats -->
    <div class="stats">
        <div class="card bg-primary text-white">
            <h2><i class="bi bi-journal-bookmark-fill"></i> Courses</h2>
            <p><?= $total_products ?></p>
            <a class="button" href="product/products.php">Manage</a>
        </div>
        <div class="card bg-success text-white">
            <h2><i class="bi bi-basket-fill"></i> Orders</h2>
            <p><?= $total_orders ?></p>
            <a class="button" href="order/orders.php">Manage</a>
        </div>
        <div class="card bg-warning text-dark">
            <h2><i class="bi bi-people-fill"></i> Users</h2>
            <p><?= $total_users ?></p>
            <a class="button" href="user/user.php">Manage</a>
        </div>
        <div class="card bg-info text-white">
            <h2><i class="bi bi-tags-fill"></i> Categories</h2>
            <p><?= $total_categories ?></p>
            <a class="button" href="category/categories.php">Manage</a>
        </div>
    </div>

    <!-- Tabs for better GUI -->
    <ul class="nav nav-tabs" id="dashboardTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">Recent Orders</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">Recent Courses</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Recent Users</button>
      </li>
    </ul>

    <div class="tab-content" id="dashboardTabContent">
      <!-- Orders Table -->
      <div class="tab-pane fade show active" id="orders" role="tabpanel">
        <table class="table table-striped table-bordered table-hover mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
            <?php if($recent_orders): ?>
                <?php while($o = $recent_orders->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($o['id']) ?></td>
                    <td><?= htmlspecialchars($o['order_id']) ?></td>
                    <td><?= htmlspecialchars($o['user_name'] ?? 'Guest User') ?></td>
                    <td><?= htmlspecialchars($o['total_amount']) ?></td>
                    <td><?= htmlspecialchars($o['payment_method']) ?></td>
                    <td><?= htmlspecialchars($o['payment_status']) ?></td>
                    <td><?= htmlspecialchars($o['created_at']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No orders found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
      </div>

      <!-- Courses Table -->
      <div class="tab-pane fade" id="courses" role="tabpanel">
        <table class="table table-striped table-bordered table-hover mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
            <?php if($recent_products): ?>
                <?php while($p = $recent_products->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= htmlspecialchars($p['category_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($p['price']) ?></td>
                    <td><?= htmlspecialchars($p['created_at']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No courses found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
      </div>

      <!-- Users Table -->
      <div class="tab-pane fade" id="users" role="tabpanel">
        <table class="table table-striped table-bordered table-hover mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
            <?php if($recent_users): ?>
                <?php while($u = $recent_users->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['created_at']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No users found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
      </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
