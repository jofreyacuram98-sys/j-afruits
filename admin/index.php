<?php
session_start();
include '../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$user]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        header("Location: admin-dashboard.php");
        exit();
    } else {
        $error = "Invalid Administrative Credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - J.A Fruits</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="background: #2c3e50; display: flex; justify-content: center; align-items: center; height: 100vh;">
    <div class="glass-card" style="width: 350px; padding: 40px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 20px; color: white;">
        <h2 style="text-align: center; margin-bottom: 20px;">J.A ADMIN</h2>
        
        <?php if(isset($error)): ?>
            <p style="color: #ff7675; font-size: 13px; text-align: center;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-row">
                <label>Admin Username</label>
                <input type="text" name="username" required style="width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: none;">
            </div>
            <div class="form-row">
                <label>Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: none;">
            </div>
            <button type="submit" class="rectangle auth-btn" style="width: 100%; margin-top: 20px;">Login to Panel</button>
        </form>
    </div>
</body>
</html>