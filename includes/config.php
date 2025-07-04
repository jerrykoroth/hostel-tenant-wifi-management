<?php
$host = "localhost";
$dbname = "hostel";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh = $pdo; // Add this line to define $dbh as alias for $pdo
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

