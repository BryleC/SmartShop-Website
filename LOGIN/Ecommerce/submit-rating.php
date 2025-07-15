<?php
session_start();
include '../connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$order_id = $_POST['order_id'];
$rating = $_POST['rating'];
$comment = trim($_POST['comment']);

// Prevent double rating
$check = $conn->prepare("SELECT * FROM ratings WHERE user_id = ? AND product_id = ? AND order_id = ?");
$check->bind_param("iii", $user_id, $product_id, $order_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $stmt = $conn->prepare(query: "INSERT INTO ratings (user_id, product_id, order_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiis", $user_id, $product_id, $order_id, $rating, $comment);
    $stmt->execute();
}

// Check if all items in this order are rated
$unratedStmt = $conn->prepare("
    SELECT oi.product_id
    FROM order_items oi
    LEFT JOIN ratings r ON r.product_id = oi.product_id AND r.user_id = ? AND r.order_id = ?
    WHERE oi.order_id = ? AND r.rating_id IS NULL
");
$unratedStmt->bind_param("iii", $user_id, $order_id, $order_id);
$unratedStmt->execute();
$unratedResult = $unratedStmt->get_result();

if ($unratedResult->num_rows === 0) {
    $updateOrder = $conn->prepare("UPDATE orders SET status = 'completed' WHERE order_id = ?");
    $updateOrder->bind_param("i", $order_id);
    $updateOrder->execute();
}

// Return success JSON
echo json_encode([
    'success' => true,
    'rating' => intval($rating),
    'comment' => $comment
]);
exit;
