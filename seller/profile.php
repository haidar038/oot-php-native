<?php
session_start();
include_once '../config/database.php';
include_once '../classes/User.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->id = $_SESSION['user_id'];
$user_data = $user->readOne();

$update_success = $update_error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];

    if (!empty($_POST['new_password'])) {
        $user->password = $_POST['new_password'];
    }

    if ($user->update()) {
        $update_success = "Profile updated successfully.";
    } else {
        $update_error = "Failed to update profile.";
    }
}

include_once '../includes/seller_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/seller_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Profile</h1>
            </div>

            <?php if (!empty($update_success)): ?>
                <div class="alert alert-success"><?php echo $update_success; ?></div>
            <?php endif; ?>

            <?php if (!empty($update_error)): ?>
                <div class="alert alert-danger"><?php echo $update_error; ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $user_data['username']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user_data['email']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </main>
    </div>
</div>

<?php include_once '../includes/seller_footer.php'; ?>