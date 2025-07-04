<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');

$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $roomId = intval($_GET['delete']);

    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = :id");
        $stmt->execute([':id' => $roomId]);
        $message = "Room deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting room: " . $e->getMessage();
    }
}

// Fetch all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="container mt-5" style="margin-left:220px;">
    <h2>Manage Rooms</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <a href="create-room.php" class="btn btn-primary mb-3">+ Add New Room</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Room Number</th>
                <th>Type</th>
                <th>Capacity</th>
                <th>Occupied</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rooms) > 0): ?>
                <?php foreach ($rooms as $i => $room): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($room['type']); ?></td>
                        <td><?php echo $room['capacity']; ?></td>
                        <td><?php echo $room['occupied']; ?></td>
                        <td>
                            <?php
                                if ($room['occupied'] >= $room['capacity']) {
                                    echo "<span class='badge bg-danger'>Full</span>";
                                } else {
                                    echo "<span class='badge bg-success'>Available</span>";
                                }
                            ?>
                        </td>
                        <td>
                            <a href="manage-rooms.php?delete=<?php echo $room['id']; ?>"
                               onclick="return confirm('Are you sure you want to delete this room?');"
                               class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No rooms found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('includes/footer.php'); ?>