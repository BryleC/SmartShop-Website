<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Get actual seller_id from sellers table
$getSellerId = $conn->prepare("SELECT seller_id FROM sellers WHERE user_id = ?");
$getSellerId->bind_param("i", $user_id);
$getSellerId->execute();
$sellerResult = $getSellerId->get_result();

if ($sellerResult->num_rows === 0) {
    die("You are not a registered seller.");
}

$seller_id_row = $sellerResult->fetch_assoc();
$seller_id = $seller_id_row['seller_id'];

// ✅ Continue with action handling
$order_id = $_POST['order_id'] ?? null;
$action = $_POST['action'] ?? '';

if (!$order_id || !$action) {
    die("Invalid request.");
}

if ($action === 'confirm_payment') {
    // Update only this seller's part of the order
    $stmt = $conn->prepare("UPDATE order_items SET seller_payment_confirmed = 1 
                            WHERE order_id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $order_id, $seller_id);
    $stmt->execute();

    // If all sellers have confirmed, update overall order status
    $checkStmt = $conn->prepare("
        SELECT COUNT(*) AS unconfirmed
        FROM order_items
        WHERE order_id = ? AND seller_payment_confirmed = 0
    ");
    $checkStmt->bind_param("i", $order_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $row = $checkResult->fetch_assoc();

    if ($row['unconfirmed'] == 0) {
        $updateOrder = $conn->prepare("UPDATE orders SET status = 'to_ship' WHERE order_id = ?");
        $updateOrder->bind_param("i", $order_id);
        $updateOrder->execute();
    }

} elseif ($action === 'mark_shipped') {
    // Update full order status
    $stmt = $conn->prepare("UPDATE orders SET status = 'to_receive', shipped_at = NOW() WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

} elseif ($action === 'mark_delivered') {
    // Mark seller delivery complete
    $stmt = $conn->prepare("UPDATE orders SET seller_marked_delivered = 1 WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
}

header("Location: orders.php");
exit;
