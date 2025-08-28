<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch wishlist courses for this user
$sql = "SELECT p.id, p.title, p.price, p.description, p.images 
        FROM wishlist w
        JOIN products p ON w.course_id = p.id
        WHERE w.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$courses = $res->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-5">
    <h2 class="mb-4 text-center">My Wishlist</h2>

    <div class="row g-4" id="wishlistContainer">
        <?php if (count($courses) === 0): ?>
            <p class="text-center text-muted">Your wishlist is empty.</p>
        <?php else: ?>
            <?php foreach ($courses as $course): 
                $images = json_decode($course['images'], true);
                $img = (!empty($images) && file_exists($images[0])) ? $images[0] : 'assets/img/default.svg';
            ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm course-card" style="transition: transform 0.3s;">
                        <img src="<?= $img ?>" class="card-img-top" style="height:200px; object-fit:cover;" loading="lazy">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                            <p class="card-text text-muted"><?= substr($course['description'], 0, 80) ?>...</p>
                            <p class="h6 text-success fw-bold">Rs. <?= number_format($course['price']) ?></p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <a href="product.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-primary">View Course</a>
                                <button class="btn btn-sm btn-outline-danger remove-wishlist" data-id="<?= $course['id'] ?>">
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
.remove-wishlist i {
    transition: transform 0.3s ease, color 0.3s ease;
    color: #dc3545; /* Red for filled heart */
}
.remove-pop {
    transform: scale(1.4);
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // Remove wishlist item
    document.addEventListener("click", function(e) {
        if(e.target.closest('.remove-wishlist')) {
            const btn = e.target.closest('.remove-wishlist');
            const icon = btn.querySelector('i');
            const courseId = btn.dataset.id;
            const card = btn.closest('.col-12');

            // Animate heart
            icon.classList.add('remove-pop');
            setTimeout(() => icon.classList.remove('remove-pop'), 300);

            // Send AJAX request to remove
            fetch('wishlist.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({course_id: courseId, remove: true})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    // Remove card from DOM
                    card.remove();
                    if(document.querySelectorAll('#wishlistContainer .col-12').length === 0){
                        document.getElementById('wishlistContainer').innerHTML = '<p class="text-center text-muted">Your wishlist is empty.</p>';
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("❌ Error removing from wishlist.");
            });
        }
    });

});
</script>
