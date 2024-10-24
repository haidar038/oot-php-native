<?php
session_start();
include_once '../config/database.php';
include_once '../config/functions.php';
include_once '../classes/Order.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$order = new Order($db);

// Handle status update with AJAX
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    if ($order->updateOrderStatus($order_id, $new_status)) {
        echo json_encode(['status' => 'success', 'message' => 'Order status updated!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
    }
    exit; // Important: Stop further execution after AJAX response
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;

$orders = $order->readPaginated($page, $records_per_page);
$total_pages = $order->getTotalPages($records_per_page);

include_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Order Management</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                    unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Buyer</th>
                            <th>Seller</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $orders->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo $row['buyer_name']; ?></td>
                                <td><?php echo $row['seller_name']; ?></td>
                                <td>Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeClass($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#orderModal<?php echo $row['id']; ?>">
                                        View Details
                                    </button>
                                </td>
                            </tr>

                            <!-- Order Details Modal -->
                            <div class="modal fade" id="orderModal<?php echo $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Order #<?php echo $row['id']; ?> Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <h6>Order Information</h6>
                                                    <p>Date: <?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></p>
                                                    <p>Total: Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></p>
                                                    <p>Status:
                                                        <span class="badge bg-<?php echo getStatusBadgeClass($row['status']); ?>">
                                                            <?php echo ucfirst($row['status']); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Customer Information</h6>
                                                    <p>Name: <?php echo $row['buyer_name']; ?></p>
                                                    <p>Email: <?php echo $row['buyer_email']; ?></p>
                                                    <p>Phone: <?php echo $row['buyer_phone']; ?></p>
                                                </div>
                                            </div>

                                            <h6>Order Items</h6>
                                            <ul class="list-group list-group-flush">
                                                <?php
                                                $order_items = $order->getOrderItems($row['id']);
                                                while ($item = $order_items->fetch(PDO::FETCH_ASSOC)):
                                                ?>
                                                    <li class="list-group-item">Product: <?php echo $item['product_name']; ?></li>
                                                    <li class="list-group-item">Price: Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></li>
                                                    <li class="list-group-item">Quantity: <?php echo $item['quantity']; ?></li>
                                                    <li class="list-group-item">Subtotal: Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></li>

                                                <?php endwhile; ?>
                                            </ul>

                                            <form action="orders.php" method="post" class="mt-3">
                                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <select name="status" class="form-select">
                                                            <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="processing" <?php echo $row['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                            <option value="completed" <?php echo $row['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                            <option value="cancelled" <?php echo $row['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../includes/admin_footer.php'; ?>