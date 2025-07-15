<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get form data
$shop_name = $_POST['shop_name'];
$category = $_POST['category'];
$shop_address = $_POST['shop_address'];
$shop_phone = $_POST['shop_phone'];

// 1. Insert seller details into `sellers` table
$stmt = $conn->prepare("INSERT INTO sellers (user_id, shop_name, category, shop_address, shop_phone) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $user_id, $shop_name, $category, $shop_address, $shop_phone);
$stmt->execute();

// 2. Update user role to 'seller'
$update = $conn->prepare("UPDATE users SET role = 'seller' WHERE user_id = ?");
$update->bind_param("i", $user_id);
$update->execute();

// 3. Redirect to seller dashboard
header("Location: SellerDashboard.php");
exit;
?>
