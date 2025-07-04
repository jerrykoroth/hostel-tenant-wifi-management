<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');

$message = "";
$error = "";

// Handle form submission
if (isset($_POST['submit'])) {
    $full_name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $room_id = intval($_POST['room_id']);

    // Basic validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($gender) || empty($address) || $room_id <= 0) {
        $error = "All fields are required.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Email already exists.";
        } else {
            // Insert tenant
            $stmt = $pdo->prepare("INSERT INTO tenants (full_name, email, phone, gender, address, room_id) 
                VALUES (:full_name, :email, :phone, :gender, :address, :room_id)");
            $success = $stmt->execute([
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':gender' => $gender,
                ':address' => $address,
                ':room_id' => $room_id
            ]);

            if ($success) {
                $message = "Tenant registered successfully!";
            } else {
                $error = "Failed to register tenant.";
            }
        }
    }
}

// Fetch available rooms (capacity not full)
$sql = "SELECT r.id, r.room_number, r.capacity, r.occupied 
        FROM rooms r 
        WHERE r.occupied < r.capacity 
        ORDER BY r.room_number";
$rooms = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="container mt-5" style="margin-left:220px;">
    <h2>Register Tenant</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" class="w-50">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Mobile Number</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Gender</label>
            <select name="gender" class="form-select" required>
                <option value="">-- Select Gender --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label>Select Room</label>
            <select name="room_id" class="form-select" required>
                <option value="">-- Select Room --</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?php echo $room['id']; ?>">
                        <?php echo $room['room_number'] . " (Available: " . ($room['capacity'] - $room['occupied']) . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Register Tenant</button>
    </form>
</div>

<?php include('includes/footer.php'); ?>