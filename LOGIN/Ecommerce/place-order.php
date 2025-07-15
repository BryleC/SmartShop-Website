<?php
session_start();
include '../connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

$address = trim($data['address'] ?? '');
$payment = trim($data['payment'] ?? '');
$items = $data['items'] ?? [];

if (!$address || !$payment || empty($items)) {
    echo json_encode(["status" => "invalid", "message" => "Missing address, payment, or items."]);
    exit;
}

// Calculate total price
$total = 0;
foreach ($items as $item) {
    $total += floatval($item['price']) * intval($item['quantity']);
}

// Insert into orders table
$orderStmt = $conn->prepare("INSERT INTO orders (user_id, address, payment_method, total) VALUES (?, ?, ?, ?)");
$orderStmt->bind_param("issd", $user_id, $address, $payment, $total);
$orderStmt->execute();
$order_id = $orderStmt->insert_id;

if (!$order_id) {
    echo json_encode(["status" => "error", "message" => "Order creation failed."]);
    exit;
}

// Process each item
foreach ($items as $item) {
    $product_id = intval($item['product_id']);
    $quantity = intval($item['quantity']);
    $size = $item['size'] ?? null;
    $price = floatval($item['price']);

    // Get seller_id from products table
    $sellerQuery = $conn->prepare("SELECT seller_id FROM products WHERE product_id = ?");
    $sellerQuery->bind_param("i", $product_id);
    $sellerQuery->execute();
    $sellerResult = $sellerQuery->get_result();

    if ($sellerResult->num_rows === 0) {
        continue; // skip if product not found
    }

    $seller_id = $sellerResult->fetch_assoc()['seller_id'];

    // Insert into order_items table
    $insertItem = $conn->prepare(
        "INSERT INTO order_items (order_id, product_id, seller_id, quantity, size, price)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insertItem->bind_param("iiiisd", $order_id, $product_id, $seller_id, $quantity, $size, $price);
    $insertItem->execute();

    // Update product stock
    if (!empty($size)) {
        $sizeColumn = "stock_" . strtolower($size);
        $updateStock = $conn->prepare("UPDATE products SET $sizeColumn = $sizeColumn - ? WHERE product_id = ?");
        $updateStock->bind_param("ii", $quantity, $product_id);
        $updateStock->execute();
    } else {
        $updateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
        $updateStock->bind_param("ii", $quantity, $product_id);
        $updateStock->execute();
    }

    // Remove from cart
    $deleteCart = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ? AND (size = ? OR size IS NULL)");
    $deleteCart->bind_param("iis", $user_id, $product_id, $size);
    $deleteCart->execute();
}

echo json_encode([
    "status" => "success",
    "order_id" => $order_id
]);
?>
