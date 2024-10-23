<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Product.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    header("Location: cart.php");
    exit();
}

// Handle Remove from Cart
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php");
    exit();
}

// Handle Update Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header("Location: cart.php");
    exit();
}

$product = new Product($db);
$cart_items = [];
$total = 0;

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product->id = $product_id;
    $item = $product->readOne();
    $item['quantity'] = $quantity;
    $item['subtotal'] = $quantity * $item['price'];
    $cart_items[] = $item;
    $total += $item['subtotal'];
}

include_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Shopping Cart</h1>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">Your cart is empty.</div>
        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
    <?php else: ?>

        <form method="post" action="cart.php" id="cart-form">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;" class="me-2">
                                    <?php echo $item['name']; ?>
                                </td>
                                <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <input type="number" name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>"
                                        min="1" max="<?php echo $item['stock']; ?>" class="form-control quantity-input" style="width: 100px" data-price="<?php echo $item['price']; ?>">
                                </td>
                                <td class="subtotal" data-id="<?php echo $item['id']; ?>">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong id="total">Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between">
                <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    // Function to update subtotal and total dynamically
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            const price = parseFloat(this.dataset.price);
            const quantity = parseInt(this.value);
            const subtotalCell = this.closest('tr').querySelector('.subtotal');
            const totalCell = document.getElementById('total');

            // Calculate subtotal
            const subtotal = price * quantity;
            subtotalCell.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');

            // Update total
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(cell => {
                const subtotalValue = parseFloat(cell.textContent.replace('Rp ', '').replace('.', '').replace(',', '.'));
                total += subtotalValue;
            });
            totalCell.textContent = 'Rp ' + total.toLocaleString('id-ID');
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>