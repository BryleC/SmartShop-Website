<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $conn->prepare("
    SELECT users.*, sellers.logo_path, sellers.shop_name 
    FROM users 
    LEFT JOIN sellers ON users.user_id = sellers.user_id 
    WHERE users.user_id = ?

");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['role'] !== 'seller') {
    header("Location: ../homepage.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Seller Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      margin: 0;
      background: linear-gradient(to right, #f5d442, #162447);
      min-height: 100vh;
      padding: 40px 20px;
      color: #162447;
    }

    .dashboard {
      max-width: 1100px;
      margin: auto;
      background: white;
      border-radius: 16px;
      padding: 40px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    h1 {
      margin-top: 0;
      color: #162447;
    }

    .intro {
      margin-bottom: 30px;
    }

    .intro p {
      font-size: 16px;
      color: #333;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 40px;
    }

    .card {
      flex: 1 1 240px;
      background: #f9f9f9;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 5px 12px rgba(0, 0, 0, 0.07);
      transition: transform 0.2s;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card h2 {
      margin-top: 0;
      font-size: 20px;
      color: #162447;
    }

    .card p {
      font-size: 14px;
      color: #555;
      margin-bottom: 15px;
    }

    .btn {
      display: inline-block;
      padding: 10px 20px;
      background-color: #f5d442;
      color: #162447;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #e4c838;
    }

    @media (max-width: 768px) {
      .actions {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<div class="dashboard">
  <h1>Welcome, <?php echo htmlspecialchars($user['firstname']); ?> 👋</h1>

  <div class="intro">
    <p>You're now inside the Seller Dashboard. Here you can manage your product listings, track customer orders, and grow your online store.</p>
    <p>🚀 <strong>Tips:</strong> Ensure your product images are clear and your stock levels are accurate. Respond quickly to orders for a better customer experience!</p>
  </div>

  <div class="actions">
    <div class="card">
      <h2>➕ Add a New Product</h2>
      <p>Create listings for the items you want to sell. Be sure to include all relevant details and upload quality photos.</p>
      <a href="add-product.php" class="btn">Add Product</a>
    </div>

<?php if (!empty($user['logo_path'])): ?>
  <div style="text-align: center; margin-bottom: 15px;">
    <img src="../<?php echo htmlspecialchars($user['logo_path']); ?>" alt="Shop Logo" style="max-height: 100px; border-radius: 8px;">
  </div>
<?php endif; ?>

<div class="card">
  <h2>🖼️ Upload Shop Logo</h2>
  <p>This logo will appear on your shop profile.</p>
<form id="logoForm" enctype="multipart/form-data">
  <input type="file" name="shop_logo" accept="image/*" required><br><br>
  <button class="btn" type="submit">Upload Logo</button>
</div>
</form>



    <div class="card">
      <h2>📋 View My Listings</h2>
      <p>See all products you’ve uploaded so far. You can update stock, edit details, or remove items here.</p>
      <a href="my-products.php" class="btn">View My Listings</a>
    </div>

    <div class="card">
      <h2>📦 Orders Received</h2>
      <p>Track customer orders placed for your products. Update order status and shipping info here.</p>
      <a href="orders.php" class="btn">View Orders</a>
    </div>

    <div class="card">
      <h2>🛍️ Back to Shopping</h2>
      <p>Want to browse and buy like a regular user? Head back to the main shopping page anytime.</p>
      <a href="../homepage.php" class="btn">Return to Shop</a>
    </div>
  </div>
</div>


<script>
document.getElementById('logoForm').addEventListener('submit', function(e) {
  e.preventDefault(); // Prevent default form behavior (page reload)

  const formData = new FormData(this);

  fetch('upload-shop-logo.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(response => {
    alert(response.trim() === 'Success' ? 'Logo uploaded successfully!' : response);
    location.reload(); // Refresh to show updated logo
  })
  .catch(err => {
    alert('Upload failed.');
    console.error(err);
  });
});
</script>

</body>
</html>
