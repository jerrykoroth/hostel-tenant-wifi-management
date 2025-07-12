<?php
// Database Configuration
$host = "localhost";
$dbname = "tenant_management";
$user = "root";
$pass = "";

// Security Headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Database Connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $dbh = $pdo; // Alias for compatibility
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// Utility Functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function log_activity($pdo, $user_id, $action, $description) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $description, $ip, $user_agent]);
}

function generate_receipt_number() {
    return 'RCP' . date('Ymd') . rand(1000, 9999);
}

// Constants
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);
?>

