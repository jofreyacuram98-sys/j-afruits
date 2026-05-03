<?php
session_start();
include '../backend/db.php'; // Path to your main db connection

// Security Check: If not logged in as admin, kick back to login
function checkAdminSession() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: index.php");
        exit();
    }
}

// Global path helper (since you're in a subfolder)
$base_url = "../"; 
?>