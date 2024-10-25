<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Order.php';
include_once 'classes/Product.php';
include_once 'classes/User.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$order = new Order($db);
$product = new Product($db);
$user = new User($db);

// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $order->updateStatus($_GET['order_id'], 'cancelled');
    header("Location: cart.php");
    exit();
}

// Get order details
$order_items = $order->getOrderItems($_GET['order_id']);
$seller_phone = ""; // You'll need to get this from the order/product/seller
$whatsapp_message = "Hello, I would like to confirm my order:\n\n";

$total = 0;
while ($item = $order_items->fetch(PDO::FETCH_ASSOC)) {
    $whatsapp_message .= "{$item['product_name']} - {$item['quantity']} pcs - Rp " . number_format($item['price'] * $item['quantity'], 0, ',', '.') . "\n";
    $total += $item['price'] * $item['quantity'];
}

$whatsapp_message .= "\nTotal: Rp " . number_format($total, 0, ',', '.');

include_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Order Review</h1>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $order_items->execute(); // Reset the cursor
                                while ($item = $order_items->fetch(PDO::FETCH_ASSOC)):
                                ?>
                                    <tr>
                                        <td><?php echo $item['product_name']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                        <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="https://wa.me/<?php echo $seller_phone; ?>?text=<?php echo urlencode($whatsapp_message); ?>"
                        class="btn btn-success btn-lg w-100 mb-3" target="_blank">
                        Contact Seller via WhatsApp
                    </a>
                    <form method="post" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                        <button type="submit" name="cancel_order" class="btn btn-danger btn-lg w-100">
                            Cancel Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>