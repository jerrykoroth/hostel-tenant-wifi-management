<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');

$error = '';
$message = '';

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Delete tenant
    $stmt = $pdo->prepare("DELETE FROM tenants WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $message = "Tenant deleted successfully!";
}

// Fetch all tenants with their room info
$query = $pdo->query("SELECT t.id, t.full_name, t.email, t.phone, r.room_number, t.status 
                      FROM tenants t
                      LEFT JOIN rooms r ON t.room_id = r.id");
$tenants = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="container mt-5" style="margin-left:220px;">
    <h2>Manage Tenants</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Room</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($tenants) > 0): ?>
                <?php foreach ($tenants as $i => $tenant): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($tenant['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['email']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['phone']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['room_number'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($tenant['status']); ?></td>
                        <td>
                            <a href="manage-tenants.php?delete=<?php echo $tenant['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this tenant?');">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No tenants found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('includes/footer.php'); ?>