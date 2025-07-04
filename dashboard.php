<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

// Fetch counts
$totalTenants = $dbh->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
$totalRooms = $dbh->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$totalPayments = $dbh->query("SELECT SUM(amount) FROM payments")->fetchColumn();
$currentCheckins = $dbh->query("SELECT COUNT(*) FROM tenants WHERE status = 'Checked-In'")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Dashboard</h2>
    <ul class="list-group">
        <li class="list-group-item">Total Tenants: <?php echo $totalTenants; ?></li>
        <li class="list-group-item">Total Rooms: <?php echo $totalRooms; ?></li>
        <li class="list-group-item">Total Payments: ₹<?php echo $totalPayments; ?></li>
        <li class="list-group-item">Currently Checked-In: <?php echo $currentCheckins; ?></li>
    </ul>
</div>
</body>
</html>
