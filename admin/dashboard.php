<?php
session_start();
include_once '../config/database.php';
include_once '../classes/User.php';
include_once '../classes/Product.php';
include_once '../classes/Order.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$product = new Product($db);
$order = new Order($db);

// Get statistics
$total_users = $user->getTotalUsers();
$total_products = $product->getTotalProducts();
$total_orders = $order->getTotalOrders();
$total_revenue = $order->getTotalRevenue();

// Get recent activities
$recent_orders = $order->getRecentOrders();
$recent_users = $user->getRecentUsers();

// Get analytics data
$monthly_sales = $order->getMonthlySales();
$category_sales = $order->getCategorySales();

function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'completed':
            return 'success';
        case 'processing':
            return 'warning';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

include_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportReport()">Export Report</button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <p class="card-text display-4"><?php echo $total_users; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Products</h5>
                            <p class="card-text display-4"><?php echo $total_products; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Orders</h5>
                            <p class="card-text display-4"><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Total Revenue</h5>
                            <p class="card-text display-4">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            Monthly Sales
                        </div>
                        <div class="card-body">
                            <canvas id="monthlySalesChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Sales by Category
                        </div>
                        <div class="card-body">
                            <canvas id="categorySalesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-md-6">
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
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = $recent_orders->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo $order['customer_name']; ?></td>
                                                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusBadgeClass($order['status']); ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Recent Users
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = $recent_users->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr>
                                                <td><?php echo $user['username']; ?></td>
                                                <td><?php echo $user['email']; ?></td>
                                                <td><?php echo ucfirst($user['role']); ?></td>
                                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Monthly Sales Chart
    const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
    const monthlySalesChart = new Chart(monthlySalesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($monthly_sales)); ?>,
            datasets: [{
                label: 'Monthly Sales',
                data: <?php echo json_encode(array_values($monthly_sales)); ?>,
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

    // Category Sales Chart
    const categorySalesCtx = document.getElementById('categorySalesChart').getContext('2d');
    const categorySalesChart = new Chart(categorySalesCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($category_sales)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($category_sales)); ?>,
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    function exportReport() {
        window.location.href = 'export_report.php';
    }
</script>

<?php include_once '../includes/admin_footer.php'; ?>