<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php'); // ensure user is logged in

// Fetch all access logs ordered by most recent
$stmt = $pdo->query("SELECT id, username, action, log_time FROM userlog ORDER BY log_time DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Access Logs</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <style>body { margin-left: 220px; }</style>
</head>
<body>
    <?php include('../includes/sidebar.php'); ?>

    <div class="container mt-5">
        <h2>Access Logs</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['id']); ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['log_time']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No logs found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>