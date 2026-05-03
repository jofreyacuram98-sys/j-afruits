<?php
session_start();
include 'backend/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page = isset($_GET['page']) ? $_GET['page'] : 'account';

// --- LOGIC: HANDLE REMOVE FROM CART ---
if (isset($_GET['remove_item'])) {
    $cart_id = $_GET['remove_item'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    
    if (isset($_SESSION['cart'])) {
        $index = array_search($_GET['prod_id'], $_SESSION['cart']);
        if ($index !== false) unset($_SESSION['cart'][$index]);
    }
    header("Location: profile.php?page=cart");
    exit();
}

// --- LOGIC: HANDLE ADDRESS ADD/DELETE/DEFAULT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $label = trim($_POST['label']);
    $full_address = trim($_POST['full_address']);
    $city = trim($_POST['city']);
    $zip_code = trim($_POST['zip_code']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // If set as default, remove default flag from existing addresses
    if ($is_default) {
        $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
    }

    if (!empty($full_address) && !empty($city)) {
        $stmt = $pdo->prepare("INSERT INTO addresses (user_id, label, full_address, city, zip_code, is_default) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $label, $full_address, $city, $zip_code, $is_default]);
        header("Location: profile.php?page=addresses");
        exit();
    }
}

if (isset($_GET['del_address'])) {
    $address_id = (int)$_GET['del_address'];
    $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    header("Location: profile.php?page=addresses");
    exit();
}

if (isset($_GET['set_default'])) {
    $address_id = (int)$_GET['set_default'];
    $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$address_id, $user_id]);
    header("Location: profile.php?page=addresses");
    exit();
}

// --- LOGIC: HANDLE SUPPORT TICKET SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($subject) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $subject, $message]);
        header("Location: profile.php?page=support&success=1");
        exit();
    }
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - J.A Fruits</title>
    <link rel="stylesheet" href="globals.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container { display: flex; gap: 30px; margin-top: 10px; min-height: 70vh; }
        .profile-sidebar { width: 250px; background: rgba(255,255,255,0.1); backdrop-filter: blur(15px); border-radius: 20px; padding: 25px; border: 1px solid rgba(255,255,255,0.2); height: fit-content; }
        .profile-content { margin-top: -10px; }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu li a { display: block; padding: 12px 15px; border-radius: 10px; color: #333; text-decoration: none; margin-bottom: 8px; transition: 0.3s; font-weight: 500; }
        .sidebar-menu li a.active { background: var(--primary-green); color: white; box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3); }
        .sidebar-menu li a:hover:not(.active) { background: rgba(255,255,255,0.5); }
        
        .profile-content { flex: 1; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-row { margin-bottom: 20px; }
        .form-row label { display: block; font-weight: 600; margin-bottom: 8px; color: #555; }
        .form-row input, .form-row textarea, .form-row select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; transition: 0.3s; box-sizing: border-box; }
        .form-row input:focus, .form-row textarea:focus, .form-row select:focus { border-color: var(--primary-green); outline: none; box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1); }
        
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid #eee; }
        .cart-item img { width: 60px; height: 60px; object-fit: contain; margin-right: 15px; }

        .address-card { background: #fdfdfd; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .ticket-history { border-left: 4px solid var(--primary-green); padding: 12px 20px; background: #f8fafc; margin-bottom: 12px; border-radius: 0 10px 10px 0; }
    </style>
</head>
<body>
    <main class="frame">
        <header>
            <div class="j-a-FARM" onclick="location.href='index.php'" style="cursor:pointer">J.A Fruits</div>
            <nav>
                <a href="index.php">Back to Shop</a>
            </nav>
        </header>

        <div class="profile-container">
            <aside class="profile-sidebar">
                <ul class="sidebar-menu">
                    <li><a href="?page=account" class="<?= $page=='account'?'active':'' ?>">My Profile</a></li>
                    <li><a href="?page=addresses" class="<?= $page=='addresses'?'active':'' ?>">Addresses</a></li>
                    <li><a href="?page=orders" class="<?= $page=='orders'?'active':'' ?>">Order History</a></li>
                    <li><a href="?page=cart" class="<?= $page=='cart'?'active':'' ?>">My Cart</a></li>
                    <li><a href="?page=support" class="<?= $page=='support'?'active':'' ?>">Support</a></li>
                    <li style="margin-top: 30px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 20px;">
                        <a href="logout.php" style="color: #e74c3c;">Logout</a>
                    </li>
                </ul>
            </aside>

            <section class="profile-content">
                <?php switch($page): 
                    case 'account': ?>
                        <h2>Personal Information</h2>
                        <p style="color: #888; margin-bottom: 30px;">Manage your account details and preferences.</p>
                        <form action="backend/update_profile.php" method="POST">
                            <div class="form-row">
                                <label>Full Name</label>
                                <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>">
                            </div>
                            <div class="form-row">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                            </div>
                            <div class="form-row">
                                <label>Phone Number</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <button type="submit" class="rectangle auth-btn">Save Changes</button>
                        </form>

                    <?php break; case 'cart': ?>
                        <h2>Checkout & Confirmation</h2>
                        <?php
                        $stmt = $pdo->prepare("SELECT c.id as cart_id, p.*, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
                        $stmt->execute([$user_id]);
                        $cart_items = $stmt->fetchAll();
                        $total = 0;
                        
                        // Fetch saved addresses for the dropdown
                        $addr_stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
                        $addr_stmt->execute([$user_id]);
                        $saved_addresses = $addr_stmt->fetchAll();
                        
                        if (empty($cart_items)): ?>
                            <div style="text-align: center; padding: 50px 0;">
                                <p>Your cart is empty.</p>
                                <a href="index.php" class="rectangle" style="text-decoration:none;">Go Shopping</a>
                            </div>
                        <?php else: ?>
                            <form action="backend/process_checkout.php" method="POST">
                                <div style="display: flex; gap: 40px; margin-top: 20px;">
                                    <div style="flex: 1;">
                                        <h3 style="font-size: 16px; margin-bottom: 15px; color: #666;">Order Summary</h3>
                                        <?php foreach($cart_items as $item): 
                                            $subtotal = $item['price'] * $item['quantity'];
                                            $total += $subtotal;
                                        ?>
                                            <div class="cart-item" style="padding: 10px 0;">
                                                <span><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</span>
                                                <strong>₱<?= number_format($subtotal, 2) ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                        <div style="margin-top: 20px; border-top: 2px solid #eee; padding-top: 15px; text-align: right;">
                                            <h3 style="color: var(--primary-green);">Total: ₱<?= number_format($total, 2) ?></h3>
                                        </div>
                                    </div>

                                    <div style="flex: 1; background: #f9f9f9; padding: 25px; border-radius: 15px;">
                                        <h3 style="font-size: 16px; margin-bottom: 20px;">Delivery Details</h3>
                                        
                                        <div class="form-row">
                                            <label>Select Delivery Address</label>
                                            <?php if (empty($saved_addresses)): ?>
                                                <p style="font-size: 13px; color: #d9534f; margin-bottom: 8px;">No saved address found. Please add one in the Addresses tab.</p>
                                                <textarea name="delivery_address" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; height: 70px;" placeholder="House No., Street, Brgy, City"></textarea>
                                            <?php else: ?>
                                                <select name="selected_address_id" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 10px; background-color: #fff;">
                                                    <option value="" disabled selected>Select Address</option>
                                                    <?php foreach ($saved_addresses as $address): ?>
                                                        <option value="<?= $address['id']; ?>" <?= $address['is_default'] ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($address['label'] . ' - ' . $address['full_address'] . ', ' . $address['city'] . ' ' . $address['zip_code']); ?> 
                                                            <?= $address['is_default'] ? '(Default)' : ''; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <p style="font-size:12px; color:#666; margin-bottom: 15px;">Want to add a new location? Go to <a href="profile.php?page=addresses" style="color: var(--primary-green);">Manage Addresses</a>.</p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="form-row">
                                            <label>Payment Method</label>
                                            <select name="payment_method" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                                                <option value="COD">Cash on Delivery (COD)</option>
                                                <option value="GCash">GCash</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                            </select>
                                        </div>

                                        <input type="hidden" name="total_amount" value="<?= $total ?>">
                                        <button type="submit" class="rectangle auth-btn" style="width: 100%; margin-top: 10px;">
                                            Confirm & Place Order
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php break; ?>

                    <?php break; case 'addresses': ?>
                        <h2>Manage Addresses</h2>
                        <p style="color: #888; margin-bottom: 25px;">Add or update your delivery addresses.</p>

                        <form action="profile.php?page=addresses" method="POST" style="margin-bottom: 30px; background: #fafafa; padding: 20px; border-radius: 12px;">
                            <div class="form-row">
                                <label>Address Label (e.g., Home, Office)</label>
                                <input type="text" name="label" placeholder="Home" required>
                            </div>
                            <div class="form-row">
                                <label>Full Address (Street, House No.)</label>
                                <input type="text" name="full_address" placeholder="Street name, building, floor" required>
                            </div>
                            <div class="form-row">
                                <label>City</label>
                                <input type="text" name="city" placeholder="Pagadian City" required>
                            </div>
                            <div class="form-row">
                                <label>ZIP Code</label>
                                <input type="text" name="zip_code" placeholder="7016" required>
                            </div>
                            <div class="form-row" style="display: flex; align-items: center;">
                                <input type="checkbox" name="is_default" id="is_default" value="1" style="width: auto; margin-right: 10px;">
                                <label for="is_default" style="margin-bottom: 0; font-weight: 500;">Set as default delivery address</label>
                            </div>
                            <input type="hidden" name="add_address" value="1">
                            <button type="submit" class="rectangle" style="background:var(--primary-green); color:white; border:none; padding:12px 25px; border-radius:8px; cursor:pointer;">
                                Add Address
                            </button>
                        </form>

                        <div>
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
                            $stmt->execute([$user_id]);
                            $addresses = $stmt->fetchAll();
                            if (empty($addresses)):
                            ?>
                                <p style="text-align:center; padding:30px; color:#999;">No saved addresses found.</p>
                            <?php else: foreach ($addresses as $addr): ?>
                                <div class="address-card" style="border-left: <?= $addr['is_default'] ? '4px solid var(--primary-green)' : '1px solid #e2e8f0' ?>;">
                                    <div>
                                        <strong><?= htmlspecialchars($addr['label']); ?></strong>
                                        <?php if ($addr['is_default']): ?>
                                            <span style="font-size:11px; background:#e6f4ea; color:#137333; padding:2px 6px; border-radius:4px; margin-left:8px;">Default</span>
                                        <?php endif; ?>
                                        <p style="color:#666; margin-top:4px;"><?= htmlspecialchars($addr['full_address']); ?>, <?= htmlspecialchars($addr['city']); ?> - <?= htmlspecialchars($addr['zip_code']); ?></p>
                                    </div>
                                    <div style="display:flex; gap: 15px;">
                                        <?php if (!$addr['is_default']): ?>
                                            <a href="profile.php?page=addresses&set_default=<?= $addr['id']; ?>" style="color:var(--primary-green); text-decoration:none; font-weight:600;">Set Default</a>
                                        <?php endif; ?>
                                        <a href="profile.php?page=addresses&del_address=<?= $addr['id']; ?>" style="color:#e74c3c; text-decoration:none; font-weight:600;">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>

                    <?php break; case 'orders': ?>
                        <h2>Order History</h2>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
                        $stmt->execute([$user_id]);
                        $orders = $stmt->fetchAll();

                        if (isset($_GET['success'])): ?>
                            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                                Order placed successfully! Your fresh fruits are on the way.
                            </div>
                        <?php endif; ?>

                        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
                            <tr style="border-bottom: 2px solid #eee; text-align: left; color: #888;">
                                <th style="padding: 15px;">Order ID</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="4" style="padding: 40px; text-align: center;">No orders found.</td></tr>
                            <?php else: foreach ($orders as $order): ?>
                                <tr style="border-bottom: 1px solid #f9f9f9;">
                                    <td style="padding: 15px;">#<?= $order['id'] ?></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td><span class="status-badge" style="background: #e1f5fe; color: #0288d1;"><?= $order['status'] ?></span></td>
                                    <td style="text-align: right; font-weight: bold;">₱<?= number_format($order['total_amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </table>

                    <?php break; case 'support': ?>
                        <h2>Support Desk</h2>
                        <p style="color: #888; margin-bottom: 25px;">Submit a ticket and our team will get back to you.</p>

                        <?php if (isset($_GET['success'])): ?>
                            <div style="background: #e6f4ea; color: #137333; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #c8e6c9;">
                                Your ticket has been submitted successfully!
                            </div>
                        <?php endif; ?>

                        <form action="profile.php?page=support" method="POST" style="background: #fafafa; padding:25px; border-radius:12px; margin-bottom: 35px;">
                            <div class="form-row">
                                <label>Subject</label>
                                <input type="text" name="subject" placeholder="e.g., Delivery Delay, Product Quality Issue" required>
                            </div>
                            <div class="form-row">
                                <label>Message Details</label>
                                <textarea name="message" rows="5" placeholder="Please describe the problem here..." required></textarea>
                            </div>
                            <input type="hidden" name="submit_ticket" value="1">
                            <button type="submit" class="rectangle" style="background:var(--primary-green); color:white; border:none; padding:14px 28px; border-radius:8px; cursor:pointer;">
                                Submit Ticket
                            </button>
                        </form>

                        <h3>Your Active Tickets</h3>
                        <div style="margin-top: 15px;">
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
                            $stmt->execute([$user_id]);
                            $tickets = $stmt->fetchAll();
                            if (empty($tickets)):
                            ?>
                                <p style="color:#999; text-align: center; padding: 20px;">No support tickets found.</p>
                            <?php else: foreach ($tickets as $tkt): ?>
                                <div class="ticket-history">
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                                        <strong><?= htmlspecialchars($tkt['subject']); ?></strong>
                                        <span style="font-size:12px; background:#e0f2fe; color:#0284c7; padding:2px 8px; border-radius: 10px;">
                                            <?= $tkt['status']; ?>
                                        </span>
                                    </div>
                                    <p style="font-size:14px; color:#4a5568;"><?= htmlspecialchars($tkt['message']); ?></p>
                                    <small style="color:#a0aec0;"><?= date('M d, Y', strtotime($tkt['created_at'])); ?></small>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>

                    <?php break; case 'receipt': ?>
                        <?php
                        $order_id = $_GET['order_id'];
                        // Fetch the order details along with the associated delivery address
                        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
                        $stmt->execute([$order_id, $user_id]);
                        $order = $stmt->fetch();

                        if (!$order): echo "Order not found."; break; endif;
                        ?>

                        <div style="max-width: 500px; margin: 0 auto; padding: 30px; border: 2px solid #eee; border-radius: 15px; background: #fff;">
                            <div style="text-align: center; margin-bottom: 20px;">
                                <h1 style="color: var(--primary-green);">J.A Fruits</h1>
                                <p style="color: #888;">Order Receipt #<?= $order['id'] ?></p>
                            </div>

                            <hr style="border: 0; border-top: 1px dashed #eee; margin: 20px 0;">

                            <div style="margin-bottom: 20px;">
                                <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></p>
                                <p><strong>Status:</strong> <?= $order['status'] ?></p>
                                <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'COD') ?></p>
                                <p><strong>Delivery Address:</strong><br>
                                    <span style="color: #4a5568;">
                                        <?= htmlspecialchars($order['delivery_address'] ?? 'N/A'); ?>
                                    </span>
                                </p>
                            </div>

                            <table width="100%" style="margin-bottom: 20px;">
                                <?php
                                $items = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE order_id = ?");
                                $items->execute([$order_id]);
                                foreach($items->fetchAll() as $item): ?>
                                    <tr>
                                        <td style="padding: 5px 0;"><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</td>
                                        <td style="text-align: right;">₱<?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>

                            <div style="border-top: 2px solid #333; padding-top: 10px; display: flex; justify-content: space-between;">
                                <strong>GRAND TOTAL</strong>
                                <strong style="color: var(--primary-green);">₱<?= number_format($order['total_amount'], 2) ?></strong>
                            </div>

                            <div style="text-align: center; margin-top: 30px;">
                                <button onclick="window.print()" class="rectangle" style="background: #333; margin-bottom: 10px;">Print Receipt</button><br>
                                <a href="index.php" style="color: #888; font-size: 14px;">Return to Shop</a>
                            </div>
                        </div>

                    <?php break; default: ?>
                        <h2>Coming Soon</h2>
                        <p>This module is under maintenance.</p>
                <?php endswitch; ?>
            </section>
        </div>
    </main>
</body>
</html>