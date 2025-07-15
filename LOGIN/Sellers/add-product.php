<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$showToast = isset($_GET['success']) && $_GET['success'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Product</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background: linear-gradient(to right, #f5d442, #162447);
      min-height: 100vh;
      margin: 0;
      padding: 40px 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .form-container {
      background: #fff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      max-width: 650px;
      width: 100%;
      position: relative;
    }

    h2 {
      margin-top: 0;
      color: #162447;
    }

    label {
      display: block;
      margin-top: 20px;
      font-weight: bold;
      color: #162447;
    }

    input, textarea, select {
      width: 100%;
      padding: 10px;
      border: 2px solid #ccc;
      border-radius: 8px;
      margin-top: 6px;
      font-size: 14px;
      transition: border-color 0.3s ease;
    }

    input:focus, textarea:focus, select:focus {
      border-color: #f5d442;
      outline: none;
    }

    .btn {
      margin-top: 30px;
      background-color: #f5d442;
      color: #162447;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      font-size: 15px;
      text-decoration: none;
      display: inline-block;
    }

    .btn:hover {
      background-color: #e4c838;
    }

    .stock-group {
      margin-top: 10px;
    }

    #sizeFields input,
    #totalStockField input {
      margin-top: 5px;
    }

    .top-buttons {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    @media (max-width: 600px) {
      .top-buttons {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
      }
    }

    /* Toast Style */
    .toast {
      position: fixed;
      top: 30px;
      right: 30px;
      background-color: #4BB543;
      color: white;
      padding: 15px 25px;
      border-radius: 8px;
      font-weight: bold;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      animation: slideIn 0.4s ease, fadeOut 0.5s ease 2.5s forwards;
      z-index: 999;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeOut {
      to { opacity: 0; transform: translateY(-10px); display: none; }
    }
  </style>
</head>
<body>

<?php if ($showToast): ?>
  <div class="toast">✅ Product Added!</div>
<?php endif; ?>

<div class="form-container">
  <div class="top-buttons">
    <h2>Add New Product</h2>
    <a href="SellerDashboard.php" class="btn" style="background:#ccc; color:#162447;">⬅ Back to Dashboard</a>
  </div>

  <form method="POST" action="process-add-product.php" enctype="multipart/form-data">

    <label for="product_name">Product Name</label>
    <input type="text" name="product_name" id="product_name" required>

    <label for="description">Description</label>
    <textarea name="description" id="description" rows="3" required></textarea>

    <label for="category">Category</label>
<select name="category" required>
  <option value="">Select Category</option>
  <option value="fashion">Fashion & Apparel</option>
  <option value="gadgets">Electronics & Gadgets</option>
  <option value="household">Household & Appliances</option>
  <option value="furniture">Furniture & Fixtures</option>
  <option value="others">Others</option> <!-- ✅ New category -->
</select>


    <label for="price">Price (₱)</label>
    <input type="number" name="price" id="price" step="0.01" required>

    <label for="image">Product Image</label>
    <input type="file" name="image" id="image" accept="image/*" required>

    <label for="hasSizes">Does this product have sizes?</label>
    <select name="has_sizes" id="hasSizes" onchange="toggleStockFields()" required>
      <option value="">-- Select --</option>
      <option value="yes">Yes</option>
      <option value="no">No</option>
    </select>

    <div id="sizeFields" class="stock-group" style="display:none;">
      <label>Stock for S</label><input type="number" name="stock_s" value="0" min="0">
      <label>Stock for M</label><input type="number" name="stock_m" value="0" min="0">
      <label>Stock for L</label><input type="number" name="stock_l" value="0" min="0">
      <label>Stock for XL</label><input type="number" name="stock_xl" value="0" min="0">
    </div>

    <div id="totalStockField" class="stock-group" style="display:none;">
      <label>Total Stock</label>
      <input type="number" name="stock" value="0" min="0">
    </div>

    <button type="submit" class="btn">✅ Add Product</button>
  </form>
</div>

<script>
  
function toggleStockFields() {
  const value = document.getElementById('hasSizes').value;
  document.getElementById('sizeFields').style.display = value === 'yes' ? 'block' : 'none';
  document.getElementById('totalStockField').style.display = value === 'no' ? 'block' : 'none';
}

$category = !empty($_POST['category']) ? $_POST['category'] : 'others';

</script>

</body>
</html>
