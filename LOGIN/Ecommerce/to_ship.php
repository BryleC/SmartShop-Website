<?php
session_start();
include '../connect.php';
include '../burgermenu.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /LOGIN/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Mark 'to_ship' orders as read
$update = $conn->prepare("UPDATE orders SET is_read = 1 WHERE user_id = ? AND status = 'to_ship'");
$update->bind_param("i", $user_id);
$update->execute();

// Get all orders with status 'to_ship'
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND status = 'to_ship' ORDER BY created_at DESC");
$orderStmt->bind_param("i", $user_id);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>To Ship Orders</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9fafb;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 1000px;
      margin: 30px auto;
      padding: 20px;
    }
    h2 {
      font-size: 28px;
      color: #111827;
      margin-bottom: 20px;
    }
    .order-card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 25px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.05);
      border: 1px solid #e5e7eb;
      transition: transform 0.2s ease-in-out;
    }
    .order-card:hover {
      transform: translateY(-4px);
    }
    .order-header {
      font-size: 18px;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 10px;
    }
    .order-info {
      font-size: 15px;
      color: #4b5563;
      margin-bottom: 15px;
    }
    .item {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }
    .item img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 10px;
      margin-right: 15px;
      border: 1px solid #e5e7eb;
    }
    .item-details {
      font-size: 14px;
      color: #374151;
    }
    .item-details strong {
      display: block;
      font-size: 15px;
      color: #111827;
      margin-bottom: 4px;
    }
    .empty-msg {
      font-size: 16px;
      color: #6b7280;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
  </style>
</head>
<body>

<div class="container">
  <h2>🧾 To Ship Orders</h2>

  <?php if ($orderResult->num_rows === 0): ?>
    <div class="empty-msg">You have no pending "To Ship" orders at the moment.</div>
  <?php else: ?>
    <?php while ($order = $orderResult->fetch_assoc()): ?>
      <div class="order-card">
        <div class="order-header">
          Order #<?= $order['order_id'] ?> &nbsp;|&nbsp; 
          Placed on <?= date("F j, Y g:i A", strtotime($order['created_at'])) ?>
        </div>
        <div class="order-info">
          <strong>Payment:</strong> <?= ucfirst($order['payment_method']) ?> &nbsp;|&nbsp;
          <strong>Total:</strong> ₱<?= number_format($order['total'], 2) ?>
        </div>

        <?php
        $itemsStmt = $conn->prepare("
          SELECT oi.*, p.product_name, p.image_path 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.product_id 
          WHERE oi.order_id = ?
        ");
        $itemsStmt->bind_param("i", $order['order_id']);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        while ($item = $itemsResult->fetch_assoc()):
        ?>
          <div class="item">
            <img src="<?= $item['image_path'] ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
            <div class="item-details">
              <strong><?= htmlspecialchars($item['product_name']) ?></strong>
              Quantity: <?= $item['quantity'] ?> &nbsp;
              <?= $item['size'] ? "| Size: " . htmlspecialchars($item['size']) . " " : "" ?>
              | Price: ₱<?= number_format($item['price'], 2) ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

</body>
</html>
