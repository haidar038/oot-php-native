<?php
session_start();
include_once '../config/database.php';
include_once '../classes/Product.php';
include_once '../classes/Category.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$category = new Category($db);

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product->id = $_GET['id'];
$product_data = $product->readOne();
$categories = $category->read();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product->seller_id = $_POST['seller_id'];
    $product->category_id = $_POST['category_id'];
    $product->name = $_POST['name'];
    $product->description = $_POST['description'];
    $product->price = $_POST['price'];
    $product->stock = $_POST['stock'];

    // Handle image upload if new image is selected
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/products/";
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $product->image = 'uploads/products/' . $file_name;
        } else {
            $_SESSION['error'] = "Failed to upload image";
            header("Location: edit_product.php?id=" . $product->id);
            exit();
        }
    }

    if ($product->update()) {
        $_SESSION['success'] = "Product updated successfully";
        header("Location: products.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update product";
    }
}

include_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Product</h1>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $product->id); ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="seller_id" class="form-label">Seller ID</label>
                    <input type="number" class="form-control" id="seller_id" name="seller_id" value="<?php echo $product_data['seller_id']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <?php while ($row = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $product_data['category_id'] ? 'selected' : ''; ?>>
                                <?php echo $row['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $product_data['name']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $product_data['description']; ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?php echo $product_data['price']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $product_data['stock']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <small class="text-muted">Leave empty to keep current image</small>
                    <?php if ($product_data['image']): ?>
                        <div class="mt-2">
                            <img src="../<?php echo $product_data['image']; ?>" alt="Current product image" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="products.php" class="btn btn-secondary">Cancel</a>
            </form>
        </main>
    </div>
</div>

<?php include_once '../includes/admin_footer.php'; ?>