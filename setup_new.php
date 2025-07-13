<?php
// Tenant Management System Setup Script
// Clean version without any potential encoding issues

// Prevent direct access after setup is complete
if (file_exists('setup_complete.lock')) {
    die('Setup has already been completed. Delete setup_complete.lock to run setup again.');
}

// Handle setup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_action'])) {
    $action = $_POST['setup_action'];
    
    switch ($action) {
        case 'test_connection':
            $host = $_POST['host'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            try {
                $pdo = new PDO("mysql:host=$host", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo json_encode(['success' => true, 'message' => 'Connection successful!']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
            }
            exit;
            
        case 'create_database':
            $host = $_POST['host'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $database = $_POST['database'];
            
            try {
                $pdo = new PDO("mysql:host=$host", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                echo json_encode(['success' => true, 'message' => 'Database created successfully!']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database creation failed: ' . $e->getMessage()]);
            }
            exit;
            
        case 'install_tables':
            $host = $_POST['host'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $database = $_POST['database'];
            
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create tables
                $sql = "
                CREATE TABLE IF NOT EXISTS hostels (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(150) NOT NULL,
                    address TEXT NOT NULL,
                    phone VARCHAR(20),
                    email VARCHAR(100),
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                );
                
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    role ENUM('admin', 'staff', 'owner') DEFAULT 'staff',
                    hostel_id INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE SET NULL
                );
                
                CREATE TABLE IF NOT EXISTS rooms (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    hostel_id INT NOT NULL,
                    room_number VARCHAR(20) NOT NULL,
                    capacity INT NOT NULL DEFAULT 1,
                    monthly_rent DECIMAL(10,2) DEFAULT 0.00,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
                );
                
                CREATE TABLE IF NOT EXISTS beds (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    room_id INT NOT NULL,
                    bed_number VARCHAR(20) NOT NULL,
                    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
                );
                
                CREATE TABLE IF NOT EXISTS tenants (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100),
                    phone VARCHAR(20) NOT NULL,
                    address TEXT,
                    id_proof_type ENUM('aadhar', 'pan', 'passport', 'driving_license') DEFAULT 'aadhar',
                    id_proof_number VARCHAR(50),
                    emergency_contact VARCHAR(20),
                    emergency_contact_name VARCHAR(100),
                    bed_id INT,
                    checkin_date DATE,
                    checkout_date DATE,
                    monthly_rent DECIMAL(10,2) DEFAULT 0.00,
                    security_deposit DECIMAL(10,2) DEFAULT 0.00,
                    status ENUM('active', 'inactive', 'checked_out') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (bed_id) REFERENCES beds(id) ON DELETE SET NULL
                );
                
                CREATE TABLE IF NOT EXISTS payments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id INT NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    date DATE NOT NULL,
                    method ENUM('cash', 'card', 'upi', 'bank_transfer', 'cheque') DEFAULT 'cash',
                    notes TEXT,
                    payment_type ENUM('rent', 'security_deposit', 'maintenance', 'penalty', 'other') DEFAULT 'rent',
                    receipt_number VARCHAR(50),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
                );
                
                CREATE TABLE IF NOT EXISTS activity_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    action VARCHAR(100) NOT NULL,
                    description TEXT,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                );
                ";
                
                $pdo->exec($sql);
                
                // Insert sample data
                $pdo->exec("INSERT IGNORE INTO hostels (name, address, phone, email, description) VALUES ('Demo Hostel', '123 Main Street', '555-0123', 'info@demo.com', 'Sample hostel')");
                $pdo->exec("INSERT IGNORE INTO users (name, email, password_hash, role, hostel_id) VALUES ('Admin', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1)");
                $pdo->exec("INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, monthly_rent) VALUES (1, '101', 2, 5000.00)");
                $pdo->exec("INSERT IGNORE INTO beds (room_id, bed_number, status) VALUES (1, 'A', 'available')");
                $pdo->exec("INSERT IGNORE INTO beds (room_id, bed_number, status) VALUES (1, 'B', 'available')");
                
                echo json_encode(['success' => true, 'message' => 'Database tables created successfully!']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $e->getMessage()]);
            }
            exit;
            
        case 'create_config':
            $host = $_POST['host'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $database = $_POST['database'];
            
            // Ensure includes directory exists
            if (!is_dir('includes')) {
                mkdir('includes', 0755, true);
            }
            
            $config_content = '<?php
// Database Configuration
$host = "' . $host . '";
$dbname = "' . $database . '";
$user = "' . $username . '";
$pass = "' . $password . '";

// Database Connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbh = $pdo;
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// Utility Functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generate_receipt_number() {
    return "RCP" . date("Ymd") . rand(1000, 9999);
}
?>';
            
            if (file_put_contents('includes/config.php', $config_content)) {
                echo json_encode(['success' => true, 'message' => 'Configuration file created successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create configuration file']);
            }
            exit;
            
        case 'complete_setup':
            file_put_contents('setup_complete.lock', date('Y-m-d H:i:s'));
            echo json_encode(['success' => true, 'message' => 'Setup completed successfully!']);
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Management System Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        .setup-container {
            max-width: 700px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #007bff, #17a2b8);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .setup-step {
            display: none;
            padding: 2rem;
        }
        .setup-step.active {
            display: block;
        }
        .btn {
            border-radius: 8px;
        }
        .form-control {
            border-radius: 8px;
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-card">
            <div class="setup-header">
                <h1>🏠 Tenant Management System Setup</h1>
                <p class="mb-0">Let's get your hostel management system ready!</p>
            </div>
            
            <!-- Step 1: Database Connection -->
            <div class="setup-step active" id="step1">
                <h3>📊 Database Connection</h3>
                <p>Enter your MySQL database connection details:</p>
                
                <form id="connectionForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Host</label>
                            <input type="text" class="form-control" name="host" value="localhost" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="root" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Leave blank if no password">
                    </div>
                    <button type="submit" class="btn btn-primary">🔌 Test Connection</button>
                </form>
                
                <div id="connectionResult" class="mt-3"></div>
            </div>
            
            <!-- Step 2: Create Database -->
            <div class="setup-step" id="step2">
                <h3>🗄️ Create Database</h3>
                <p>Create a new database for the tenant management system:</p>
                
                <form id="databaseForm">
                    <div class="mb-3">
                        <label class="form-label">Database Name</label>
                        <input type="text" class="form-control" name="database" value="tenant_management" required>
                    </div>
                    <button type="submit" class="btn btn-primary">➕ Create Database</button>
                </form>
                
                <div id="databaseResult" class="mt-3"></div>
            </div>
            
            <!-- Step 3: Install Tables -->
            <div class="setup-step" id="step3">
                <h3>📋 Install Database Tables</h3>
                <p>Install the required database tables and sample data:</p>
                
                <button class="btn btn-primary" onclick="installTables()">⬇️ Install Tables</button>
                
                <div id="tablesResult" class="mt-3"></div>
            </div>
            
            <!-- Step 4: Configuration -->
            <div class="setup-step" id="step4">
                <h3>⚙️ Create Configuration</h3>
                <p>Generate the configuration file with your database settings:</p>
                
                <button class="btn btn-primary" onclick="createConfig()">📁 Create Configuration</button>
                
                <div id="configResult" class="mt-3"></div>
            </div>
            
            <!-- Step 5: Complete -->
            <div class="setup-step" id="step5">
                <h3>✅ Setup Complete!</h3>
                <p>Your tenant management system is now ready to use.</p>
                
                <div class="alert alert-success">
                    <h5>🎉 Congratulations!</h5>
                    <p>Setup completed successfully. Default login credentials:</p>
                    <ul>
                        <li><strong>Email:</strong> admin@demo.com</li>
                        <li><strong>Password:</strong> password</li>
                    </ul>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <a href="mobile_app.php" class="btn btn-success w-100">📱 Open Mobile App</a>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-warning w-100" onclick="completeSetup()">🔒 Lock Setup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        let setupData = {};
        
        function showStep(step) {
            document.querySelectorAll('.setup-step').forEach(s => s.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            currentStep = step;
        }
        
        function showResult(containerId, success, message) {
            const container = document.getElementById(containerId);
            const alertClass = success ? 'alert-success' : 'alert-danger';
            
            container.innerHTML = '<div class="alert ' + alertClass + '">' + message + '</div>';
            
            if (success) {
                setTimeout(() => {
                    showStep(currentStep + 1);
                }, 1500);
            }
        }
        
        // Step 1: Test Connection
        document.getElementById('connectionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('setup_action', 'test_connection');
            
            setupData.host = formData.get('host');
            setupData.username = formData.get('username');
            setupData.password = formData.get('password');
            
            fetch('setup_new.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showResult('connectionResult', data.success, data.message);
            })
            .catch(error => {
                showResult('connectionResult', false, 'Error: ' + error.message);
            });
        });
        
        // Step 2: Create Database
        document.getElementById('databaseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('setup_action', 'create_database');
            formData.append('host', setupData.host);
            formData.append('username', setupData.username);
            formData.append('password', setupData.password);
            formData.append('database', this.database.value);
            
            setupData.database = this.database.value;
            
            fetch('setup_new.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showResult('databaseResult', data.success, data.message);
            })
            .catch(error => {
                showResult('databaseResult', false, 'Error: ' + error.message);
            });
        });
        
        // Step 3: Install Tables
        function installTables() {
            const formData = new FormData();
            formData.append('setup_action', 'install_tables');
            formData.append('host', setupData.host);
            formData.append('username', setupData.username);
            formData.append('password', setupData.password);
            formData.append('database', setupData.database);
            
            fetch('setup_new.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showResult('tablesResult', data.success, data.message);
            })
            .catch(error => {
                showResult('tablesResult', false, 'Error: ' + error.message);
            });
        }
        
        // Step 4: Create Configuration
        function createConfig() {
            const formData = new FormData();
            formData.append('setup_action', 'create_config');
            formData.append('host', setupData.host);
            formData.append('username', setupData.username);
            formData.append('password', setupData.password);
            formData.append('database', setupData.database);
            
            fetch('setup_new.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showResult('configResult', data.success, data.message);
            })
            .catch(error => {
                showResult('configResult', false, 'Error: ' + error.message);
            });
        }
        
        // Step 5: Complete Setup
        function completeSetup() {
            const formData = new FormData();
            formData.append('setup_action', 'complete_setup');
            
            fetch('setup_new.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Setup locked successfully! You can now use the application.');
                    location.reload();
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    </script>
</body>
</html>