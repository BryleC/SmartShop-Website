<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Not logged in');
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$size = isset($_POST['size']) && $_POST['size'] !== '' ? $_POST['size'] : 'none';

if (!$product_id) {
    http_response_code(400);
    exit('Missing product ID');
}

// Properly delete based on size logic
$stmt = $conn->prepare("
    DELETE FROM cart 
    WHERE user_id = ? 
      AND product_id = ? 
      AND (size = ? OR (size IS NULL AND ? = 'none'))
");
$stmt->bind_param("iiss", $user_id, $product_id, $size, $size);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo 'Item removed';
} else {
    http_response_code(404);
    echo 'Item not found';
}
?>
