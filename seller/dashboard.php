<?php
session_start();
include_once '../config/database.php';
include_once '../classes/Product.php';
include_once '../classes/Order.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$order = new Order($db);

// Get seller statistics
$seller_id = $_SESSION['user_id'];
$total_products = $product->getSellerTotalProducts($seller_id);
$total_orders = $order->getSellerTotalOrders($seller_id);
$total_revenue = $order->getSellerTotalRevenue($seller_id);
$recent_orders = $order->getSellerRecentOrders($seller_id);

function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

include_once '../includes/seller_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/seller_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Seller Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportReports()">Export Reports</button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Products</h5>
                            <p class="card-text display-4"><?php echo $total_products; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Orders</h5>
                            <p class="card-text display-4"><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Revenue</h5>
                            <p class="card-text display-4">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="card mb-4">
                <div class="card-header">
                    Sales Analytics
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    Recent Orders
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Products</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $recent_orders->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo $order['customer_name']; ?></td>
                                        <td><?php echo $order['products']; ?></td>
                                        <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo getStatusBadgeClass($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Chart Implementation
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($order->getSellerSalesLabels($seller_id)); ?>,
            datasets: [{
                label: 'Sales',
                data: <?php echo json_encode($order->getSellerSalesData($seller_id)); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function exportReports() {
        window.location.href = 'export_reports.php';
    }
</script>

<?php include_once '../includes/seller_footer.php'; ?>