<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['product_id'])) {
    http_response_code(400);
    exit('Invalid request');
}

$user_id = $_SESSION['user_id'];
$product_id = (int) $_POST['product_id'];
$action = $_POST['action'] ?? 'save';

if ($action === 'unsave') {
    $stmt = $conn->prepare("DELETE FROM saved WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo 'unsaved';
    } else {
        echo 'not_found';
    }

} else if ($action === 'save') {
    // Avoid duplicate saves
    $check = $conn->prepare("SELECT 1 FROM saved WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
$stmt = $conn->prepare("INSERT INTO saved (user_id, product_id, saved_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }

    echo 'saved';
}
?>
