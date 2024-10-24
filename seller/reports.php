<?php
session_start();
include_once '../config/database.php';
include_once '../config/functions.php';
include_once '../classes/Order.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$order = new Order($db);
$seller_id = $_SESSION['user_id'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;

// Get seller's orders
$orders = $order->getUserOrders($seller_id, $page, $records_per_page);

include_once '../includes/seller_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/seller_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Reports</h1>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $orders->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeClass($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php include_once '../includes/seller_footer.php'; ?>