<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Verify seller role
$stmt = $conn->prepare("
    SELECT users.role, sellers.shop_name 
    FROM users 
    LEFT JOIN sellers ON users.user_id = sellers.user_id 
    WHERE users.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || $user['role'] !== 'seller') {
    header("Location: ../homepage.php");
    exit;
}

// Get the seller_id based on user_id
$stmt = $conn->prepare("SELECT seller_id, shop_name FROM sellers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sellerInfo = $stmt->get_result()->fetch_assoc();

if (!$sellerInfo) {
    echo "Seller profile not found.";
    exit;
}

$seller_id = $sellerInfo['seller_id'];
$shop_name = $sellerInfo['shop_name'] ?? '';

// Fetch products using seller_id (not user_id)
$stmt = $conn->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY product_id DESC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
  <title>My Product Listings</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background: #f4f4f4;
      padding: 40px 20px;
    }

    .container {
      max-width: 1100px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    h2 {
      color: #162447;
      text-align: center;
      margin-bottom: 30px;
    }

    .btn-back {
      display: inline-block;
      margin-bottom: 20px;
      background: #ccc;
      color: #162447;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      text-align: left;
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      vertical-align: middle;
    }

    th {
      background-color: #162447;
      color: #fff;
    }

    tr:hover {
      background-color: #f9f9f9;
    }

    .product-img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
    }

    .price {
      color: #f5d442;
      font-weight: bold;
    }

    .stock {
      font-weight: bold;
      color: green;
    }

    .empty-message {
      text-align: center;
      color: #888;
      padding: 30px;
    }

    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      th {
        display: none;
      }

      td {
        border: none;
        position: relative;
        padding-left: 50%;
      }

      td:before {
        position: absolute;
        left: 15px;
        top: 12px;
        font-weight: bold;
        color: #555;
        white-space: nowrap;
      }

      td:nth-child(1):before { content: "Image"; }
      td:nth-child(2):before { content: "Product"; }
      td:nth-child(3):before { content: "Category"; }
      td:nth-child(4):before { content: "Price"; }
      td:nth-child(5):before { content: "Stock"; }
      td:nth-child(6):before { content: "Date"; }
    }
  </style>
</head>
<body>

<div class="container">
  <a href="SellerDashboard.php" class="btn-back">⬅ Back to Dashboard</a>

<h2>📋 My Product Listings</h2>
<?php if (!empty($shop_name)): ?>
  <p style="text-align: center; font-size: 18px; color: #555;">
     <strong><?php echo htmlspecialchars($shop_name); ?></strong>
  </p>
<?php endif; ?>

  <?php if ($products->num_rows > 0): ?>
    <table>
      <thead>
  <tr>
    <th>Image</th>
    <th>Product</th>
    <th>Category</th>
    <th>Price</th>
    <th>Stock</th>
    <th>Date Added</th>
    <th>Action</th>
  </tr>
</thead>

      </thead>
      <tbody>
        <?php while ($p = $products->fetch_assoc()): ?>
         <tr>
  <td><img src="<?php echo htmlspecialchars($p['image_path']); ?>" class="product-img" alt="Product"></td>
  <td>
    <strong><?php echo htmlspecialchars($p['product_name']); ?></strong><br>
    <span style="font-size: 13px; color: #555;"><?php echo htmlspecialchars($p['description']); ?></span>
  </td>
  <td><?php echo ucfirst($p['category']); ?></td>
  <td class="price">₱<?php echo number_format($p['price'], 2); ?></td>
  <td class="stock">
    <?php
if ($p['has_sizes']) {
    echo "S: {$p['stock_s']}, M: {$p['stock_m']}, L: {$p['stock_l']}, XL: {$p['stock_xl']}";
} else {
    echo "{$p['stock']} pcs";
}

    ?>
  </td>
  <td><?php echo date('M d, Y', strtotime($p['created_at'] ?? 'now')); ?></td>
  <td>
    <form method="POST" action="toggle-product-status.php">
      <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
      <input type="hidden" name="new_status" value="<?= $p['status'] === 'available' ? 'unavailable' : 'available' ?>">
      <button type="submit" style="padding:6px 12px; background: <?= $p['status'] === 'available' ? '#e74c3c' : '#2ecc71' ?>; color:#fff; border:none; border-radius:5px;">
        <?= $p['status'] === 'available' ? 'Remove' : 'Reactivate' ?>
      </button>
    </form>
  </td>
</tr>

        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="empty-message">You haven't added any products yet.</div>
  <?php endif; ?>
</div>

</body>
</html>
