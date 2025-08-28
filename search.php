<?php
include 'includes/config.php';

$q = trim($_GET['q'] ?? '');
$products = [];

if ($q !== '') {
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, title, price, image FROM products WHERE title LIKE ? OR description LIKE ? ORDER BY created_at DESC");
    $searchTerm = "%$q%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
}

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4">Search Results for "<?php echo htmlspecialchars($q); ?>"</h2>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">No courses found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $row): 
                $productId = $row['id'];
                $title = htmlspecialchars($row['title']);
                $price = number_format($row['price'],2);
                $rawImage = trim($row['image'] ?? '');
                $imageSrc = $rawImage === '' ? 'assets/img/default.svg' : ((strpos($rawImage, '/') !== false) ? $rawImage : 'images/'.rawurlencode($rawImage));
                $alreadyInCart = isset($_SESSION['cart'][$productId]);
                $alreadyInWishlist = isset($_SESSION['wishlist'][$productId]);
            ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo htmlspecialchars($imageSrc); ?>" class="card-img-top" alt="<?php echo $title; ?>" onerror="this.src='assets/img/default.svg'">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $title; ?></h5>
                        <p class="card-text">Rs. <?php echo $price; ?></p>
                        <div class="mt-auto d-flex justify-content-between">
                            <a href="product.php?id=<?php echo $productId; ?>" class="btn btn-sm btn-outline-primary">View</a>

                            <form method="post" action="api/add_to_cart.php" style="display:inline-block">
                                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                <input type="hidden" name="qty" value="1">
                                <button class="btn btn-sm btn-primary"
                                        type="submit"
                                        <?php if($alreadyInCart) echo 'disabled title="Already in cart"'; ?>>
                                    <?php echo $alreadyInCart ? 'In Cart' : 'Add to Cart'; ?>
                                </button>
                            </form>

                            <button class="btn btn-sm wishlist-btn <?php echo $alreadyInWishlist ? 'btn-danger' : 'btn-outline-warning'; ?>"
                                    data-product-id="<?php echo $productId; ?>"
                                    title="<?php echo $alreadyInWishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                                <i class="fa fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function(){
    $('.wishlist-btn').click(function(e){
        e.preventDefault();
        let btn = $(this);
        let productId = btn.data('product-id');

        $.post('api/toggle_wishlist.php', {product_id: productId}, function(resp){
            alert(resp.message);
            if(resp.status === 'added'){
                btn.removeClass('btn-outline-warning').addClass('btn-danger');
                btn.attr('title','Remove from wishlist');
            } else if(resp.status === 'removed'){
                btn.removeClass('btn-danger').addClass('btn-outline-warning');
                btn.attr('title','Add to wishlist');
            }
            // Update wishlist counter in navbar
            $('.fa-heart').parent().find('.badge').text(resp.count);
        }, 'json');
    });
});
</script>

<?php include 'includes/footer.php'; ?>
