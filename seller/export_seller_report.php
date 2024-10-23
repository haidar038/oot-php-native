<?php
session_start();
include_once '../config/database.php';
include_once '../classes/Order.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="seller_orders_report.csv"');

// Create file pointer
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, array('Order ID', 'Buyer', 'Total Amount', 'Status', 'Date'));

// Get seller's orders
$seller_id = $_SESSION['user_id'];
$orders = $order->getUserOrders($seller_id);

// Add data rows
while ($row = $orders->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, array(
        $row['id'],
        $row['buyer_name'],
        $row['total_amount'],
        $row['status'],
        $row['created_at']
    ));
}

fclose($output);
exit();
