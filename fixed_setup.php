<?php
// Fixed Setup - Executes each SQL statement separately
$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['host'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $database = $_POST['database'];
    
    try {
        // Step 1: Connect to MySQL
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Step 2: Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
        
        // Step 3: Connect to the database
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Step 4: Create tables one by one
        $createTables = [
            "CREATE TABLE IF NOT EXISTS hostels (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                address TEXT NOT NULL,
                phone VARCHAR(20),
                email VARCHAR(100),
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'admin',
                hostel_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS rooms (
                id INT AUTO_INCREMENT PRIMARY KEY,
                hostel_id INT DEFAULT 1,
                room_number VARCHAR(20) NOT NULL,
                capacity INT DEFAULT 1,
                rent_amount DECIMAL(10,2) DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS tenants (
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
            )",
            
            "CREATE TABLE IF NOT EXISTS payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                payment_date DATE NOT NULL,
                payment_method VARCHAR(20) DEFAULT 'cash',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS checkin_checkout (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT NOT NULL,
                room_id INT NOT NULL,
                checkin_date DATE NOT NULL,
                checkout_date DATE,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        ];
        
        // Execute each CREATE TABLE statement
        foreach ($createTables as $sql) {
            $pdo->exec($sql);
        }
        
        // Step 5: Insert sample data - one statement at a time
        $sampleData = [
            "INSERT IGNORE INTO hostels (id, name, address, phone, email, description) VALUES (1, 'Demo Hostel', '123 Main Street', '555-0123', 'info@demo.com', 'Sample hostel for testing')",
            
            "INSERT IGNORE INTO users (name, email, password, role, hostel_id) VALUES ('Admin User', 'admin@demo.com', 'admin123', 'admin', 1)",
            
            "INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, rent_amount) VALUES (1, '101', 2, 5000.00)",
            
            "INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, rent_amount) VALUES (1, '102', 1, 7000.00)",
            
            "INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, rent_amount) VALUES (1, '103', 3, 4000.00)"
        ];
        
        // Execute each INSERT statement
        foreach ($sampleData as $sql) {
            $pdo->exec($sql);
        }
        
        // Step 6: Create config file
        if (!file_exists('includes')) {
            mkdir('includes', 0755, true);
        }
        
        $configContent = '<?php
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

function generate_receipt_number() {
    return "RCP" . date("Ymd") . rand(1000, 9999);
}
?>';
        
        file_put_contents('includes/config.php', $configContent);
        
        $success = true;
        $message = "✅ Setup completed successfully! Database created with all tables and sample data.";
        
    } catch (PDOException $e) {
        $success = false;
        $message = "❌ Setup failed: " . $e->getMessage();
    } catch (Exception $e) {
        $success = false;
        $message = "❌ Setup failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fixed Setup</title>
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
            margin-bottom: 30px;
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
            margin-top: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin: 20px 0;
        }
        .login-info {
            background: #e2f3ff;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #b3d9ff;
        }
        .login-info h3 {
            margin-top: 0;
            color: #0056b3;
        }
        .btn-link {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
            margin-top: 10px;
        }
        .btn-link:hover {
            background: #0056b3;
        }
        .info-box {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fixed Setup</h1>
        
        <?php if ($message): ?>
            <div class="<?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="login-info">
                <h3>🎉 Setup Complete!</h3>
                <p><strong>Database created:</strong> <?php echo $database; ?></p>
                <p><strong>Tables created:</strong> hostels, users, rooms, tenants, payments, checkin_checkout</p>
                <p><strong>Config file:</strong> includes/config.php</p>
                <hr>
                <p><strong>Default Login Credentials:</strong></p>
                <p>📧 <strong>Email:</strong> admin@demo.com</p>
                <p>🔑 <strong>Password:</strong> admin123</p>
                <hr>
                <p><strong>Access Your Application:</strong></p>
                <a href="mobile_app.php" class="btn-link">📱 Mobile App</a>
                <a href="dashboard.php" class="btn-link">📊 Dashboard</a>
            </div>
        <?php else: ?>
            <div class="info-box">
                <strong>🔧 This setup fixes the SQL syntax error by:</strong>
                <ul>
                    <li>Executing CREATE TABLE statements separately</li>
                    <li>Executing INSERT statements separately</li>
                    <li>Proper error handling for MariaDB</li>
                </ul>
            </div>
            
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
                
                <button type="submit">🚀 Setup Database (Fixed)</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>