
<?php include 'header.php';
$products = mysqli_query($conn,"SELECT p.*, c.name as cat FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='active' ORDER BY p.id DESC");
?>
<h3 class="mb-3">Products</h3>
<div class="row g-3">
<?php while($p = mysqli_fetch_assoc($products)): ?>
  <div class="col-sm-6 col-md-4 col-lg-3">
    <div class="card h-100">
      <img src="<?= $p['image'] ? '../assets/images/'.$p['image'] : 'https://via.placeholder.com/400x300' ?>" class="card-img-top" alt="">
      <div class="card-body d-flex flex-column">
        <h6 class="card-title mb-1"><?= htmlspecialchars($p['name']) ?></h6>
        <small class="text-muted mb-2"><?= htmlspecialchars($p['cat']) ?></small>
        <p class="fw-bold mb-2">Rs. <?= number_format($p['price'],2) ?></p>
        <a class="btn btn-sm btn-outline-primary mt-auto" href="product.php?slug=<?= urlencode($p['slug']) ?>">View</a>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>
<?php include 'footer.php'; ?>
