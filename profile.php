<?php
session_start();
include_once 'config/database.php';
include_once 'classes/User.php';
include_once 'classes/Order.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$order = new Order($db);

$user->id = $_SESSION['user_id'];
$user_data = $user->readOne();
$orders = $order->getUserOrders($_SESSION['user_id']);

$update_success = $update_error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
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
}

include_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1>My Profile</h1>

    <?php if (!empty($update_success)): ?>
        <div class="alert alert-success"><?php echo $update_success; ?></div>
    <?php endif; ?>

    <?php if (!empty($update_error)): ?>
        <div class="alert alert-danger"><?php echo $update_error; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
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
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order History</h5>
                </div>
                <div class="card-body">
                    <?php if ($orders->rowCount() > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $orders->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $order['status'] == 'completed' ? 'success' : ($order['status'] == 'cancelled' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No orders found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>