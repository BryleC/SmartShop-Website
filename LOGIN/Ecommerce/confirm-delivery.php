<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['order_id'])) {
    header("Location: /LOGIN/index.php");
    exit;
}

$order_id = $_POST['order_id'];
$user_id = $_SESSION['user_id'];

// Ensure order belongs to the current user
$check = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$check->bind_param("ii", $order_id, $user_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 1) {
    // Update status from 'to_receive' to 'to_rate'
    $update = $conn->prepare("UPDATE orders SET status = 'to_rate' WHERE order_id = ?");
    $update->bind_param("i", $order_id);
    $update->execute();
}

// Redirect back to the To Receive page
header("Location: to_receive.php");
exit;
