<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Product.php';
include_once 'classes/User.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$user = new User($db);

$cart_items = [];
$total = 0;
$whatsapp_message = "Hello, I would like to order:\n\n";

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product->id = $product_id;
    $item = $product->readOne();
    $item['quantity'] = $quantity;
    $item['subtotal'] = $quantity * $item['price'];
    $cart_items[] = $item;
    $total += $item['subtotal'];

    $whatsapp_message .= "{$item['name']} - {$quantity} pcs - Rp " . number_format($item['subtotal'], 0, ',', '.') . "\n";
}

$whatsapp_message .= "\nTotal: Rp " . number_format($total, 0, ',', '.');

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
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['name']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                        <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Complete Order</h5>
                </div>
                <div class="card-body">
                    <p>Click the button below to complete your order via WhatsApp:</p>
                    <a href="https://wa.me/<?php echo $cart_items[0]['seller_phone']; ?>?text=<?php echo urlencode($whatsapp_message); ?>"
                        class="btn btn-success btn-lg w-100" target="_blank">
                        Complete Order via WhatsApp
                    </a>
                    <small class="text-muted mt-2 d-block">You will be redirected to WhatsApp to complete your order with the seller.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>