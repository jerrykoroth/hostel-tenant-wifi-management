<?php
// Manual Configuration File
// Copy this entire content to: includes/config.php

$host = "localhost";
$dbname = "tenant_management";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbh = $pdo;
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generate_receipt_number() {
    return "RCP" . date("Ymd") . rand(1000, 9999);
}
?>