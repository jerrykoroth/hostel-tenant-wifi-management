<?php
// Debug Setup - Shows detailed errors
if (isset($_POST['action'])) {
    $host = $_POST['host'] ?? 'localhost';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    $database = $_POST['database'] ?? 'tenant_management';
    
    echo "<h2>🔍 Debug Results:</h2>";
    
    // Test 1: Basic Connection
    echo "<h3>1. Testing MySQL Connection...</h3>";
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ <strong>Connection successful!</strong><br>";
        
        // Show MySQL/MariaDB version
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        echo "📊 <strong>Database version:</strong> $version<br><br>";
        
    } catch (PDOException $e) {
        echo "❌ <strong>Connection failed:</strong> " . $e->getMessage() . "<br><br>";
        exit;
    }
    
    // Test 2: Database Creation
    echo "<h3>2. Creating Database...</h3>";
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
        echo "✅ <strong>Database '$database' created/exists!</strong><br><br>";
    } catch (PDOException $e) {
        echo "❌ <strong>Database creation failed:</strong> " . $e->getMessage() . "<br><br>";
        exit;
    }
    
    // Test 3: Connect to Database
    echo "<h3>3. Connecting to Database...</h3>";
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ <strong>Connected to database '$database'!</strong><br><br>";
    } catch (PDOException $e) {
        echo "❌ <strong>Database connection failed:</strong> " . $e->getMessage() . "<br><br>";
        exit;
    }
    
    // Test 4: Create Tables One by One
    echo "<h3>4. Creating Tables...</h3>";
    
    $tables = [
        'hostels' => "CREATE TABLE IF NOT EXISTS hostels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            address TEXT NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'admin',
            hostel_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        'rooms' => "CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hostel_id INT DEFAULT 1,
            room_number VARCHAR(20) NOT NULL,
            capacity INT DEFAULT 1,
            monthly_rent DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        'beds' => "CREATE TABLE IF NOT EXISTS beds (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            bed_number VARCHAR(20) NOT NULL,
            status VARCHAR(20) DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        'tenants' => "CREATE TABLE IF NOT EXISTS tenants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            phone VARCHAR(20) NOT NULL,
            address TEXT,
            bed_id INT,
            room_id INT,
            monthly_rent DECIMAL(10,2) DEFAULT 0.00,
            security_deposit DECIMAL(10,2) DEFAULT 0.00,
            status VARCHAR(20) DEFAULT 'active',
            checkin_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        'payments' => "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            method VARCHAR(20) DEFAULT 'cash',
            notes TEXT,
            receipt_number VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    $success_count = 0;
    foreach ($tables as $table_name => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ <strong>Table '$table_name' created successfully!</strong><br>";
            $success_count++;
        } catch (PDOException $e) {
            echo "❌ <strong>Table '$table_name' failed:</strong> " . $e->getMessage() . "<br>";
            echo "<details><summary>SQL Query:</summary><pre>$sql</pre></details><br>";
        }
    }
    
    echo "<br><h3>5. Inserting Sample Data...</h3>";
    
    $sample_data = [
        "INSERT IGNORE INTO hostels (id, name, address, phone, email) VALUES (1, 'Demo Hostel', '123 Main Street', '555-0123', 'info@demo.com')",
        "INSERT IGNORE INTO users (name, email, password_hash, role, hostel_id) VALUES ('Admin', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1)",
        "INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, monthly_rent) VALUES (1, '101', 2, 5000.00)",
        "INSERT IGNORE INTO beds (room_id, bed_number, status) VALUES (1, 'A', 'available')",
        "INSERT IGNORE INTO beds (room_id, bed_number, status) VALUES (1, 'B', 'available')"
    ];
    
    foreach ($sample_data as $i => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ <strong>Sample data " . ($i + 1) . " inserted!</strong><br>";
        } catch (PDOException $e) {
            echo "❌ <strong>Sample data " . ($i + 1) . " failed:</strong> " . $e->getMessage() . "<br>";
        }
    }
    
    // Test 6: Create Config File
    echo "<br><h3>6. Creating Config File...</h3>";
    try {
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

function generate_receipt_number() {
    return "RCP" . date("Ymd") . rand(1000, 9999);
}
?>';
        
        file_put_contents('includes/config.php', $config);
        echo "✅ <strong>Config file created: includes/config.php</strong><br>";
        
    } catch (Exception $e) {
        echo "❌ <strong>Config creation failed:</strong> " . $e->getMessage() . "<br>";
    }
    
    echo "<br><div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Setup Summary:</h3>";
    echo "<p><strong>Tables created:</strong> $success_count out of " . count($tables) . "</p>";
    echo "<p><strong>Login credentials:</strong></p>";
    echo "<p>Email: admin@demo.com<br>Password: password</p>";
    echo "<p><a href='mobile_app.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Open Application</a></p>";
    echo "</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; line-height: 1.6; }
        h1 { color: #333; text-align: center; }
        h2, h3 { color: #666; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        details { margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Debug Setup</h1>
    
    <form method="POST">
        <div class="form-group">
            <label>MySQL Host:</label>
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
        
        <button type="submit" name="action" value="debug">🔍 Debug Install</button>
    </form>
</body>
</html>