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

$orders = $order->getUserOrders($seller_id, $page, $records_per_page); // Pass pagination parameters
$total_pages = $order->getTotalPagesForSeller($seller_id, $records_per_page); // 

// Handle status update with AJAX
if (isset($_POST['complete_order'])) {
    $order_id = $_POST['order_id'];
    $new_status = 'completed';
    if ($order->updateOrderStatus($order_id, $new_status)) {
        echo json_encode(['status' => 'success', 'message' => 'Order status updated!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
    }
    exit; // Important: Stop further execution after AJAX response
}

include_once '../includes/seller_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/seller_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Orders</h1>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Buyer</th>
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
                                <td><?php echo $row['buyer_name']; ?></td> <!-- Display buyer_name -->
                                <td>Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeClass($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <?php if ($row['status'] == 'cancelled') { ?>
                                            <button type="button" class="btn btn-sm btn-success disabled" disabled data-bs-toggle="modal" data-bs-target="#confirmCompleteOrderModal" data-order-id="<?php echo $row['id']; ?>">
                                                Mark as Completed
                                            </button>
                                        <?php } else { ?>
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#confirmCompleteOrderModal" data-order-id="<?php echo $row['id']; ?>">
                                                Mark as Completed
                                            </button>
                                        <?php } ?>
                                    </form>
                                </td>
                            </tr>
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

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmCompleteOrderModal" tabindex="-1" aria-labelledby="confirmCompleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="orders.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmCompleteOrderModalLabel">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menandai pesanan ini sebagai completed?
                    <input type="hidden" name="order_id" id="modal_order_id" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="complete_order" class="btn btn-success">Ya, Tandai sebagai Completed</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include_once '../includes/seller_footer.php'; ?>

<script>
    var confirmCompleteOrderModal = document.getElementById('confirmCompleteOrderModal');
    confirmCompleteOrderModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var orderId = button.getAttribute('data-order-id');
        var modalOrderIdInput = confirmCompleteOrderModal.querySelector('#modal_order_id');
        modalOrderIdInput.value = orderId;
    });
</script>