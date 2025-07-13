<?php
// Quick Setup for Hostel Management System
if (isset($_POST['action']) && $_POST['action'] == 'setup') {
    $host = $_POST['host'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $database = $_POST['database'];
    
    try {
        // Connect to MySQL
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
        $pdo->exec("USE `$database`");
        
        // Create tables
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'admin'
            )
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS hostels (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                address TEXT NOT NULL
            )
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rooms (
                id INT AUTO_INCREMENT PRIMARY KEY,
                hostel_id INT DEFAULT 1,
                room_number VARCHAR(20) NOT NULL,
                capacity INT DEFAULT 1,
                monthly_rent DECIMAL(10,2) DEFAULT 0
            )
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tenants (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100),
                phone VARCHAR(20) NOT NULL,
                room_id INT,
                monthly_rent DECIMAL(10,2) DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active'
            )
        ");
        
        // Insert sample data
        $pdo->exec("INSERT IGNORE INTO hostels (id, name, address) VALUES (1, 'Demo Hostel', '123 Main Street')");
        $pdo->exec("INSERT IGNORE INTO users (name, email, password_hash, role) VALUES ('Admin', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
        $pdo->exec("INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, monthly_rent) VALUES (1, '101', 2, 5000)");
        
        // Create includes directory
        if (!file_exists('includes')) {
            mkdir('includes');
        }
        
        // Create config file
        $config = '<?php
$host = "' . $host . '";
$dbname = "' . $database . '";
$user = "' . $username . '";
$pass = "' . $password . '";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh = $pdo;
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function sanitize_input($data) {
    return trim(strip_tags($data));
}
?>';
        
        file_put_contents('includes/config.php', $config);
        
        echo '<div style="color: green; padding: 20px; background: #d4edda; margin: 20px 0; border-radius: 5px;">
                <h3>Setup Successful!</h3>
                <p><strong>Database:</strong> ' . $database . '</p>
                <p><strong>Tables created:</strong> users, hostels, rooms, tenants</p>
                <p><strong>Config file:</strong> includes/config.php</p>
                <hr>
                <p><strong>Login Credentials:</strong></p>
                <p>Email: admin@demo.com</p>
                <p>Password: password</p>
                <hr>
                <p><a href="mobile_app.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Open Application</a></p>
              </div>';
              
    } catch (Exception $e) {
        echo '<div style="color: red; padding: 20px; background: #f8d7da; margin: 20px 0; border-radius: 5px;">
                <h3>Setup Failed!</h3>
                <p>Error: ' . $e->getMessage() . '</p>
              </div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            background: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏠 Quick Setup</h1>
        <form method="POST">
            <div class="form-group">
                <label>MySQL Host:</label>
                <input type="text" name="host" value="localhost" required>
            </div>
            
            <div class="form-group">
                <label>MySQL Username:</label>
                <input type="text" name="username" value="root" required>
            </div>
            
            <div class="form-group">
                <label>MySQL Password:</label>
                <input type="password" name="password" placeholder="Leave blank if no password">
            </div>
            
            <div class="form-group">
                <label>Database Name:</label>
                <input type="text" name="database" value="tenant_management" required>
            </div>
            
            <button type="submit" name="action" value="setup">🚀 Setup System</button>
        </form>
    </div>
</body>
</html>