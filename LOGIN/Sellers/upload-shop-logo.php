<?php
session_start();
include '../connect.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || !isset($_FILES['shop_logo'])) {
    echo "Invalid request";
    exit;
}

$target_dir = "../uploads/logos/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$filename = time() . "_" . basename($_FILES["shop_logo"]["name"]);
$target_file = $target_dir . $filename;

$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif'];

if (in_array($imageFileType, $allowed)) {
    if (move_uploaded_file($_FILES["shop_logo"]["tmp_name"], $target_file)) {
        $relative_path = "uploads/logos/" . $filename;

        $stmt = $conn->prepare("UPDATE sellers SET logo_path = ? WHERE user_id = ?");
        $stmt->bind_param("si", $relative_path, $user_id);
        $stmt->execute();

        echo "Success";
    } else {
        echo "Failed to upload.";
    }
} else {
    echo "Invalid file type.";
}
?>
