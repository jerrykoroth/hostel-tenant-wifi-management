<?php
// Check if setup has been completed
$setup_completed = file_exists('setup_complete.lock');

// If setup is not completed, redirect to setup
if (!$setup_completed) {
    header("Location: setup.php");
    exit;
}

// Check if config file exists
if (!file_exists('includes/config.php')) {
    header("Location: setup.php");
    exit;
}

// Test database connection
try {
    include('includes/config.php');
    $pdo->query("SELECT 1");
} catch (Exception $e) {
    header("Location: setup.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            padding: 4rem 0;
            text-align: center;
            color: white;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .btn-launch {
            background: linear-gradient(135deg, #007bff, #17a2b8);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .btn-launch:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            color: white;
        }
        
        .stats-section {
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
        }
        
        .stat-item {
            text-align: center;
            color: white;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .footer {
            background: rgba(0,0,0,0.2);
            padding: 2rem 0;
            margin-top: 4rem;
            color: white;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .feature-icon {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1 class="hero-title">
                <i class="fas fa-building"></i> Tenant Management System
            </h1>
            <p class="hero-subtitle">
                Complete hostel management solution with mobile-responsive design
            </p>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="stats-section">
                        <div class="row">
                            <div class="col-md-3 col-6">
                                <div class="stat-item">
                                    <span class="stat-number"><i class="fas fa-mobile-alt"></i></span>
                                    <div class="stat-label">Mobile Ready</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="stat-item">
                                    <span class="stat-number"><i class="fas fa-shield-alt"></i></span>
                                    <div class="stat-label">Secure</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="stat-item">
                                    <span class="stat-number"><i class="fas fa-rocket"></i></span>
                                    <div class="stat-label">Fast</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="stat-item">
                                    <span class="stat-number"><i class="fas fa-chart-line"></i></span>
                                    <div class="stat-label">Analytics</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row justify-content-center mt-4">
                <div class="col-md-6">
                    <a href="mobile_app.php" class="btn-launch">
                        <i class="fas fa-mobile-alt"></i> Launch Mobile App
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="room_management.php" class="btn-launch">
                        <i class="fas fa-bed"></i> Manage Rooms
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Features Section -->
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Tenant Management</h4>
                    <p>Complete tenant registration, check-in/check-out, and history tracking with personal details and emergency contacts.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon text-success">
                        <i class="fas fa-bed"></i>
                    </div>
                    <h4>Room & Bed Management</h4>
                    <p>Dynamic room creation, automatic bed generation, visual bed layouts, and real-time occupancy tracking.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon text-info">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h4>Payment System</h4>
                    <p>Multiple payment methods, automatic receipt generation, payment history, and comprehensive reporting.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon text-warning">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h4>Analytics & Reports</h4>
                    <p>Real-time dashboard, occupancy analytics, revenue tracking, and detailed date-range reports.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon text-danger">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h4>Mobile-Responsive</h4>
                    <p>Perfect mobile experience with touch-friendly interface, progressive web app capabilities, and offline support.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon text-secondary">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Security & Reliability</h4>
                    <p>Advanced security features, activity logging, role-based access control, and data protection.</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="feature-card">
                    <h4 class="text-center mb-4">Quick Actions</h4>
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <a href="mobile_app.php" class="btn btn-primary w-100">
                                <i class="fas fa-mobile-alt"></i><br>Mobile App
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="room_management.php" class="btn btn-success w-100">
                                <i class="fas fa-bed"></i><br>Room Management
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="mobile_app.php#dashboard" class="btn btn-info w-100">
                                <i class="fas fa-chart-bar"></i><br>Dashboard
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="mobile_app.php#reports" class="btn btn-warning w-100">
                                <i class="fas fa-file-alt"></i><br>Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="feature-card">
                    <h4 class="text-center mb-4">System Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-info-circle"></i> Application Details</h6>
                            <ul class="list-unstyled">
                                <li><strong>Version:</strong> 1.0.0</li>
                                <li><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></li>
                                <li><strong>Database:</strong> MySQL</li>
                                <li><strong>Setup Status:</strong> <span class="badge bg-success">Complete</span></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-tools"></i> Administration</h6>
                            <ul class="list-unstyled">
                                <li><a href="setup.php" class="text-decoration-none">Re-run Setup</a></li>
                                <li><a href="README.md" class="text-decoration-none">Documentation</a></li>
                                <li><strong>Support:</strong> jerrykoroth@gmail.com</li>
                                <li><strong>Last Updated:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Tenant Management System. All rights reserved.</p>
                </div>
                <div class="col-md-6">
                    <p>Made with <i class="fas fa-heart text-danger"></i> for hostel management</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
