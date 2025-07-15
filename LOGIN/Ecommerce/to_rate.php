<?php
session_start();
include '../connect.php';
include '../burgermenu.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /LOGIN/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Mark 'to_rate' orders as read
$update = $conn->prepare(query: "UPDATE orders SET is_read = 1 WHERE user_id = ? AND status = 'to_rate'");
$update->bind_param("i", $user_id);
$update->execute();

// Get 'to_rate' orders
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND status IN ('to_rate', 'completed') ORDER BY created_at DESC");
$orderStmt->bind_param("i", $user_id);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>To Rate Orders</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f3f4f6;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 20px;
    }
    h2 {
      font-size: 30px;
      color: #111827;
      margin-bottom: 25px;
    }
    .order-card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 30px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.05);
      border: 1px solid #e5e7eb;
    }
    .order-header {
      font-size: 18px;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 12px;
    }
    .order-info {
      font-size: 15px;
      color: #4b5563;
      margin-bottom: 18px;
    }
    .item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 25px;
      border-top: 1px solid #e5e7eb;
      padding-top: 15px;
    }
    .item img {
      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 10px;
      margin-right: 18px;
      border: 1px solid #e5e7eb;
    }
    .item-details {
      flex: 1;
    }
    .item-details strong {
      display: block;
      font-size: 16px;
      color: #111827;
      margin-bottom: 6px;
    }
    .rating-form {
      margin-top: 10px;
    }
    .stars {
      direction: rtl;
      display: inline-flex;
      gap: 4px;
      margin-bottom: 8px;
    }
    .stars input[type="radio"] {
      display: none;
    }
    .stars label {
      font-size: 24px;
      color: #d1d5db;
      cursor: pointer;
      transition: color 0.2s;
    }
    .stars input:checked ~ label,
    .stars label:hover,
    .stars label:hover ~ label {
      color: #fbbf24;
    }
    .rating-form textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      resize: vertical;
      margin-top: 6px;
    }
    .rating-form button {
      margin-top: 10px;
      padding: 8px 16px;
      background-color: #3b82f6;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
    }
    .rating-form button:hover {
      background-color: #2563eb;
    }
    .empty-msg {
      font-size: 16px;
      color: #6b7280;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
  </style>
</head>
<body>

<div class="container">
  <h2>🌟 Rate Your Products</h2>

  <?php if ($orderResult->num_rows === 0): ?>
    <div class="empty-msg">You have no pending products to rate. Go shop some more! 🛍️</div>
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
              Quantity: <?= $item['quantity'] ?> <?= $item['size'] ? "| Size: " . htmlspecialchars($item['size']) : "" ?><br>
              Price: ₱<?= number_format($item['price'], 2) ?>

 <?php
  // Check if item already rated
$ratedStmt = $conn->prepare("SELECT rating, comment FROM ratings WHERE user_id = ? AND product_id = ? AND order_id = ?");
$ratedStmt->bind_param("iii", $user_id, $item['product_id'], $order['order_id']);
$ratedStmt->execute();
$ratedResult = $ratedStmt->get_result();

$rated = $ratedResult->fetch_assoc();

if ($rated):

?>
  <div class="rated-message" style="margin-top:10px;">
  <span style="color: #10b981; font-weight: bold;">
    ⭐ Rated: <?= str_repeat("★", $rated['rating']) ?><?= str_repeat("☆", 5 - $rated['rating']) ?>
  </span><br>
  <span style="color: #374151; font-style: italic;">
    <?= htmlspecialchars($rated['comment']) ?>
  </span>
</div>

<?php else: ?>
<form class="rating-form" data-product-id="<?= $item['product_id'] ?>" data-order-id="<?= $order['order_id'] ?>">
  <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
  <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">

  <div class="stars">
    <?php
      $unique_prefix = $order['order_id'] . '-' . $item['product_id'];
      for ($i = 5; $i >= 1; $i--):
        $inputId = "star-$unique_prefix-$i";
    ?>
      <input type="radio" name="rating" id="<?= $inputId ?>" value="<?= $i ?>">
      <label for="<?= $inputId ?>">★</label>
    <?php endfor; ?>
  </div>

  <textarea name="comment" rows="2" placeholder="Leave a comment (optional)"></textarea>
  <button type="submit">Submit Rating</button>
</form>

<!-- Placeholder for Rated message -->
<div class="rated-message" style="display:none; margin-top:10px; font-weight: bold; color: #10b981;"></div>

<?php endif; ?>

              </form>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>


<script>
document.querySelectorAll('.rating-form').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();

    const productId = form.dataset.productId;
    const orderId = form.dataset.orderId;
    const rating = form.querySelector('input[name="rating"]:checked');
    const comment = form.querySelector('textarea').value;

    if (!rating) {
      alert("Please select a star rating.");
      return;
    }

    fetch('submit-rating.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        product_id: productId,
        order_id: orderId,
        rating: rating.value,
        comment: comment
      })
    })
    .then(res => res.json())
.then(data => {
  if (data.success) {
    form.style.display = 'none';
    const msg = form.nextElementSibling;
msg.innerHTML = `
  <div>
    <span style="color: #10b981; font-weight: bold;">
      ⭐ Rated: ${"★".repeat(data.rating)}${"☆".repeat(5 - data.rating)}
    </span><br>
    <span style="color: #374151; font-style: italic; font-weight: normal;">
      ${data.comment || ''}
    </span>
  </div>
`;

    msg.style.display = 'block';
  } else {
    alert("Failed to submit rating.");
  }
})

    .catch(err => {
      console.error(err);
      alert("An error occurred.");
    });
  });
});
</script>

</body>
</html>
