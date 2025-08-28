<?php
session_start();
include 'includes/config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit; }

$res = mysqli_query($conn, "SELECT p.*, c.name AS catname 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.id = $id");
$course = $res ? mysqli_fetch_assoc($res) : null;

if (!$course) { echo '<div class="alert alert-danger text-center mt-4">Course not found.</div>'; exit; }

$images = json_decode($course['images'], true);
$imageSrc = (!empty($images[0]) && file_exists($images[0])) ? $images[0] : 'assets/img/default.svg';
$inCart = isset($_SESSION['cart'][$course['id']]);

$catId = intval($course['category_id']);
$relatedRes = mysqli_query($conn, "SELECT id, title, price, images FROM products 
                                   WHERE category_id = $catId AND id != $id 
                                   ORDER BY created_at DESC LIMIT 4");
$relatedCourses = $relatedRes ? mysqli_fetch_all($relatedRes, MYSQLI_ASSOC) : [];

// Price calculation
$originalPrice = $course['price'] * 1.10; // 10% higher
$discountPrice = $course['price'];
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row g-5 align-items-center bg-white rounded shadow-lg p-4">
        <div class="col-lg-5 text-center">
            <img src="<?= $imageSrc ?>" class="img-fluid rounded transition shadow" style="max-height:400px; object-fit:cover;" alt="<?= htmlspecialchars($course['title']); ?>">
        </div>
        <div class="col-lg-7">
            <h2 class="fw-bold mb-3"><?= htmlspecialchars($course['title']); ?></h2>
            <p class="text-muted mb-2"><i class="bi bi-tags"></i> <?= htmlspecialchars($course['catname']); ?></p>
            <p class="lead"><?= nl2br(htmlspecialchars($course['description'])); ?></p>

            <!-- Price Section -->
            <div class="d-flex align-items-center gap-3 mt-4">
                <span class="text-muted text-decoration-line-through fs-5">
                    Rs. <?= number_format($originalPrice, 2); ?>
                </span>
                <span class="text-success fw-bold fs-3">
                    Rs. <?= number_format($discountPrice, 2); ?>
                </span>
                <span class="badge bg-danger px-3 py-2">-10% OFF</span>
            </div>

            <!-- Enroll Button -->
            <form method="post" action="api/add_to_cart.php" class="mt-4">
                <input type="hidden" name="product_id" value="<?= $course['id']; ?>">
                <input type="hidden" name="qty" value="1">
                <?php if($inCart): ?>
                    <a href="payments/cart.php" class="btn btn-warning btn-lg px-4 shadow">
                        <i class="bi bi-cart-check"></i> Go to Cart
                    </a>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary btn-lg px-4 shadow">
                        <i class="bi bi-bag-plus"></i> Enroll Now
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Related Courses -->
    <?php if($relatedCourses): ?>
    <h4 class="mt-5 mb-4 text-center fw-bold">✨ Related Courses ✨</h4>
    <div class="row g-4">
        <?php foreach($relatedCourses as $rel): 
            $relImgs = json_decode($rel['images'], true);
            $relImg = (!empty($relImgs[0]) && file_exists($relImgs[0])) ? $relImgs[0] : 'assets/img/default.svg';

            $relOriginal = $rel['price'] * 1.10;
            $relDiscount = $rel['price'];
        ?>
        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm border-0 transition rounded-3 overflow-hidden">
                <img src="<?= $relImg ?>" class="card-img-top" style="height:180px; object-fit:cover;">
                <div class="card-body d-flex flex-column">
                    <h6 class="fw-bold text-truncate"><?= htmlspecialchars($rel['title']); ?></h6>
                    <div class="d-flex align-items-center gap-2 my-2">
                        <small class="text-muted text-decoration-line-through">Rs. <?= number_format($relOriginal, 0); ?></small>
                        <span class="text-success fw-bold">Rs. <?= number_format($relDiscount, 0); ?></span>
                    </div>
                    <span class="badge bg-danger mb-2">-10% OFF</span>
                    <a href="product.php?id=<?= $rel['id']; ?>" class="btn btn-sm btn-outline-primary mt-auto w-100">
                        <i class="bi bi-eye"></i> View
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.transition { transition: all 0.3s ease; }
.transition:hover { transform: translateY(-8px); box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
.card:hover img { transform: scale(1.05); transition: 0.5s; }
.card-img-top { transition: 0.5s ease; }
</style>
