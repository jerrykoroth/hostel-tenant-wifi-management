<?php
// checkin.php
session_start();
include_once '../db_connect.php'; // adjust path as needed

// Check if user is logged in - simple example
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tenant_id'])) {
    $tenantId = intval($_POST['tenant_id']);
    $checkinDate = date('Y-m-d H:i:s');

    // Check if tenant already checked in
    $stmt = $conn->prepare("SELECT * FROM checkins WHERE tenant_id = ? AND checkout_date IS NULL");
    $stmt->bind_param("i", $tenantId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "Tenant is already checked in.";
    } else {
        // Insert new check-in record
        $stmt = $conn->prepare("INSERT INTO checkins (tenant_id, checkin_date) VALUES (?, ?)");
        $stmt->bind_param("is", $tenantId, $checkinDate);
        if ($stmt->execute()) {
            $message = "Tenant checked in successfully.";
        } else {
            $message = "Error checking in tenant.";
        }
    }
}

// Fetch tenants who are NOT currently checked in
$sql = "SELECT t.id, t.name FROM tenants t
        WHERE t.id NOT IN (
          SELECT tenant_id FROM checkins WHERE checkout_date IS NULL
        )";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Check-In Tenant</title>
<link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="container mt-4">
  <h2>Tenant Check-In</h2>

  <?php if (isset($message)): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <?php if ($result && $result->num_rows > 0): ?>
    <form method="POST" action="checkin.php">
      <div class="mb-3">
        <label for="tenant_id" class="form-label">Select Tenant to Check-In:</label>
        <select class="form-select" id="tenant_id" name="tenant_id" required>
          <option value="" selected disabled>-- Select Tenant --</option>
          <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Check In</button>
    </form>
  <?php else: ?>
    <p>All tenants are currently checked in or no tenants available.</p>
  <?php endif; ?>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>