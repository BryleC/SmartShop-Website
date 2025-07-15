<?php
session_start();
include '../connect.php';
include '../burgermenu.php';  
if (!isset($_SESSION['user_id'])) {
    header("Location: /login/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$statuses = ['to_pay', 'to_ship', 'to_receive', 'to_rate'];
$notifs = [];

foreach ($statuses as $status) {
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status = ?");
    $stmt->bind_param("is", $user_id, $status);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    $notifs[$status] = $count;
}

$sql = "SELECT p.product_name, p.price, p.image_path, c.quantity, p.product_id, c.date_added, c.size, p.category, p.has_sizes, p.seller_id
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$userQuery = $conn->prepare("SELECT firstname, lastname, address, wallet_type, wallet_firstname, wallet_lastname, wallet_number FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();

$userName = "User";
$userAddress = $walletType = $walletFname = $walletLname = $walletNumber = '';
if ($userResult && $userResult->num_rows > 0) {
  $userRow = $userResult->fetch_assoc();
  $userName = $userRow['firstname'] . ' ' . $userRow['lastname'];
  $userAddress = $userRow['address'];
  $walletType = $userRow['wallet_type'];
  $walletFname = $userRow['wallet_firstname'];
  $walletLname = $userRow['wallet_lastname'];
  $walletNumber = $userRow['wallet_number'];

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Cart - ShopSmart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>


    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f3f4f6;
      color: #111827;
    }
    header {
      background-color: #000;
      color: white;
      text-align: center;
      padding: 30px 0;
      font-size: 40px;
      font-weight: 700;
    }
    .main-container {
      display: grid;
      grid-template-columns: 2fr 1fr;
      max-width: 1400px;
      margin: 40px auto;
      gap: 40px;
      padding: 0 20px;
    }
    .cart-section, .summary-section {
      background: #fff;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 12px 40px rgba(0,0,0,0.06);
    }
    .summary-section {
      align-self: flex-start;
      position: sticky;
      top: 40px;
    }
    .cart-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 25px;
      color: #1f2937;
    }
    .cart-item {
      display: flex;
      gap: 20px;
      padding: 20px 0;
      border-bottom: 1px solid #e5e7eb;
      align-items: center;
    }
    .cart-item img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 12px;
      cursor: zoom-in;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.3s ease-in-out, z-index 0s;
    }
    .cart-item img:hover {
      transform: scale(2.5);
      z-index: 999;
      position: relative;
    }
    .item-details {
      flex-grow: 1;
    }
    .item-details h3 {
      font-size: 18px;
      margin-bottom: 8px;
    }
    .item-details p {
      font-size: 14px;
      color: #4b5563;
    }
    .item-actions {
      text-align: right;
    }
    .item-actions input[type="number"] {
      width: 60px;
      padding: 5px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      margin-bottom: 10px;
    }
    .subtotal {
      font-size: 16px;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 8px;
    }
    .item-actions button {
      background: none;
      border: none;
      color: #dc2626;
      cursor: pointer;
    }
    .summary-details {
      margin-bottom: 20px;
      font-size: 15px;
      color: #374151;
      line-height: 1.8;
    }
    .total-price {
      font-size: 24px;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 20px;
    }
    .checkout-button {
      width: 100%;
      padding: 14px;
      background-color: #2563eb;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
    }
    .checkout-button:hover {
      background-color: #1e3a8a;
    }
    #checkoutModal, #thankYouModal {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.7);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1001;
      animation: fadeIn 0.25s ease-in-out;
    }
    .modal-content {
      background: white;
      padding: 30px 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      max-width: 500px;
      width: 90%;
      text-align: center;
    }
    .modal-content label {
      font-weight: 600;
      display: block;
      margin-top: 15px;
      text-align: left;
    }
    .modal-content input, .modal-content select {
      width: 100%;
      padding: 12px;
      margin-top: 8px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
    }
    .modal-content button {
      margin-top: 20px;
      padding: 12px;
      width: 100%;
      border: none;
      background-color: #2563eb;
      color: white;
      font-weight: 600;
      font-size: 16px;
      border-radius: 10px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .modal-content button:hover {
      background-color: #1e40af;
    }
    .close-modal {
      background-color: #9ca3af;
      margin-top: 10px;
    }
    .close-modal:hover {
      background-color: #6b7280;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    .order-status-tabs {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  display: flex;
  overflow: hidden;
  z-index: 10000;
  border: 1px solid #d1d5db;
}

.order-status-tabs button {
  background: #f9fafb;
  border: none;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  border-right: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 6px;
}

.order-status-tabs button:last-child {
  border-right: none;
}

.order-status-tabs button:hover {
  background-color: #facc15; /* yellow highlight on hover */
  color: #000000;
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
}

.badge {
  background-color: red;
  color: white;
  padding: 2px 6px;
  border-radius: 50%;
  font-size: 12px;
  margin-left: 6px;
  display: inline-block;
  min-width: 18px;
  text-align: center;
}

  </style>
</head>
<body>

<header>ShopSmart</header>

<div class="main-container">
  <div class="cart-section">

    <h2 class="cart-title">🛒 My Shopping Cart</h2>

        <div style="margin-bottom: 15px;">
  <label style="font-size: 16px; cursor: pointer;">
    <input type="checkbox" id="selectAll" style="margin-right: 8px;" onchange="toggleSelectAll(this)">
    Select All
  </label>
</div>
    <?php
    $total = 0;
    $itemCount = 0;
    while ($row = $result->fetch_assoc()):
        $subtotal = $row['price'] * $row['quantity'];
        $total += $subtotal;
        $itemCount += $row['quantity'];
    ?>
    <div class="cart-item">
<input type="checkbox" class="select-item" onchange="handleItemSelection()">
      <img src="<?php echo htmlspecialchars($row['image_path']); ?>?v=<?= time() ?>" alt="Product">
      <div class="item-details">
        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
        <p>Price: ₱<?php echo number_format($row['price'], 2); ?></p>
        
        <?php if ($row['has_sizes'] == 1 && !empty($row['size'])): ?>
        <p>Size: <?php echo htmlspecialchars($row['size']); ?></p>
        <?php endif; ?>


        <p>Quantity: <?= $row['quantity'] ?></p>
        <p>Date Added: <?php echo date("F j, Y g:i A", strtotime($row['date_added'])); ?></p>
      </div>
      <div class="item-actions">
        <input type="number" value="<?= $row['quantity'] ?>" min="1" onchange="updateSubtotal(this, <?= $row['price'] ?>)">
        <p class="subtotal">₱<?= number_format($subtotal, 2) ?></p>
<button class="remove-btn" 
  data-product-id="<?= $row['product_id'] ?>" 
  data-size="<?= htmlspecialchars($row['size']) ?>"
  data-seller-id="<?= $row['seller_id'] ?>">Remove</button>      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <div class="summary-section">
    <h2 class="cart-title">Order Summary</h2>
   <?php
  if ($itemCount === 0) {
    $deliveryTotal = 0;
  } elseif ($itemCount <= 3) {
    $deliveryTotal = 40;
  } elseif ($itemCount <= 6) {
    $deliveryTotal = 80;
  } elseif ($itemCount <= 9) {
    $deliveryTotal = 120;
  } else {
    $deliveryTotal = 140;
  }
?>
    <div class="summary-details">
      <p><strong>Total Items:</strong> <?= $itemCount ?></p>
<!-- <p><strong>Delivery Fee:</strong> ₱<?= number_format($deliveryTotal, 2) ?></p> -->
<!-- Delivery fee will show in confirm modal -->
      <p><strong>Estimated Delivery:</strong> 3-5 business days</p>
    </div>
      <div class="total-price" id="totalPrice">Total: ₱<?php echo number_format($total, 2); ?></div>
    <p id="selectedTotal" style="font-size: 16px; color: #10b981; margin-top: 10px;">Selected Total: ₱0.00</p>
<button class="checkout-button" onclick="showCheckoutModal()">Proceed to Checkout</button>
  </div>
</div>

<div id="checkoutModal">
  <div class="modal-content">
    <h2>Confirm Your Order</h2>
    <p><strong>Name:</strong> <?= htmlspecialchars($userName) ?></p>
    <label for="address">Delivery Address</label>
<input type="text" id="address" value="<?= htmlspecialchars($userAddress) ?>" placeholder="Enter your address" required>
    <label for="payment">Payment Method</label>
    <select id="payment" required>
      <option value="">Select...</option>
      <option value="cod">Cash on Delivery</option>
      <option value="ewallet">E-Wallet</option>
      <option value="debit">Debit Card</option>
      <option value="credit">Credit Card</option>
    </select>


    <div id="ewalletInfo" style="display:none; text-align: left; margin-top: 20px;"></div>
    <div id="checkoutPreview" style="margin-top: 20px; font-size: 14px; line-height: 1.6; text-align: left; color: #1f2937;"></div>
    <div id="receiptDetails" style="margin-top: 20px; font-size: 14px; line-height: 1.6; text-align: left;"></div>


    <button onclick="confirmOrder()">Confirm Order</button>
    <button class="close-modal" onclick="closeModal('checkoutModal')">Cancel</button>
  </div>
</div>

<div id="thankYouModal">
  <div class="modal-content">
    <h2>Thank you for your purchase!</h2>
    <p>Your order has been placed successfully.</p>
    <p><strong>Name:</strong> <?= htmlspecialchars($userName) ?></p>
    <p id="receiptDetails"></p>
    <button class="close-modal" onclick="closeModal('thankYouModal')">Close</button>
  </div>
</div>

<div class="order-status-tabs">
  <button onclick="window.location.href='/LOGIN/Ecommerce/to_pay.php'">
    🧾 To Pay<?= $notifs['to_pay'] > 0 ? "<span class='badge'>{$notifs['to_pay']}</span>" : "" ?>
  </button>
  <button onclick="window.location.href='/LOGIN/Ecommerce/to_ship.php'">
    📦 To Ship<?= $notifs['to_ship'] > 0 ? "<span class='badge'>{$notifs['to_ship']}</span>" : "" ?>
  </button>
  <button onclick="window.location.href='/LOGIN/Ecommerce/to_receive.php'">
    🚚 To Receive<?= $notifs['to_receive'] > 0 ? "<span class='badge'>{$notifs['to_receive']}</span>" : "" ?>
  </button>
  <button onclick="window.location.href='/LOGIN/Ecommerce/to_rate.php'">
    ⭐ To Rate<?= $notifs['to_rate'] > 0 ? "<span class='badge'>{$notifs['to_rate']}</span>" : "" ?>
  </button>
</div>




<script>

function toggleSelectAll(checkbox) {
  const allItems = document.querySelectorAll('.select-item');
  allItems.forEach(item => {
    item.checked = checkbox.checked;
  });
  updateSelectedTotal();
}


function showCheckoutModal() {
  const selectedItems = document.querySelectorAll('.select-item:checked');
  if (selectedItems.length === 0) {
    alert('Please select at least one item to check out.');
    return;
  }

  let totalQuantity = 0;
  let selectedTotal = 0;

  selectedItems.forEach(item => {
    const cartItem = item.closest('.cart-item');
    const quantity = parseInt(cartItem.querySelector('input[type="number"]').value);
    const price = parseFloat(cartItem.querySelector('.subtotal').textContent.replace(/[^\d.]/g, ''));

    totalQuantity += quantity;
    selectedTotal += price;
  });

  let deliveryFee = 0;
  if (totalQuantity === 0) {
    deliveryFee = 0;
  } else if (totalQuantity <= 3) {
    deliveryFee = 40;
  } else if (totalQuantity <= 6) {
    deliveryFee = 80;
  } else if (totalQuantity <= 9) {
    deliveryFee = 120;
  } else {
    deliveryFee = 140;
  }

  const grandTotal = selectedTotal + deliveryFee;

  document.getElementById('checkoutPreview').innerHTML = `
    <p><strong>Selected Items:</strong> ${totalQuantity}</p>
    <p><strong>Selected Total:</strong> ₱${selectedTotal.toFixed(2)}</p>
    <p><strong>Delivery Fee:</strong> ₱${deliveryFee.toFixed(2)}</p>
    <p style="font-weight: bold; font-size: 15px;">Total Payment: ₱${grandTotal.toFixed(2)}</p>
  `;

  // Store deliveryFee for later use in confirmOrder()
  window._latestDeliveryFee = deliveryFee;

  document.getElementById('checkoutModal').style.display = 'flex';
}

document.querySelectorAll('.remove-btn').forEach(button => {
  button.addEventListener('click', function () {
    const productId = this.dataset.productId;
    const size = this.dataset.size;
    const itemElement = this.closest('.cart-item');
    itemElement.remove();
    updateTotal();
    fetch('/LOGIN/Ecommerce/remove.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `product_id=${productId}&size=${encodeURIComponent(size)}`
    });
  });
});

function updateSubtotal (input, price) {
  const quantity = parseInt(input.value);
  const itemElement = input.closest('.cart-item');
  const subtotal = quantity * price;
  itemElement.querySelector('.subtotal').textContent = '₱' + subtotal.toFixed(2);
  updateTotal();
  updateSelectedTotal(); // <-- add this
}

function updateTotal() {
  let total = 0;
  let items = 0;
  document.querySelectorAll('.cart-item').forEach(item => {
    const price = parseFloat(item.querySelector('.subtotal').textContent.replace(/[^\d.]/g, ''));
    const quantityInput = item.querySelector('input[type="number"]');
    const quantity = parseInt(quantityInput.value);
    total += price;
    items += quantity;
  });

let deliveryFee = 0;
if (items === 0) {
  deliveryFee = 0;
} else if (items <= 3) {
  deliveryFee = 40;
} else if (items <= 6) {
  deliveryFee = 80;
} else if (items <= 9) {
  deliveryFee = 120;
} else {
  deliveryFee = 140;
}



const totalAmount = total + deliveryFee;
  document.getElementById('totalPrice').textContent = 'Total: ₱' + totalAmount.toFixed(2);
  document.querySelector('.summary-details').innerHTML = `
    <p><strong>Total Items:</strong> ${items}</p>
<p><strong>Delivery Fee:</strong> ₱${deliveryFee}</p>
    <p><strong>Estimated Delivery:</strong> 3-5 business days</p>`;
}

function confirmOrder() {
  const address = document.getElementById('address').value.trim();
  const payment = document.getElementById('payment').value;
  if (!address || !payment) {
    alert('Please provide address and payment method.');
    return;
  }

  const selectedItems = document.querySelectorAll('.select-item:checked');
  if (selectedItems.length === 0) {
    alert('Please select at least one item to check out.');
    return;
  }

  let products = [];
  let totalQuantity = 0;
  let selectedTotal = 0;

  selectedItems.forEach(item => {
    const cartItem = item.closest('.cart-item');
    const productId = cartItem.querySelector('.remove-btn').dataset.productId;
    const size = cartItem.querySelector('.remove-btn').dataset.size;
    const quantity = parseInt(cartItem.querySelector('input[type="number"]').value);
    const price = parseFloat(cartItem.querySelector('.subtotal').textContent.replace(/[^\d.]/g, ''));

const sellerId = cartItem.querySelector('.remove-btn').dataset.sellerId;

products.push({ 
  product_id: productId, 
  size: size, 
  quantity: quantity, 
  price: price,
  seller_id: sellerId
});

totalQuantity += quantity;
selectedTotal += price;
});

  // Calculate delivery fee based on total quantity of selected items
  let deliveryFee = 0;
  if (totalQuantity === 0) {
    deliveryFee = 0;
  } else if (totalQuantity <= 3) {
    deliveryFee = 40;
  } else if (totalQuantity <= 6) {
    deliveryFee = 80;
  } else if (totalQuantity <= 9) {
    deliveryFee = 120;
  } else {
    deliveryFee = 140;
  }

  // Show updated receipt in confirmation modal
  const receiptPreview = `
    <strong>Address:</strong> ${address}<br>
    <strong>Payment:</strong> ${payment.charAt(0).toUpperCase() + payment.slice(1)}<br>
    <strong>Delivery Fee:</strong> ₱${deliveryFee.toFixed(2)}<br>
    <strong>Total Payment:</strong> ₱${(selectedTotal + deliveryFee).toFixed(2)}
  `;
  document.getElementById('receiptDetails').innerHTML = receiptPreview;

  // Save deliveryFee globally so it can be used later in thank you modal
  window._latestDeliveryFee = deliveryFee;

  // Send data to backend
  fetch('../Ecommerce/place-order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ address: address, payment: payment, items: products })
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      document.getElementById('checkoutModal').style.display = 'none';
      document.getElementById('thankYouModal').style.display = 'flex';
      document.getElementById('receiptDetails').innerHTML += `<br><strong>Order ID:</strong> ${data.order_id}<br><strong>Date:</strong> ${new Date().toLocaleString()}`;

      // Remove items from UI
      selectedItems.forEach(item => {
        const cartItem = item.closest('.cart-item');
        if (cartItem) cartItem.remove();
      });

      updateSelectedTotal();
      updateTotal();
    } else {
      alert("Failed to place order.");
    }
  });
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

const walletType = <?= json_encode($walletType) ?>;
const walletFname = <?= json_encode($walletFname) ?>;
const walletLname = <?= json_encode($walletLname) ?>;
const walletNumber = <?= json_encode($walletNumber) ?>;

document.getElementById('payment').addEventListener('change', function () {
  const ewalletDiv = document.getElementById('ewalletInfo');
  if (this.value === 'ewallet') {
    if (!walletType || !walletFname || !walletNumber) {
      ewalletDiv.innerHTML = `
        <p style="color: #c0392b;"><strong>No E-Wallet Linked!</strong></p>
        <button onclick="window.location.href='profile.php'" style="margin-top: 10px; background-color: #f59e0b; padding: 10px 20px; border: none; border-radius: 6px; color: white; font-weight: bold; cursor: pointer;">
          Link Your E-Wallet
        </button>
      `;
    } else {
      ewalletDiv.innerHTML = `
        <p><strong>Wallet:</strong> ${walletType.toUpperCase()}</p>
        <p><strong>Name:</strong> ${walletFname} ${walletLname}</p>
        <p><strong>Number:</strong> ${walletNumber}</p>
        <img src="../Images/E-wallet/${walletType}.png" alt="${walletType}" style="height: 40px; margin-top: 10px;">
      `;
    }
    ewalletDiv.style.display = 'block';
  } else {
    ewalletDiv.style.display = 'none';
    ewalletDiv.innerHTML = '';
  }
});

function updateSelectedTotal() {
  let selectedTotal = 0;
  const selectedItems = document.querySelectorAll('.select-item:checked');

  selectedItems.forEach(item => {
    const cartItem = item.closest('.cart-item');
    const price = parseFloat(cartItem.querySelector('.subtotal').textContent.replace(/[^\d.]/g, ''));
    selectedTotal += price;
  });

  document.getElementById('selectedTotal').textContent = 'Selected Total: ₱' + selectedTotal.toFixed(2);
}


</script>


</body>
</html>