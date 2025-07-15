<?php

session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int) $_POST['product_id'];
$quantity = (int) $_POST['quantity'];
$size = isset($_POST['size']) && $_POST['size'] !== '' ? $_POST['size'] : null;

// Fetch has_sizes from the database
$stmt = $conn->prepare("SELECT has_sizes FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

$has_sizes = $product['has_sizes'] ?? 'no';

// Prevent submission if size is required but missing
if ($has_sizes === 'yes' && !$size) {
    echo "Error: Size is required.";
    exit;
}

// Step 1: Check if same product_id and size (or null) already exists
$sql = "SELECT cart_id, quantity FROM cart 
        WHERE user_id = ? AND product_id = ? AND (
            (size IS NULL AND ? IS NULL) OR (size = ?)
        )";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $user_id, $product_id, $size, $size);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Step 2: Update quantity
    $new_quantity = $row['quantity'] + $quantity;
$update = $conn->prepare("UPDATE cart SET quantity = ?, date_added = NOW() WHERE cart_id = ?");
$update->bind_param("ii", $new_quantity, $row['cart_id']);

    $update->execute();
} else {
    // Step 3: Insert as new
$insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, size, date_added) VALUES (?, ?, ?, ?, NOW())");
$insert->bind_param("iiis", $user_id, $product_id, $quantity, $size);
    $insert->execute();
}

// Optional: Redirect back or return success
echo "success";
exit;
?>