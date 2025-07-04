<?php
session_start();
include('../includes/config.php');
include('../includes/checklogin.php');

// Fetch payments joined with tenant names
$stmt = $pdo->query("
    SELECT p.id, t.full_name, p.amount, p.payment_date, p.remarks 
    FROM payments p 
    LEFT JOIN tenants t ON p.tenant_id = t.id 
    ORDER BY p.payment_date DESC
");

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('../includes/header.php'); ?>
<?php include('../includes/sidebar.php'); ?>

<div class="container mt-5" style="margin-left:220px;">
    <h2>All Payments</h2>

    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Tenant</th>
                <th>Amount (₹)</th>
                <th>Date</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($payments) > 0): ?>
                <?php foreach ($payments as $i => $row): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td>₹ <?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No payments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>