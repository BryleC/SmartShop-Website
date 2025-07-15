<?php
session_start();
include '../connect.php';
include '../burgermenu.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT p.product_id, p.product_name, p.price, p.image_path, p.category, p.has_sizes,
               p.stock, p.stock_s, p.stock_m, p.stock_l, p.stock_xl,
               s.saved_at
        FROM saved s
        JOIN products p ON s.product_id = p.product_id
        WHERE s.user_id = ?
        ORDER BY s.saved_at DESC";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Saved Items</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background-color: #f0f2f5;
    }
    header {
      background-color: #000;
      color: white;
      text-align: center;
      padding: 30px 0;
      font-size: 40px;
      font-weight: 700;
    }
    .saved-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 20px;
      padding: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.2s ease;
      cursor: pointer;
      position: relative;
    }
    .card:hover { transform: translateY(-5px); }
    .card img {
      width: 100%;
      height: 240px;
      object-fit: cover;
    }
    .card-body {
      padding: 15px;
      text-align: center;
    }
    .card-body h3 {
      margin: 10px 0 5px;
      font-size: 18px;
    }
    .card-body p {
      margin: 0;
      color: #666;
      font-size: 15px;
      font-weight: bold;
    }
    .card-body button {
      margin-top: 10px;
      padding: 8px 16px;
      background-color: #2980b9;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .card-body button:hover {
      opacity: 0.9;
    }
    #toast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: #27ae60;
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      display: none;
      font-size: 14px;
      z-index: 1000;
    }
    #productModal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.7);
      z-index: 2000;
      align-items: center;
      justify-content: center;
    }
#modalContent {
  background: white;
  width: 90%;
  max-width: 800px;
  height: 450px; /* Fixed height for consistency */
  padding: 20px;
  border-radius: 10px;
  display: flex;
  gap: 20px;
  position: relative; 
  box-sizing: border-box;
}

#modalImage {
  width: 50%;
  height: 100%; /* Match the fixed height */
  object-fit: contain; /* Maintain image ratio inside fixed box */
  border-radius: 8px;
  background-color: #f6f6f6;
}

    #modalContent h2 {
      margin-top: 0;
    }
    #modalClose {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 24px;
      cursor: pointer;
    }
    
    h1{
      text-align: center;
      margin: 20px 0;
    }
  </style>
</head>
<body>
  <header>ShopSmart</header>
  <div class="saved-container">
  <?php
    if ($result->num_rows === 0) {
        echo "<p style='text-align:center; padding:40px; font-size:18px; color:#999;'>You haven’t saved any items yet.</p>";
    } else {
        while ($row = $result->fetch_assoc()) {
          
            $id = $row['product_id'];
            $name = htmlspecialchars($row['product_name']);
            $price = number_format($row['price'], 2);
            $image = htmlspecialchars($row['image_path']) . '?v=' . time();

            $in_stock = 0;
if ($row['has_sizes'] == '1') {
    $in_stock += (int)$row['stock_s'];
    $in_stock += (int)$row['stock_m'];
    $in_stock += (int)$row['stock_l'];
    $in_stock += (int)$row['stock_xl'];
} else {
    $in_stock = (int)$row['stock'];
}


            echo "<div class='card' onclick=\"openModal($id, '$name', {$row['price']}, '$image', '{$row['category']}', '{$row['has_sizes']}', {$row['stock']}, {$row['stock_s']}, {$row['stock_m']}, {$row['stock_l']}, {$row['stock_xl']})\">";
            echo "<img src='$image?v=" . time() . "' alt='$name'>";
            echo "<div class='card-body'>";
            echo "<h3>$name</h3>";
            echo "<p>₱$price</p>";
            echo "<p>Stock: $in_stock</p>";
            echo "<form class='add-to-cart-form' data-id='$id' data-qty='1'>";
            echo "<input type='hidden' name='product_id' value='$id'>";
            echo "<input type='hidden' name='quantity' value='1'>";
            echo "<button type='submit'>Add to Cart</button>";
            echo "</form>";
            echo "</div></div>";
        }
    }
    $stmt->close();
  ?>
  </div>

  <!-- Toast -->
  <div id="toast">Item added to cart!</div>

  <!-- Product Modal -->
  <div id="productModal">
    <div id="modalContent">
      <span id="modalClose">&times;</span>
      <img id="modalImage" src="" alt="Product Image">
      <div style="flex:1;">
        <h2 id="modalName"></h2>
        <p id="modalPrice"></p>

<div id="sizeWrapper">
  <label for="modalSize">Size:</label>
  <select id="modalSize" name="size">
    <option value="">Select size</option>
    <option value="S">S</option>
    <option value="M">M</option>
    <option value="L">L</option>
    <option value="XL">XL</option>
  </select><br>

    <p id="stockNote" style="font-size: 14px; color: #888;"></p>

</div>



        <label for="modalQty">Quantity:</label>
        <input type="number" id="modalQty" min="1" value="1" style="width:60px; margin-bottom:15px;"><br>

        <form id="modalForm">
          <input type="hidden" id="modalProductId" name="product_id">
          <button type="submit">Add to Cart</button>
        </form>
      </div>
    </div>
  </div>

<script>
document.querySelectorAll('.add-to-cart-form').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const productId = this.getAttribute('data-id');
    const quantity = this.getAttribute('data-qty');
    let size = "M";
    const sizeInput = this.querySelector('select[name="size"]');
    if (sizeInput) size = sizeInput.value;

    fetch('../Ecommerce/add-to-cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `product_id=${productId}&quantity=${quantity}&size=${encodeURIComponent(size)}`
    })
    .then(res => res.text())
    .then(() => {
      const toast = document.getElementById('toast');
      toast.style.display = 'block';
      setTimeout(() => toast.style.display = 'none', 3000);
    })
    .catch(err => console.error(err));
  });
});

function openModal(id, name, price, image, category, hasSizes, stock, stocks, stockm, stockl, stockxl) {
  document.getElementById('productModal').style.display = 'flex';
  document.getElementById('modalName').textContent = name;
  document.getElementById('modalPrice').textContent = `₱${price.toFixed(2)}`;
  document.getElementById('modalImage').src = image;
  document.getElementById('modalProductId').value = id;
  document.getElementById('modalQty').value = 1;

  // Store global stock for size selection
  window.stocks = stocks;
  window.stockm = stockm;
  window.stockl = stockl;
  window.stockxl = stockxl;
  window.stockTotal = stock;

  const sizeWrapper = document.getElementById('sizeWrapper');
  const modalSize = document.getElementById('modalSize');

  // Show/hide size selector
  if (hasSizes === '1' || hasSizes === 'yes') {
    sizeWrapper.style.display = 'block';
    modalSize.required = true;
    modalSize.value = '';

    modalSize.onchange = () => {
      let qty = 0;
      if (modalSize.value === 'S') qty = stocks;
      if (modalSize.value === 'M') qty = stockm;
      if (modalSize.value === 'L') qty = stockl;
      if (modalSize.value === 'XL') qty = stockxl;

const stockNote = document.getElementById('stockNote');
if (qty > 0) {
  stockNote.textContent = `Stocks left for ${modalSize.value}: ${qty}`;
} else {
  stockNote.textContent = `Out of stock for size ${modalSize.value}`;
}
    };

    modalSize.dispatchEvent(new Event('change'));
  } else {
    sizeWrapper.style.display = 'none';
    modalSize.required = false;
    modalSize.value = 'none';
    if (stock <= 0) alert("Out of stock.");
  }
}



document.getElementById('modalClose').onclick = function() {
  document.getElementById('productModal').style.display = 'none';
};

document.getElementById('modalForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const productId = document.getElementById('modalProductId').value;
  const quantity = parseInt(document.getElementById('modalQty').value);
  const size = document.getElementById('modalSize').value;

  const sizeVisible = document.getElementById('sizeWrapper').style.display !== 'none';

  // Validate quantity vs stock
  let available = 0;
  if (sizeVisible) {
    if (!size) {
      alert("Please select a size.");
      return;
    }
    if (size === 'S') available = stocks;
    if (size === 'M') available = stockm;
    if (size === 'L') available = stockl;
    if (size === 'XL') available = stockxl;
  } else {
    available = stockTotal;
  }

  if (quantity > available) {
    alert(`Only ${available} item(s) in stock for your selection.`);
    return;
  }

  // Proceed to add to cart
  fetch('../Ecommerce/add-to-cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}&quantity=${quantity}&size=${encodeURIComponent(size)}`
  })
  .then(() => {
    document.getElementById('productModal').style.display = 'none';
    const toast = document.getElementById('toast');
    toast.style.display = 'block';
    setTimeout(() => toast.style.display = 'none', 3000);
  })
  .catch(err => console.error(err));
});



</script>
</body>
</html>
