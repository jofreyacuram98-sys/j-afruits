<?php 
include 'admin-auth.php'; 

// --- ADMIN: HANDLE ORDER STATUS UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = trim($_POST['status']);

    $update_stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $order_id]);

    header("Location: admin-dashboard.php?mod=orders");
    exit();
}

$mod = $_GET['mod'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - J.A Fruits</title>
    <link rel="stylesheet" href="../globals.css">
    <link rel="stylesheet" href="../style.css">
  <style>
    .admin-container { 
        display: flex; 
        min-height: 100vh; 
        /* Adds a dark, semi-transparent gradient over your background image */
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                    url('../images/full-frame-of-a-variety-of-fruits-royalty-free-image-1747747836.jpg') no-repeat center center fixed; 
        background-size: cover;
        color: #fff; /* Ensures text is light to contrast with the dark overlay */
    }
    
    .admin-sidebar { 
        width: 260px; 
        background: rgba(44, 62, 80, 0.85); /* Semi-transparent solid color */
        color: white; 
        padding: 20px; 
    }
    
    .admin-sidebar h2 { 
        color: var(--primary-green); 
        margin-bottom: 30px; 
        text-align: center; 
    }
    
    .admin-nav { 
        list-style: none; 
        padding: 0; 
    }
    
    .admin-nav li a { 
        display: block; 
        padding: 12px; 
        color: #bdc3c7; 
        text-decoration: none; 
        border-radius: 8px; 
        margin-bottom: 5px; 
    }
    
    .admin-nav li a:hover, .admin-nav li a.active { 
        background: #34495e; 
        color: white; 
    }
    
    .admin-content { 
        flex: 1; 
        padding: 40px; 
        overflow-y: auto; 
        background: rgba(244, 247, 246, 0.9); /* Slight transparency for the container to let the background peak through */
        border-radius: 10px;
        margin: 20px; /* Separates the content box from the edges */
        color: #333; /* Dark text for readability on light content panel */
    }
    
    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        background: white; 
        border-radius: 10px; 
        overflow: hidden; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.15); 
    }
    
    .data-table th { 
        background: #f8f9fa; 
        padding: 15px; 
        text-align: left; 
        border-bottom: 2px solid #eee; 
    }
    
    .data-table td { 
        padding: 15px; 
        border-bottom: 1px solid #eee; 
    }
    
    .btn-sm { 
        padding: 5px 10px; 
        font-size: 12px; 
        border-radius: 5px; 
        cursor: pointer; 
        border: none; 
    }
    
    .btn-edit { 
        background: #3498db; 
        color: white; 
    }
    
    .btn-view { 
        background: #2ecc71; 
        color: white; 
    }
</style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>J.A ADMIN</h2>
            <ul class="admin-nav">
                <li><a href="?mod=dashboard" class="<?= $mod=='dashboard'?'active':'' ?>">Dashboard</a></li>
                <li><a href="?mod=products" class="<?= $mod=='products'?'active':'' ?>">Products</a></li>
                <li><a href="?mod=categories" class="<?= $mod=='categories'?'active':'' ?>">Categories</a></li>
                <li><a href="?mod=orders" class="<?= $mod=='orders'?'active':'' ?>">Orders</a></li>
                <li><a href="?mod=customers" class="<?= $mod=='customers'?'active':'' ?>">Customers</a></li>
                <li style="margin-top: 50px;"><a href="logout.php" style="color: #e74c3c;">Logout</a></li>
            </ul>
        </aside>

        <main class="admin-content">
            <?php switch($mod): 
                 case 'products': ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2>Product Management</h2>
                        <button class="rectangle btn-view" onclick="location.href='?mod=add-product'">+ Add New Fruit</button>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Fruit Name</th>
                                <th>Price</th>
                                <th>Stock Level</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
                            while($row = $stmt->fetch()): 
                                $stock_color = ($row['stock'] <= 5) ? '#e74c3c' : '#2ecc71';
                            ?>
                                <tr>
                                    <td><img src="../<?= $row['image_path'] ?>" width="40" style="border-radius: 5px;"></td>
                                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                    <td>₱<?= number_format($row['price'], 2) ?></td>
                                    <td style="color: <?= $stock_color ?>; font-weight: bold;">
                                        <?= $row['stock'] ?> units
                                    </td>
                                    <td>
                                        <?php if($row['stock'] > 0): ?>
                                            <span style="font-size: 11px; padding: 3px 8px; background: #e8f5e9; color: #2e7d32; border-radius: 10px;">In Stock</span>
                                        <?php else: ?>
                                            <span style="font-size: 11px; padding: 3px 8px; background: #ffebee; color: #c62828; border-radius: 10px;">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-sm btn-edit" onclick="location.href='?mod=edit-product&id=<?= $row['id'] ?>'">Edit</button>
                                        <button class="btn-sm" style="background:#f39c12; color:white;">Archive</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                <?php break; case 'orders': ?>
                    <h2>Customer Orders</h2>
                    <p style="color: #888; margin-bottom: 25px;">Manage customer orders and update shipping or fulfillment status below.</p>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT o.*, u.fullname FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
                            while($row = $stmt->fetch()): ?>
                                <tr>
                                    <td>#<?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                                    <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                                    <td>
                                        <span style="background: #e1f5fe; color: #0288d1; padding: 4px 8px; border-radius: 4px; font-size: 13px;">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="admin-dashboard.php?mod=orders" method="POST" style="display: flex; gap: 8px; align-items: center;">
                                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                            <select name="status" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
                                                <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Processing" <?= $row['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                                <option value="Shipped" <?= $row['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="Delivered" <?= $row['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="Cancelled" <?= $row['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_order_status" class="btn-sm" style="background: var(--primary-green); color: white;">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                <?php break; case 'customers': ?>
                    <h2>Registered Customers</h2>
                    
                    <?php if (isset($_GET['delete_id'])): 
                        $del_id = $_GET['delete_id'];
                        $delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        if ($delete->execute([$del_id])) {
                            echo "<p style='color: green; margin-bottom: 15px;'>Customer deleted successfully.</p>";
                        }
                    endif; ?>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT id, fullname, email, phone, created_at FROM users ORDER BY created_at DESC");
                            $customers = $stmt->fetchAll();

                            if (!$customers): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px; color: #888;">
                                        No registered customers found.
                                    </td>
                                </tr>
                            <?php else: 
                                foreach($customers as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                                        <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
                                        <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                        <td>
                                            <a href="?mod=customers&delete_id=<?= $row['id'] ?>" 
                                            onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.')" 
                                            style="color: #e74c3c; text-decoration: none; font-size: 13px; font-weight: bold;">
                                            Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; 
                            endif; ?>
                        </tbody>
                    </table>

                <?php break; case 'categories': ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2>Product Categories</h2>
                    </div>

                    <?php
                    // --- LOGIC: ADD CATEGORY ---
                    if (isset($_POST['add_category'])) {
                        $cat_name = trim($_POST['cat_name']);
                        if (!empty($cat_name)) {
                            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                            try {
                                $stmt->execute([$cat_name]);
                                echo "<p style='color: green;'>Category '$cat_name' added!</p>";
                            } catch (PDOException $e) {
                                echo "<p style='color: red;'>Error: Category might already exist.</p>";
                            }
                        }
                    }

                    // --- LOGIC: DELETE CATEGORY ---
                    if (isset($_GET['delete_cat'])) {
                        $cat_id = $_GET['delete_cat'];
                        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                        $stmt->execute([$cat_id]);
                        echo "<p style='color: orange;'>Category deleted.</p>";
                    }
                    ?>

                    <div style="display: flex; gap: 30px;">
                        <div style="flex: 1; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); height: fit-content;">
                            <h3>Add New Category</h3>
                            <form method="POST" style="margin-top: 15px;">
                                <div class="form-row">
                                    <label>Category Name</label>
                                    <input type="text" name="cat_name" required placeholder="e.g. Berries" 
                                        style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px;">
                                </div>
                                <button type="submit" name="add_category" class="rectangle btn-view" style="width: 100%; margin-top: 15px;">
                                    Save Category
                                </button>
                            </form>
                        </div>

                        <div style="flex: 2;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category Name</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                                    $cats = $stmt->fetchAll();
                                    if (!$cats): ?>
                                        <tr><td colspan="4" style="text-align:center;">No categories found.</td></tr>
                                    <?php else: foreach ($cats as $cat): ?>
                                        <tr>
                                            <td>#<?= $cat['id'] ?></td>
                                            <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                            <td><?= date('M d, Y', strtotime($cat['created_at'])) ?></td>
                                            <td>
                                                <a href="?mod=categories&delete_cat=<?= $cat['id'] ?>" 
                                                onclick="return confirm('Delete this category? Products in this category might lose their link.')"
                                                style="color: #e74c3c; text-decoration: none; font-size: 13px;">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php break; case 'add-product': ?>
                    <div style="max-width: 800px; margin: 0 auto;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h2>Add New Fruit Product</h2>
                            <a href="?mod=products" style="color: #888; text-decoration: none;">← Back to List</a>
                        </div>

                        <?php
                        if (isset($_POST['save_product'])) {
                            $name = $_POST['name'];
                            $cat_id = $_POST['category_id'];
                            $price = $_POST['price'];
                            $stock = $_POST['stock'];
                            $desc = $_POST['description'];

                            $target_dir = "../assets/products/";
                            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                            
                            $file_name = time() . "_" . basename($_FILES["image"]["name"]);
                            $target_file = $target_dir . $file_name;
                            $db_path = "assets/products/" . $file_name;

                            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image_path) VALUES (?, ?, ?, ?, ?, ?)");
                                if ($stmt->execute([$cat_id, $name, $desc, $price, $stock, $db_path])) {
                                    echo "<div style='background:#d4edda; color:#155724; padding:15px; border-radius:10px; margin-bottom:20px;'>Fruit added successfully!</div>";
                                }
                            } else {
                                echo "<p style='color:red;'>Failed to upload image. Check folder permissions.</p>";
                            }
                        }
                        ?>

                        <form method="POST" enctype="multipart/form-data" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-row">
                                    <label>Fruit Name</label>
                                    <input type="text" name="name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                </div>
                                <div class="form-row">
                                    <label>Category</label>
                                    <select name="category_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                        <option value="">Select Category</option>
                                        <?php
                                        $cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                                        foreach($cats as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-row">
                                    <label>Price (₱)</label>
                                    <input type="number" step="0.01" name="price" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                </div>
                                <div class="form-row">
                                    <label>Initial Stock</label>
                                    <input type="number" name="stock" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                </div>
                            </div>

                            <div class="form-row" style="margin-top:20px;">
                                <label>Product Image</label>
                                <input type="file" name="image" accept="image/*" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                            </div>

                            <div class="form-row" style="margin-top:20px;">
                                <label>Description</label>
                                <textarea name="description" rows="4" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></textarea>
                            </div>

                            <button type="submit" name="save_product" class="rectangle btn-view" style="width: 100%; margin-top: 30px; height: 50px;">
                                Upload & Publish Product
                            </button>
                        </form>
                    </div>

                <?php break; default: ?>
                    <h2>Store Overview</h2>
                    
                    <?php
                    $total_sales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
                    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
                    $total_customers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    $low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn();
                    ?>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #2ecc71;">
                            <p style="color: #888; font-size: 14px; margin-bottom: 10px;">Total Revenue</p>
                            <h3 style="font-size: 24px;"><?= number_format($total_sales ?? 0) ?></h3>
                        </div>

                        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #3498db;">
                            <p style="color: #888; font-size: 14px; margin-bottom: 10px;">Total Orders</p>
                             <h3 style="font-size: 24px;"><?= $total_orders ?></h3>
                        </div>

                        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #9b59b6;">
                            <p style="color: #888; font-size: 14px; margin-bottom: 10px;">Customers</p>
                            <h3 style="font-size: 24px;"><?= $total_customers ?></h3>
                        </div>

                        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #e74c3c;">
                            <p style="color: #888; font-size: 14px; margin-bottom: 10px;">Low Stock Items</p>
                            <h3 style="font-size: 24px; color: #e74c3c;"><?= $low_stock ?></h3>
                        </div>
                    </div>

                    <div style="margin-top: 40px; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h3>Recent Orders</h3>
                            <a href="?mod=orders" style="color: var(--primary-green); text-decoration: none; font-size: 14px;">View All</a>
                        </div>
                        <table class="data-table" style="box-shadow: none;">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent = $pdo->query("SELECT o.*, u.fullname FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 5");
                                foreach($recent as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['fullname']) ?></td>
                                        <td><?= date('M d', strtotime($r['order_date'])) ?></td>
                                        <td>₱<?= number_format($r['total_amount'], 2) ?></td>
                                        <td><span style="font-size: 12px; padding: 4px 8px; border-radius: 5px; background: #eee;"><?= $r['status'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
            <?php endswitch; ?>
        </main>
    </div>
</body>
</html>
