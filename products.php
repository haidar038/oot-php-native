<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Product.php';
include_once 'classes/Category.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$category = new Category($db);

// Get all categories for filter
$categories = $category->read();

// Handle filters
$category_id = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 8;

// Get filtered products
$products = $product->readPaginatedFiltered($page, $records_per_page, $category_id, $search, $sort);
$total_pages = $product->getTotalPagesFiltered($records_per_page, $category_id, $search);

include_once 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form action="products.php" method="get" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search products..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <select name="category" class="form-select" style="width: auto;">
                    <option value="">All Categories</option>
                    <?php while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <select name="sort" class="form-select" style="width: auto;">
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        <?php if ($products->rowCount() > 0): ?>
            <?php while ($product = $products->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <?php if ($product['featured']): ?>
                            <div class="badge bg-primary position-absolute" style="top: 0.5rem; right: 0.5rem">Featured</div>
                        <?php endif; ?>
                        <img src="<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="card-text"><?php echo substr($product['description'], 0, 100); ?>...</p>
                            <p class="card-text"><strong>Price: Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></strong></p>
                            <p class="card-text"><small class="text-muted">Seller: <?php echo $product['seller_name']; ?></small></p>
                            <div class="d-grid">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">No products found.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>