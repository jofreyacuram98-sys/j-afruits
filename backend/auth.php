<?php
// 1. Turn on error reporting so you don't get a white screen if something breaks
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$host = 'localhost';
$db   = 'ja_fruits';
$user = 'root'; 
$pass = '';     
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action === 'register') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Updated query to include new columns
        $stmt = $pdo->prepare("INSERT INTO users (fullname, username, email, phone, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $username, $email, $phone, $password]);
        
        header("Location: ../index.php?msg=registered");
        exit();
    } catch (Exception $e) {
        die("Registration Error: " . $e->getMessage());
    }

    } 
    
    elseif ($action === 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect back to index.php
            header("Location: ../index.php");
            exit();
        } else {
            // Redirect back with error
            header("Location: ../index.php?error=invalid_login");
            exit();
        }
    }
} else {
    // If someone tries to access this file directly, send them home
    header("Location: ../index.php");
    exit();
}