<?php
session_start();
include_once '../config/database.php';
include_once '../classes/Category.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$category = new Category($db);
$category->id = $_GET['id'];
$category_data = $category->readOne();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category->name = $_POST['name'];
    $category->description = $_POST['description'];

    if ($category->update()) {
        $_SESSION['success'] = "Category updated successfully";
        header("Location: categories.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update category";
    }
}

include_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Category</h1>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $category->id); ?>" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="<?php echo $category_data['name']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description (Optional)</label>
                    <textarea class="form-control" id="description" name="description"
                        rows="3"><?php echo $category_data['description']; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Category</button>
                <a href="categories.php" class="btn btn-secondary">Cancel</a>
            </form>
        </main>
    </div>
</div>

<?php include_once '../includes/admin_footer.php'; ?>