<?php
session_start();
include('../includes/config.php');
include('../includes/checklogin.php');

$message = "";
$error = "";

// Fetch tenants for dropdown
$tenants = $pdo->query("SELECT id, full_name FROM tenants WHERE status = 'Checked-In'")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submit
if (isset($_POST['submit'])) {
    $tenant_id = $_POST['tenant_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $remarks = $_POST['remarks'];

    if (!$tenant_id || !$amount || !$payment_date) {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO payments (tenant_id, amount, payment_date, remarks) 
                               VALUES (:tenant_id, :amount, :payment_date, :remarks)");
        $stmt->execute([
            ':tenant_id' => $tenant_id,
            ':amount' => $amount,
            ':payment_date' => $payment_date,
            ':remarks' => $remarks
        ]);
        $message = "Payment recorded successfully!";
    }
}
?>

<?php include('../includes/header.php'); ?>
<?php include('../includes/sidebar.php'); ?>

<div class="container mt-5" style="margin-left:220px;">
    <h2>Add Payment</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="w-50">
        <div class="mb-3">
            <label>Tenant</label>
            <select name="tenant_id" class="form-select" required>
                <option value="">-- Select Tenant --</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Amount</label>
            <input type="number" name="amount" class="form-control" min="1" required>
        </div>

        <div class="mb-3">
            <label>Payment Date</label>
            <input type="date" name="payment_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Remarks (optional)</label>
            <textarea name="remarks" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Add Payment</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>