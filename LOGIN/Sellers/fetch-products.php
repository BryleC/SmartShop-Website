<?php
session_start();
include '../connect.php';

$user_id = $_SESSION['user_id'] ?? null;
$saved_ids = [];

if ($user_id) {
    $savedQuery = $conn->prepare("SELECT product_id FROM saved WHERE user_id = ?");
    $savedQuery->bind_param("i", $user_id);
    $savedQuery->execute();
    $savedResult = $savedQuery->get_result();
    while ($row = $savedResult->fetch_assoc()) {
        $saved_ids[] = $row['product_id'];
    }
}

if (!isset($_GET['seller']) || (int)$_GET['seller'] <= 0) {
    exit;
}

$seller_id = (int)$_GET['seller'];
$search = isset($_GET['q']) ? $_GET['q'] : '';

$sql = "SELECT * FROM products WHERE seller_id = ? AND status = 'available'";
$params = [$seller_id];
$types = "i";

if ($search !== '') {
    $sql .= " AND product_name LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= "s";
}

$sql .= " ORDER BY product_name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

while ($p = $products->fetch_assoc()) {
    $imagePath = $p['image_path'];
    if (stripos($imagePath, '/login/') === false) {
        $imagePath = "../" . ltrim($imagePath, '/');
    }

    $is_saved = in_array($p['product_id'], $saved_ids);

    if ((int)$p['has_sizes'] === 1) {
        $in_stock = (int)$p['stock_s'] + (int)$p['stock_m'] + (int)$p['stock_l'] + (int)$p['stock_xl'];
    } else {
        $in_stock = (int)$p['stock'];
    }

    ?>
    <div class="card product-card"
        data-id="<?= $p['product_id'] ?>"
        data-name="<?= htmlspecialchars($p['product_name'], ENT_QUOTES) ?>"
        data-price="<?= $p['price'] ?>"
        data-image="<?= $imagePath ?>?v=<?= time() ?>"
        data-stock="<?= $in_stock ?>"
        data-description="<?= htmlspecialchars($p['description'], ENT_QUOTES) ?>"
        data-has-sizes="<?= $p['has_sizes'] ?>"
        data-stock-s="<?= (int)$p['stock_s'] ?>"
        data-stock-m="<?= (int)$p['stock_m'] ?>"
        data-stock-l="<?= (int)$p['stock_l'] ?>"
        data-stock-xl="<?= (int)$p['stock_xl'] ?>"
        data-seller-id="<?= (int)$p['seller_id'] ?>"
    >
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
                <button class="open-modal-btn" disabled style="background: grey; cursor: not-allowed;">Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>
