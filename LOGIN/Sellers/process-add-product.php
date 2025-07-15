<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sellerStmt = $conn->prepare("SELECT seller_id FROM sellers WHERE user_id = ?");
$sellerStmt->bind_param("i", $user_id);
$sellerStmt->execute();
$sellerResult = $sellerStmt->get_result();
$sellerData = $sellerResult->fetch_assoc();

if (!$sellerData) {
    die("Seller not found.");
}

$seller_id = $sellerData['seller_id']; // ✅ Use seller_id to insert

// ✅ Define inputs BEFORE using in logic
$product_name = $_POST['product_name'];
$description = $_POST['description'];
$category = $_POST['category'];
$price = floatval($_POST['price']);
$has_sizes = ($_POST['has_sizes'] === 'yes') ? 1 : 0;
$status = 'available';

// ✅ Handle stock
if ($has_sizes) {
    $stock_s = intval($_POST['stock_s']);
    $stock_m = intval($_POST['stock_m']);
    $stock_l = intval($_POST['stock_l']);
    $stock_xl = intval($_POST['stock_xl']);
    $stock = $stock_s + $stock_m + $stock_l + $stock_xl;
} else {
    $stock = intval($_POST['stock']);
    $stock_s = $stock_m = $stock_l = $stock_xl = 0;
}

// ✅ Handle image upload
$target_dir = $_SERVER['DOCUMENT_ROOT'] . "/login/images/products/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$filename = time() . "_" . basename($_FILES["image"]["name"]);
$target_file = $target_dir . $filename;

if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    $image_path = "/login/images/products/" . $filename;
} else {
    die("Error uploading image.");
}

// ✅ Insert product into DB
$stmt = $conn->prepare("INSERT INTO products 
(product_name, description, category, price, image_path, has_sizes, stock_s, stock_m, stock_l, stock_xl, stock, seller_id, status) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sssdsiiiiiiis",
    $product_name,
    $description,
    $category,
    $price,
    $image_path,
    $has_sizes,
    $stock_s,
    $stock_m,
    $stock_l,
    $stock_xl,
    $stock,
    $seller_id, // ✅ Now using correct seller_id
    $status
);

if ($stmt->execute()) {
    header("Location: add-product.php?success=1");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
?>
