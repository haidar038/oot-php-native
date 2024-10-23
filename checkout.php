<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Product.php';
include_once 'classes/Order.php';
include_once 'classes/User.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$order = new Order($db);
$user = new User($db);

// Handle direct product checkout
if (isset($_GET['product_id']) && isset($_GET['quantity'])) {
    $product->id = $_GET['product_id'];
    $product_data = $product->readOne();
    $quantity = $_GET['quantity'];
    $subtotal = $quantity * $product_data['price'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
        $order->buyer_id = $_SESSION['user_id'];
        $order->seller_id = $product_data['seller_id'];
        $order->total_amount = $subtotal;
        $order->status = 'pending';

        if ($order->create()) {
            $order_id = $db->lastInsertId();
            $order->addOrderItem($order_id, $product_data['id'], $quantity);
            header("Location: order_review.php?order_id=" . $order_id);
            exit();
        } else {
            $_SESSION['error'] = "Failed to create order.";
        }
    }
} else {
    header("Location: index.php");
    exit();
}

include_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Checkout</h1>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
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
                                <tr>
                                    <td><?php echo $product_data['name']; ?></td>
                                    <td><?php echo $quantity; ?></td>
                                    <td>Rp <?php echo number_format($product_data['price'], 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Place Order</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <button type="submit" name="place_order" class="btn btn-success btn-lg w-100">
                            Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>