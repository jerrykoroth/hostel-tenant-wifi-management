<?php
// Working Setup - Creates all required tables
$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        
        // Connect to database
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create all tables
        $pdo->exec("CREATE TABLE IF NOT EXISTS hostels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            address TEXT NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'admin',
            hostel_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hostel_id INT DEFAULT 1,
            room_number VARCHAR(20) NOT NULL,
            capacity INT DEFAULT 1,
            rent_amount DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS tenants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            phone VARCHAR(20) NOT NULL,
            address TEXT,
            room_id INT,
            rent_amount DECIMAL(10,2) DEFAULT 0.00,
            security_deposit DECIMAL(10,2) DEFAULT 0.00,
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_date DATE NOT NULL,
            payment_method VARCHAR(20) DEFAULT 'cash',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // This is the missing table that dashboard.php needs
        $pdo->exec("CREATE TABLE IF NOT EXISTS checkin_checkout (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            room_id INT NOT NULL,
            checkin_date DATE NOT NULL,
            checkout_date DATE,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert sample data
        $pdo->exec("INSERT IGNORE INTO hostels (id, name, address, phone, email) VALUES (1, 'Demo Hostel', '123 Main Street', '555-0123', 'info@demo.com')");
        $pdo->exec("INSERT IGNORE INTO users (name, email, password, role, hostel_id) VALUES ('Admin User', 'admin@demo.com', 'admin123', 'admin', 1)");
        $pdo->exec("INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, rent_amount) VALUES (1, '101', 2, 5000.00)");
        $pdo->exec("INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, rent_amount) VALUES (1, '102', 1, 7000.00)");
        
        // Create config file
        if (!file_exists('includes')) {
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
        $message = "Setup completed successfully! All tables created including checkin_checkout table.";
        
    } catch (Exception $e) {
        $success = false;
        $message = "Setup failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Working Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
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
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
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
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .login-info {
            background: #e2f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏠 Working Setup</h1>
        <p>This setup creates all required tables including the missing checkin_checkout table.</p>
        
        <?php if ($message): ?>
            <div class="<?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="login-info">
                <h3>✅ Setup Complete!</h3>
                <p><strong>Login Credentials:</strong></p>
                <p>Email: admin@demo.com<br>Password: admin123</p>
                <p><a href="mobile_app.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Open Mobile App</a></p>
                <p><a href="dashboard.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Open Dashboard</a></p>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="host">MySQL Host:</label>
                    <input type="text" id="host" name="host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="username">MySQL Username:</label>
                    <input type="text" id="username" name="username" value="root" required>
                </div>
                
                <div class="form-group">
                    <label for="password">MySQL Password:</label>
                    <input type="password" id="password" name="password" placeholder="Leave blank if no password">
                </div>
                
                <div class="form-group">
                    <label for="database">Database Name:</label>
                    <input type="text" id="database" name="database" value="tenant_management" required>
                </div>
                
                <button type="submit">🚀 Setup Complete System</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>