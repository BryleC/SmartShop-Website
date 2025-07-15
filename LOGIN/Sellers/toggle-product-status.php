<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $new_status = $_POST['new_status'] ?? null;

    if (!$product_id || !$new_status) {
        die("Missing product ID or status.");
    }

    // Ensure new_status is either 'available' or 'unavailable'
    if (!in_array($new_status, ['available', 'unavailable'])) {
        die("Invalid status.");
    }

    // Optional: Verify that the product belongs to the logged-in seller
    $stmt = $conn->prepare("SELECT seller_id FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result) {
        die("Product not found.");
    }

    $seller_id = $result['seller_id'];

    // Get actual seller_id of this user
    $stmt = $conn->prepare("SELECT seller_id FROM sellers WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $seller_check = $stmt->get_result()->fetch_assoc();

    if (!$seller_check || $seller_check['seller_id'] != $seller_id) {
        die("Unauthorized action.");
    }

    // Update status
    $stmt = $conn->prepare("UPDATE products SET status = ? WHERE product_id = ?");
    $stmt->bind_param("si", $new_status, $product_id);

    if ($stmt->execute()) {
        header("Location: my-products.php");
        exit;
    } else {
        echo "Failed to update status.";
    }
}
?>
