<?php
session_start();
require_once 'includes/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch all orders with product info
$sql = "SELECT o.id AS order_id, o.total_amount, o.payment_status, o.created_at AS enrolled_at,
               p.title, p.price, p.images
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <h2 class="mb-5 text-center fw-bold">🎓 My Courses</h2>

    <?php if(empty($courses)): ?>
        <p class="text-center text-muted fs-5">You have not enrolled in any courses yet.</p>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($courses as $course):
                $images = json_decode($course['images'], true);
                $imageSrc = (!empty($images[0]) && file_exists($images[0])) ? $images[0] : 'assets/img/default_course.svg';
                $enrollDate = date("d M Y", strtotime($course['enrolled_at']));
                $oldPrice = $course['price'] * 1.10; // 10% above original price
                $progress = $course['payment_status'] === 'PAID' ? rand(20, 100) : 0;
                
                // Badge color based on payment status
                switch($course['payment_status']){
                    case 'PAID': $badge = ['text'=>'Paid','class'=>'bg-success']; break;
                    case 'PENDING': $badge = ['text'=>'Pending','class'=>'bg-warning text-dark']; break;
                    default: $badge = ['text'=>'Failed','class'=>'bg-danger']; break;
                }
            ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 course-card">
                    <div class="position-relative">
                        <img src="<?= $imageSrc ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                        <span class="badge <?= $badge['class']; ?> position-absolute top-0 start-0 m-2"><?= $badge['text']; ?></span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($course['title']); ?></h5>

                        <p class="mb-1">
                            <span class="text-muted text-decoration-line-through">Rs. <?= number_format($oldPrice,2); ?></span>
                            <span class="text-success fw-bold ms-2">Rs. <?= number_format($course['price'],2); ?></span>
                            <span class="badge bg-danger ms-2">10% OFF</span>
                        </p>

                        <small class="text-secondary mb-2">Enrolled on: <?= $enrollDate; ?></small>

                        <div class="progress mb-3" style="height:10px; border-radius:5px; overflow:hidden;">
                            <div class="progress-bar bg-success progress-animate" role="progressbar" style="width:0%;" data-width="<?= $progress; ?>%"></div>
                        </div>

                        <?php if($course['payment_status'] === 'PAID'): ?>
                            <a href="order_details.php?order_id=<?= $course['order_id']; ?>" class="btn btn-primary mt-auto">View Course</a>
                        <?php else: ?>
                            <button class="btn btn-secondary mt-auto" disabled>Access Pending</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.course-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
}
.progress-animate {
    transition: width 1.5s ease-in-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bars = document.querySelectorAll('.progress-animate');
    bars.forEach(bar => {
        setTimeout(() => {
            bar.style.width = bar.getAttribute('data-width');
        }, 300);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
