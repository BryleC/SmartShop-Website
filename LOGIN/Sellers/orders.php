<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Get the correct seller_id for this user_id
$getSellerId = $conn->prepare("SELECT seller_id FROM sellers WHERE user_id = ?");
$getSellerId->bind_param("i", $user_id);
$getSellerId->execute();
$sellerResult = $getSellerId->get_result();

if ($sellerResult->num_rows === 0) {
    echo "<div style='padding: 40px; text-align: center;'>You are not registered as a seller.</div>";
    exit;
}

$seller_id = $sellerResult->fetch_assoc()['seller_id'];
// echo "<pre>Logged in as seller ID: $seller_id</pre>";

// ✅ Confirm role is seller
$userCheck = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$userCheck->bind_param("i", $user_id);
$userCheck->execute();
$userRole = $userCheck->get_result()->fetch_assoc();

if ($userRole['role'] !== 'seller') {
    header("Location: ../homepage.php");
    exit;
}

// ✅ Fetch orders for this seller_id
$sql = "SELECT 
            oi.order_id, oi.product_id, oi.quantity, oi.size, oi.price, 
            o.created_at AS order_date, o.status, o.payment_method, o.seller_marked_delivered,
            u.firstname, u.lastname, o.address,
            p.product_name, p.image_path, oi.seller_id, oi.seller_payment_confirmed
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        JOIN users u ON o.user_id = u.user_id
        WHERE oi.seller_id = ?
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders Received</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f4f8;
      padding: 40px 20px;
    }

    .container {
      max-width: 1000px;
      margin: auto;
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #162447;
    }

    .order {
      display: flex;
      gap: 20px;
      margin-bottom: 25px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 20px;
    }

    .order img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
    }

    .details {
      flex-grow: 1;
    }

    .details h3 {
      margin: 0 0 10px;
      font-size: 18px;
      color: #1f2937;
    }

    .details p {
      margin: 4px 0;
      color: #374151;
      font-size: 14px;
    }

    .status {
      font-weight: bold;
      color: #2563eb;
    }

    form {
      margin-top: 10px;
    }

    button {
      padding: 8px 16px;
      margin-right: 10px;
      background: #2563eb;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
    }

    button:hover {
      background: #1d4ed8;
    }

    .empty {
      text-align: center;
      color: #888;
      padding: 50px;
      font-size: 18px;
    }
  </style>
</head>
<body>

<div class="container">
  <h1>📦 Orders Received</h1>

  <?php if ($result->num_rows > 0): ?>

<?php
// Group items by order_id
$orders = [];

while ($row = $result->fetch_assoc()) {
    $orders[$row['order_id']]['items'][] = $row;

    if (!isset($orders[$row['order_id']]['info'])) {
        $orders[$row['order_id']]['info'] = [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'address' => $row['address'],
            'payment_method' => $row['payment_method'],
            'order_date' => $row['order_date'],
            'status' => $row['status'],
            'seller_marked_delivered' => $row['seller_marked_delivered']
        ];
    }
}
?>

<?php foreach ($orders as $order_id => $order): ?>
  <div class="order">
    <div class="details" style="width: 100%;">
      <h3>🧾 Order #<?= $order_id ?></h3>
      <p><strong>Buyer:</strong> <?= htmlspecialchars($order['info']['firstname'] . ' ' . $order['info']['lastname']) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($order['info']['address']) ?></p>
      <p><strong>Payment Method:</strong> <?= strtoupper($order['info']['payment_method']) ?></p>
      <p><strong>Ordered On:</strong> <?= date("F j, Y g:i A", strtotime($order['info']['order_date'])) ?></p>
      <p><strong>Status:</strong> <span class="status"><?= ucfirst($order['info']['status']) ?></span></p>

      <hr>

      <?php foreach ($order['items'] as $item): ?>
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
          <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Product" width="80" height="80" style="object-fit: cover; border-radius: 6px;">
          <div>
            <p><strong><?= htmlspecialchars($item['product_name'] ?? 'Product Deleted') ?></strong></p>
            <p>Quantity: <?= $item['quantity'] ?><?= $item['size'] ? ', Size: ' . htmlspecialchars($item['size']) : '' ?></p>
            <p>Price: ₱<?= number_format($item['price'], 2) ?></p>

             <!-- ✅ ADD THIS RIGHT HERE: -->
      <p style="font-style: italic; color: gray;">
        <?= ($item['seller_id'] == $seller_id)
              ? ($item['seller_payment_confirmed'] ? '✅ You confirmed payment' : '❗You haven’t confirmed payment yet')
              : ($item['seller_payment_confirmed'] ? '✅ Confirmed by another seller' : '⏳ Waiting for other seller') ?>
      </p>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- Confirm form appears once per order -->
      <form method="POST" action="update-order-status.php">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">

<?php
$showConfirm = false;
foreach ($order['items'] as $item) {
    if ($item['seller_id'] == $seller_id && $item['seller_payment_confirmed'] == 0) {
        $showConfirm = true;
        break;
    }
}
?>

<?php if ($showConfirm && $order['info']['payment_method'] !== 'cod'): ?>
    <button type="submit" name="action" value="confirm_payment">Confirm Payment</button>


        
        <?php elseif ($order['info']['status'] === 'to_ship'): ?>
          <button type="submit" name="action" value="mark_shipped">Mark as Shipped</button>
        
        <?php endif; ?>

        <?php if ($order['info']['status'] === 'to_receive' && !$order['info']['seller_marked_delivered']): ?>
          <button type="submit" name="action" value="mark_delivered">Mark as Delivered</button>
        <?php endif; ?>
      </form>

    </div>
  </div>
<?php endforeach; ?>

  <?php else: ?>
    <div class="empty">You have not received any orders yet.</div>
  <?php endif; ?>
</div>

</body>
</html>
