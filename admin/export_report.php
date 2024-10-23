<?php
session_start();
include_once '../config/database.php';
include_once '../classes/Order.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="admin_report.csv"');

// Create file pointer
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, array('Date', 'Orders', 'Revenue', 'Average Order Value'));

// Get monthly report data
$monthly_data = $order->getMonthlySales();

// Add data rows
foreach ($monthly_data as $month => $revenue) {
    $orders = $order->getMonthlyOrderCount($month);
    $avg_order = $orders > 0 ? $revenue / $orders : 0;

    fputcsv($output, array(
        $month,
        $orders,
        $revenue,
        number_format($avg_order, 2)
    ));
}

fclose($output);
exit();
