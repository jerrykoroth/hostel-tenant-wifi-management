<?php
// Very Simple Setup Script
if (isset($_POST['setup'])) {
    $host = $_POST['host'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $database = $_POST['database'];
    
    try {
        // Create database connection
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
        
        // Connect to the new database
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        
        // Create basic tables
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS hostels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            address TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hostel_id INT DEFAULT 1,
            room_number VARCHAR(20) NOT NULL,
            capacity INT DEFAULT 1,
            monthly_rent DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS tenants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            phone VARCHAR(20) NOT NULL,
            address TEXT,
            room_id INT,
            monthly_rent DECIMAL(10,2) DEFAULT 0.00,
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert sample data
        $pdo->exec("INSERT IGNORE INTO hostels (name, address) VALUES ('Demo Hostel', '123 Main Street')");
        $pdo->exec("INSERT IGNORE INTO users (name, email, password_hash, role) VALUES ('Admin', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
        $pdo->exec("INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, monthly_rent) VALUES (1, '101', 2, 5000.00)");
        
        // Create config file
        if (!is_dir('includes')) {
            mkdir('includes', 0755, true);
        }
        
        $config = '<?php
$host = "' . $host . '";
$dbname = "' . $database . '";
$user = "' . $username . '";
$pass = "' . $password . '";

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
?>';
        
        file_put_contents('includes/config.php', $config);
        
        $success = true;
        $message = "Setup completed successfully!";
        
    } catch (Exception $e) {
        $success = false;
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🏠 Simple Hostel Setup</h1>
    
    <?php if (isset($success)): ?>
        <div class="<?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
            <?php if ($success): ?>
                <p><strong>Default Login:</strong></p>
                <p>Email: admin@demo.com<br>Password: password</p>
                <p><a href="mobile_app.php">Open Application</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Host:</label>
            <input type="text" name="host" value="localhost" required>
        </div>
        
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" value="root" required>
        </div>
        
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" placeholder="Leave blank if no password">
        </div>
        
        <div class="form-group">
            <label>Database Name:</label>
            <input type="text" name="database" value="tenant_management" required>
        </div>
        
        <button type="submit" name="setup">🚀 Setup System</button>
    </form>
</body>
</html>