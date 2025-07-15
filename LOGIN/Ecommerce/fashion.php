<?php
session_start();
include '../connect.php'; 
include '../burgermenu.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$saved_ids = [];

// Fetch saved product IDs
$stmt = $conn->prepare("SELECT product_id FROM saved WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $saved_ids[] = $row['product_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fashion & Apparel - ShopSmart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    .products {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 20px;
      padding: 40px;
    }
    .card {
      position: relative;
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: transform 0.2s ease;
    }
    .card:hover { transform: translateY(-5px); }
    .card img {
      width: 100%;
      height: 240px;
      object-fit: cover;
      cursor: pointer;
    }
    .card-body {
      padding: 15px;
      text-align: center;
    }
    .card-body h3 { margin: 10px 0 5px; font-size: 18px; }
    .card-body p { margin: 0; color: #666; font-size: 15px; }
    .card-body button {
      margin: 5px;
      padding: 8px 16px;
      background-color:rgb(255, 153, 0);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .wishlist-form {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 1;
      display: none;
    }
    .card:hover .wishlist-form[data-saved="0"] {
      display: block;
    }
    .wishlist-form[data-saved="1"] {
      display: block;
    }

    .heart-icon {
      background: transparent;
      border: none;
      font-size: 10px;
      color: #e74c3c;
      cursor: pointer;
    }

    #AddToCartToast, #SavedToast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: #27ae60;
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      display: none;
      font-size: 14px;
      z-index: 1000;
    }
    #SavedToast { background: #e74c3c; bottom: 70px; }

    #productModal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.7);
      z-index: 2000;
      justify-content: center;
      align-items: center;
    }

    #modalContent {
      background: white;
      display: flex;
      width: 80%;
      max-width: 900px;
      padding: 20px;
      border-radius: 10px;
      position: relative;
    }

    #modalImage {
      width: 100%;
      max-width: 400px;
      height: 400px;
      object-fit: contain;
      background-color: #f9f9f9;
      border-radius: 8px;
    }

    #modalForm {
      flex: 1;
      padding: 20px;
    }

    #modalForm input, #modalForm button {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 24px;
      background: none;
      border: none;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <header>Fashion & Apparel Collection</header>

  <div class="products">
    <?php
$sql = "SELECT p.*, s.shop_name, s.user_id AS seller_id 
        FROM products p 
        LEFT JOIN sellers s ON p.seller_id = s.user_id 
        WHERE p.category = 'fashion' AND p.status = 'available' 
        ORDER BY p.product_name ASC";

    $result = $conn->query($sql);

    while ($p = $result->fetch_assoc()):
      $imagePath = $p['image_path'];
      if (stripos($imagePath, '/login/') === false) {
          $imagePath = "../" . ltrim($imagePath, '/'); // adjust path as needed
      }

      $is_saved = in_array($p['product_id'], $saved_ids);
    if ($p['has_sizes'] === '1') {
    $in_stock = 0;
    $in_stock += (int)$p['stock_s'];
    $in_stock += (int)$p['stock_m'];
    $in_stock += (int)$p['stock_l'];
    $in_stock += (int)$p['stock_xl'];
} else {
    $in_stock = (int)$p['stock'];
}

    ?>
    <div class="card" onclick="openModal(
  <?= $p['product_id'] ?>, 
  '<?= htmlspecialchars($p['product_name']) ?>', 
  <?= $p['price'] ?>, 
  '<?= $imagePath ?>?v=<?= time() ?>', 
  <?= $in_stock ?>,
  '<?= htmlspecialchars($p['description']) ?>',
  '<?= $p['has_sizes'] ?>',
<?= (int)$p['stock_s'] ?>,
<?= (int)$p['stock_m'] ?>,
<?= (int)$p['stock_l'] ?>,
<?= (int)$p['stock_xl'] ?>,
<?= (int)$p['seller_id'] ?>

)">

      <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($p['product_name']) ?>">

      <form class="wishlist-form save-to-saved-form" data-id="<?= $p['product_id'] ?>" data-saved="<?= $is_saved ? '1' : '0' ?>">
        <button type="submit" class="heart-icon"><?= $is_saved ? '❤️' : '🤍' ?></button>
      </form>

      <div class="card-body">
        <h3><?= $p['product_name'] ?></h3>
        <p>₱<?= number_format($p['price'], 2) ?></p>
        <p>Stock: <?= $in_stock ?></p>

        <?php if ($in_stock > 0): ?>
          <button class="open-modal-btn"
  onclick="event.stopPropagation(); openModal(
    <?= $p['product_id'] ?>, 
    '<?= htmlspecialchars($p['product_name']) ?>', 
    <?= $p['price'] ?>, 
    '<?= $imagePath ?>?v=<?= time() ?>', 
    <?= $in_stock ?>,
    '<?= htmlspecialchars($p['description']) ?>',
    '<?= $p['has_sizes'] ?>',
<?= (int)$p['stock_s'] ?>,
<?= (int)$p['stock_m'] ?>,
<?= (int)$p['stock_l'] ?>,
<?= (int)$p['stock_xl'] ?>,
<?= (int)$p['seller_id'] ?>


  )">

            Add to Cart
          </button>
        <?php else: ?>
          <button class="open-modal-btn" disabled style="background: grey; cursor: not-allowed;">
            Out of Stock
          </button>
        <?php endif; ?>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <div id="AddToCartToast">Item added to cart!</div>
  <div id="SavedToast">Item saved!</div>

  <!-- Modal -->
  <div id="productModal">
    <div id="modalContent">
      <button class="close-btn" onclick="closeModal()">&times;</button>
      <img id="modalImage" src="" alt="Product Image">
      <form id="modalForm">
        <h2 id="modalName"></h2>
        <p id="modalPrice"></p>

      <div id="sizeSelectGroup" style="display: none;">
  <label for="modalSize">Size:</label>
  <select id="modalSize">
    <option value="">Select Size</option>
    <option value="S">S</option>
    <option value="M">M</option>
    <option value="L">L</option>
    <option value="XL">XL</option>
  </select>
</div>
<p id="stockNote" style="font-size: 14px; color: #888;"></p> <!-- 👈 Moved outside -->



        <label for="modalQty">Quantity:</label>
        <input type="number" id="modalQty" min="1" value="1">
        <input type="hidden" id="modalProductId">
        <p style="font-weight: bold; margin: 10px 0 5px;">Description:</p>
        <p id="modalDescription" style="margin-bottom: 10px; color: #555;"></p>
        <button type="submit">Add to Cart</button>
      </form>
    </div>
  </div>

  <script>
function openModal(id, name, price, image, stock, description, hasSizes, stocks, stockm, stockl, stockxl, sellerId) {
  document.getElementById('productModal').style.display = 'flex';
  document.getElementById('modalName').textContent = name;
  document.getElementById('modalPrice').textContent = `₱${price.toFixed(2)}`;
  document.getElementById('modalImage').src = image;
  document.getElementById('modalProductId').value = id;
  document.getElementById('modalQty').value = 1;
  document.getElementById('modalDescription').textContent = description;

  // Remove any previous shop link
let oldShopLink = document.getElementById('shopLink');
if (oldShopLink) oldShopLink.remove();

// Create the shop button
let shopBtn = document.createElement('a');
shopBtn.href = `/LOGIN/Sellers/shop.php?seller=${sellerId}`;
shopBtn.textContent = 'Visit Shop';
shopBtn.target = '_blank';
shopBtn.id = 'shopLink';

// Style it nicely like a button/badge
shopBtn.style.display = 'inline-block';
shopBtn.style.padding = '6px 12px';
shopBtn.style.backgroundColor = '#f5d442';
shopBtn.style.color = '#162447';
shopBtn.style.borderRadius = '6px';
shopBtn.style.fontWeight = 'bold';
shopBtn.style.textDecoration = 'none';
shopBtn.style.marginBottom = '10px';

// Insert it after the product name
const modalName = document.getElementById('modalName');
modalName.parentNode.insertBefore(shopBtn, modalName.nextSibling);



  // Save global stock values
  window.stockS = stocks;
  window.stockM = stockm;
  window.stockL = stockl;
  window.stockXL = stockxl;
  window.stockTotal = stock;

  const sizeGroup = document.getElementById('sizeSelectGroup');
  const modalSize = document.getElementById('modalSize');
  const stockNote = document.getElementById('stockNote');

  if (hasSizes === 'yes' || hasSizes === '1') {
    sizeGroup.style.display = 'block';

    modalSize.onchange = () => {
      let size = modalSize.value;
      let qty = 0;
      if (size === 'S') qty = window.stockS;
      if (size === 'M') qty = window.stockM;
      if (size === 'L') qty = window.stockL;
      if (size === 'XL') qty = window.stockXL;

      stockNote.textContent = qty > 0 ? `Stocks: ${qty} ` : 'Out of stock for this size.';
    };

    modalSize.dispatchEvent(new Event('change'));
  } else {
sizeGroup.style.display = 'none';
stockNote.textContent = stock > 0 ? `Stocks: ${stock} ` : 'Out of stock.';


  }

  const addBtn = document.querySelector('#modalForm button[type="submit"]');
  addBtn.setAttribute('data-has-sizes', hasSizes);
  addBtn.disabled = stock <= 0;
  addBtn.textContent = stock <= 0 ? 'Out of Stock' : 'Add to Cart';
  addBtn.style.backgroundColor = stock <= 0 ? 'grey' : 'rgb(255, 153, 0)';
  addBtn.style.cursor = stock <= 0 ? 'not-allowed' : 'pointer';
}


    function closeModal() {
      document.getElementById('productModal').style.display = 'none';
    }

document.getElementById('modalForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const id = document.getElementById('modalProductId').value;
  const qty = parseInt(document.getElementById('modalQty').value);
  const size = document.getElementById('modalSize').value;
  const hasSizes = document.querySelector('#modalForm button[type="submit"]').getAttribute('data-has-sizes');

  let maxStock = 0;

  if (hasSizes === 'yes' || hasSizes === '1') {
    if (size === '') {
      alert("Please select a size.");
      return;
    }

    if (size === 'S') maxStock = window.stockS;
    if (size === 'M') maxStock = window.stockM;
    if (size === 'L') maxStock = window.stockL;
    if (size === 'XL') maxStock = window.stockXL;
  } else {
    maxStock = window.stockTotal;
  }

  if (qty > maxStock) {
    alert(`Only ${maxStock} item(s) left in stock${hasSizes === 'yes' ? ' for this size' : ''}.`);
    return;
  }

  let postData = `product_id=${id}&quantity=${qty}`;
  if (hasSizes === 'yes' || hasSizes === '1') {
    postData += `&size=${size}`;
  }

  fetch('add-to-cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: postData
  }).then(() => {
    document.getElementById('AddToCartToast').style.display = 'block';
    setTimeout(() => document.getElementById('AddToCartToast').style.display = 'none', 2000);
    closeModal();
  });
});


    document.querySelectorAll('.save-to-saved-form').forEach(form => {
      form.addEventListener('click', e => e.stopPropagation());
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = this.getAttribute('data-id');
        const isSaved = this.getAttribute('data-saved') === '1';
        const action = isSaved ? 'unsave' : 'save';

        fetch('saved-item.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `product_id=${id}&action=${action}`
        }).then(() => {
          const toast = document.getElementById('SavedToast');
          toast.textContent = isSaved ? 'Item removed!' : 'Item saved!';
          toast.style.display = 'block';
          setTimeout(() => toast.style.display = 'none', 2000);

          this.setAttribute('data-saved', isSaved ? '0' : '1');
          this.querySelector('.heart-icon').textContent = isSaved ? '🤍' : '❤️';
        });
      });
    });
  </script>
</body>
</html>
