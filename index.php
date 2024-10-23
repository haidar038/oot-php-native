<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$featured_products = $product->getFeatured();
$latest_products = $product->read();

include_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Welcome to Oleh-Oleh Local</h1>
    <p>Discover authentic local products from various regions</p>

    <!-- Featured Products -->
    <section class="mb-5">
        <h2>Featured Products</h2>
        <div class="row">
            <?php while ($row = $featured_products->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="badge bg-primary position-absolute" style="top: 0.5rem; right: 0.5rem">Featured</div>
                        <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['name']; ?></h5>
                            <p class="card-text"><?php echo substr($row['description'], 0, 100); ?>...</p>
                            <p class="card-text"><strong>Price: Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></strong></p>
                            <p class="card-text"><small class="text-muted">Seller: <?php echo $row['seller_name']; ?></small></p>
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Latest Products -->
    <section>
        <h2>Latest Products</h2>
        <div class="row">
            <?php while ($row = $latest_products->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['name']; ?></h5>
                            <p class="card-text"><?php echo substr($row['description'], 0, 100); ?>...</p>
                            <p class="card-text"><strong>Price: Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></strong></p>
                            <p class="card-text"><small class="text-muted">Seller: <?php echo $row['seller_name']; ?></small></p>
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
</div>

<?php include_once 'includes/footer.php'; ?>