<?php
// backend/fetch_products.php
include 'db.php'; 

// Get parameters from the AJAX request
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$category_id = isset($_GET['cat']) && is_numeric($_GET['cat']) ? (int)$_GET['cat'] : null;
$limit = 6;

// Build the SQL query dynamically
$sql = "SELECT * FROM products";
$params = [];

if ($category_id) {
    $sql .= " WHERE category_id = :cat";
}

$sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

// Bind values
if ($category_id) {
    $stmt->bindValue(':cat', $category_id, PDO::PARAM_INT);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$new_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($new_products) {
    foreach ($new_products as $product) {
        $is_out_of_stock = ($product['stock'] <= 0);
        
        echo '
        <div class="product-card">
            <img src="'.htmlspecialchars($product['image_path']).'" alt="'.htmlspecialchars($product['name']).'">
            <h3>'.htmlspecialchars($product['name']).'</h3>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <p style="margin:0;">₱'.number_format($product['price'], 2).' / kg</p>
                <span style="font-size: 12px; color: '.($is_out_of_stock ? "#e74c3c" : "#2ecc71").';">
                    '.($is_out_of_stock ? "Sold Out" : $product['stock']." left").'
                </span>
            </div>';

        if ($is_out_of_stock) {
            echo '<button class="rectangle" style="background: #ccc; cursor: not-allowed;" disabled>Out of Stock</button>';
        } else {
            echo '<button class="add-to-cart-btn rectangle" data-id="'.$product['id'].'">
                    Add to Cart
                  </button>';
        }
        
        echo '</div>';
    }
}
?>