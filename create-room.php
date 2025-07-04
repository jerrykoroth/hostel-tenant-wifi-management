<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');

$message = "";
$error = "";

// Handle room creation form
if (isset($_POST['submit'])) {
    $room_number = trim($_POST['room_number']);
    $type = trim($_POST['type']);
    $capacity = intval($_POST['capacity']);

    if (empty($room_number) || empty($type) || $capacity <= 0) {
        $error = "Please fill all fields correctly.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO rooms (room_number, type, capacity, occupied) 
                                   VALUES (:room_number, :type, :capacity, 0)");
            $stmt->execute([
                ':room_number' => $room_number,
                ':type' => $type,
                ':capacity' => $capacity
            ]);
            $message = "Room created successfully!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Room number already exists.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>

<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="container mt-5" style="margin-left:220px;">
    <h2>Create New Room</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="w-50">
        <div class="mb-3">
            <label>Room Number</label>
            <input type="text" name="room_number" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Room Type</label>
            <select name="type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="Single">Single</option>
                <option value="2-Share">2-Share</option>
                <option value="3-Share">3-Share</option>
                <option value="Shared">Shared</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Capacity</label>
            <input type="number" name="capacity" class="form-control" min="1" required>
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Add Room</button>
    </form>
</div>

<?php include('includes/footer.php'); ?>