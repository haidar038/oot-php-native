<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Product.php';
include_once 'classes/Review.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$review = new Review($db);

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product->id = $_GET['id'];
$product_data = $product->readOne();
$reviews = $review->getProductReviews($product->id);
$avg_rating = $review->getAverageRating($product->id);

include_once 'includes/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item active"><?php echo $product_data['name']; ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo $product_data['image']; ?>" class="img-fluid product-detail-img" alt="<?php echo $product_data['name']; ?>">
        </div>
        <div class="col-md-6">
            <h1><?php echo $product_data['name']; ?></h1>
            <p class="text-muted">Seller: <?php echo $product_data['seller_name']; ?></p>
            <div class="mb-3">
                <span class="h4">Rp <?php echo number_format($product_data['price'], 0, ',', '.'); ?></span>
            </div>
            <p><?php echo $product_data['description']; ?></p>
            <p>Stock: <?php echo $product_data['stock']; ?> items</p>

            <form action="checkout.php" method="get" class="mb-3">
                <input type="hidden" name="product_id" value="<?php echo $product_data['id']; ?>">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product_data['stock']; ?>">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Proceed to Checkout</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3>Reviews</h3>
            <div class="mb-3">
                Average Rating: <?php echo number_format($avg_rating, 1); ?> / 5
            </div>
            <?php while ($review_data = $reviews->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title"><?php echo $review_data['username']; ?></h5>
                            <div class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $review_data['rating']): ?>
                                        ★
                                    <?php else: ?>
                                        ☆
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="card-text"><?php echo $review_data['comment']; ?></p>
                        <small class="text-muted"><?php echo date('d M Y', strtotime($review_data['created_at'])); ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>