<?php
// reports.php
session_start();
include_once '../db_connect.php'; // adjust path as needed

// Check admin login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch current check-ins with tenant and room info and payment status
$sql = "
SELECT t.name AS tenant_name, r.room_number, c.checkin_date,
  IF(p.paid_amount IS NULL, 'No', 'Yes') AS payment_status
FROM checkins c
JOIN tenants t ON c.tenant_id = t.id
JOIN rooms r ON t.room_id = r.id
LEFT JOIN (
    SELECT tenant_id, SUM(amount) AS paid_amount
    FROM payments
    GROUP BY tenant_id
) p ON t.id = p.tenant_id
WHERE c.checkout_date IS NULL
ORDER BY c.checkin_date DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Hostel Reports</title>
<link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="container mt-4">
  <h2>Current Tenants Report</h2>

  <div class="mb-3">
    <!-- Export buttons - you can link these to scripts that generate PDF/Excel -->
    <form method="POST" action="export_pdf.php" style="display:inline;">
      <button type="submit" class="btn btn-danger">Export PDF</button>
    </form>
    <form method="POST" action="export_excel.php" style="display:inline;">
      <button type="submit" class="btn btn-success">Export Excel</button>
    </form>
  </div>

  <?php if ($result && $result->num_rows > 0): ?>
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>Tenant Name</th>
          <th>Room Number</th>
          <th>Check-In Date</th>
          <th>Payment Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['tenant_name']); ?></td>
            <td><?php echo htmlspecialchars($row['room_number']); ?></td>
            <td><?php echo date('d M Y', strtotime($row['checkin_date'])); ?></td>
            <td><?php echo $row['payment_status']; ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No current tenants found.</p>
  <?php endif; ?>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>