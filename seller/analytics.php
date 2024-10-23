<?php
session_start();
include_once '../config/database.php';
include_once '../classes/Order.php';
include_once '../classes/Product.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$order = new Order($db);
$product = new Product($db);
$seller_id = $_SESSION['user_id'];

// Get analytics data
$total_orders = $order->getTotalOrdersBySeller($seller_id);
$total_revenue = $order->getTotalRevenueBySeller($seller_id);
$monthly_sales = $order->getMonthlySalesBySeller($seller_id);

include_once '../includes/seller_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/seller_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Analytics</h1>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Orders</h5>
                            <p class="card-text display-4"><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Revenue</h5>
                            <p class="card-text display-4">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Monthly Sales</h3>
            <canvas id="monthlySalesChart"></canvas>
        </main>
    </div>
</div>

<script>
    const ctx = document.getElementById('monthlySalesChart').getContext('2d');
    const monthlySalesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($monthly_sales)); ?>,
            datasets: [{
                label: 'Sales',
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
</script>

<?php include_once '../includes/seller_footer.php'; ?>