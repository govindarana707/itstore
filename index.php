<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4 text-center fw-bold">Available Courses</h2>

    <!-- Courses Container -->
    <div class="row g-4" id="coursesContainer"></div>

    <!-- Loading Spinner -->
    <div id="loading" class="text-center my-5" style="display:none;">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const container = document.getElementById('coursesContainer');
    const loading = document.getElementById('loading');

    async function fetchCourses() {
        loading.style.display = 'block';
        container.innerHTML = '';

        try {
            const res = await fetch('fetch_courses.php');
            const data = await res.json();
            loading.style.display = 'none';

            if(data.length === 0){
                container.innerHTML = '<p class="text-center text-muted">No courses found.</p>';
                return;
            }

            data.forEach(course => {
                const img = course.image_exists ? course.images[0] : 'assets/img/default.svg';
                const discountPrice = course.price;
                const originalPrice = (course.price * 1.10).toFixed(2);
                const inCart = course.inCart ?? false;

                const card = document.createElement('div');
                card.className = 'col-12 col-md-6 col-lg-4';
                card.innerHTML = `
                    <div class="card h-100 shadow-sm course-card position-relative transition">
                        ${course.isNew ? '<span class="badge bg-success position-absolute top-0 start-0 m-2">New</span>' : ''}
                        ${course.isPopular ? '<span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">Popular</span>' : ''}
                        <img src="${img}" class="card-img-top" style="height:200px; object-fit:cover;" loading="lazy">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold">${course.title}</h5>
                            <p class="card-text text-muted">${course.description.substring(0,80)}...</p>

                            <div class="d-flex align-items-center gap-2 mb-2">
                                <small class="text-muted text-decoration-line-through">Rs. ${Number(originalPrice).toLocaleString()}</small>
                                <span class="text-success fw-bold">Rs. ${Number(discountPrice).toLocaleString()}</span>
                            </div>

                            <span class="badge bg-danger mb-2">-10% OFF</span>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <a href="product.php?id=${course.id}" class="btn btn-sm btn-outline-primary">Details</a>
                                ${inCart ? 
                                    `<a href="payments/cart.php" class="btn btn-sm btn-warning">Go to Cart</a>` : 
                                    `<form method="post" action="api/add_to_cart.php" class="m-0">
                                        <input type="hidden" name="product_id" value="${course.id}">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" class="btn btn-sm btn-success">Enroll Now</button>
                                    </form>`
                                }
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });

        } catch(err) {
            loading.style.display = 'none';
            container.innerHTML = '<p class="text-center text-danger">Error loading courses.</p>';
            console.error(err);
        }
    }

    fetchCourses();
});
</script>

<style>
.transition { transition: all 0.3s ease; }
.transition:hover { transform: translateY(-7px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
.course-card .badge { font-size: 0.8rem; }
</style>
