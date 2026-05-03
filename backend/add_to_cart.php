<?php
session_start();
include 'db.php'; // Ensure this points to your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // 1. Save to PHP Session (Temporary)
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = $product_id;

    // 2. Save to Database Table (Persistent)
    if ($user_id) {
        try {
            // Check if item already exists in cart for this user
            $check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $check->execute([$user_id, $product_id]);
            $item = $check->fetch();

            if ($item) {
                // If it exists, just increase the quantity
                $update = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
                $update->execute([$item['id']]);
            } else {
                // If it's new, insert a fresh row
                $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $insert->execute([$user_id, $product_id]);
            }
        } catch (PDOException $e) {
            // Log error but don't stop the script
            error_log("Database Cart Error: " . $e->getMessage());
        }
    }

    // Return the total count for the "My Cart (X)" display
    echo count($_SESSION['cart']);
}