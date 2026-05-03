<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$total_amount = $_POST['total_amount'];
$address = $_POST['delivery_address'];
$payment = $_POST['payment_method'];

try {
    $pdo->beginTransaction();

    // 1. Create Order with Address and Payment
    // Make sure these columns exist in your 'orders' table!
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, address_id, payment_method) VALUES (?, ?, 'Pending', ?, ?)");
    // Note: We are saving the text address as a placeholder or you can use a specific ID
    $stmt->execute([$user_id, $total_amount, null, $payment]); 
    $order_id = $pdo->lastInsertId();

    // 2. Move items
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    $insertItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) 
                                 SELECT ?, id, ?, price FROM products WHERE id = ?");

    // Inside backend/process_checkout.php loop:
 foreach ($cart_items as $item) {
    // 1. Insert into order_items
    $insertItem->execute([$order_id, $item['quantity'], $item['product_id']]);

    // 2. NEW: Deduct from products table stock
    $updateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $updateStock->execute([$item['quantity'], $item['product_id']]);
 }

    // 3. Cleanup
    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
    $_SESSION['cart'] = [];

    $pdo->commit();
    
    // REDIRECT TO RECEIPT PAGE
    header("Location: ../profile.php?page=receipt&order_id=" . $order_id);

 } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Checkout Error: " . $e->getMessage());
}