<?php
session_start();
include('includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: mobile_app.php");
    exit;
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_room':
            $hostel_id = $_POST['hostel_id'];
            $room_number = sanitize_input($_POST['room_number']);
            $capacity = $_POST['capacity'];
            $monthly_rent = $_POST['monthly_rent'];
            $description = sanitize_input($_POST['description']);
            
            $pdo->beginTransaction();
            try {
                // Insert room
                $stmt = $pdo->prepare("INSERT INTO rooms (hostel_id, room_number, capacity, monthly_rent, description) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$hostel_id, $room_number, $capacity, $monthly_rent, $description]);
                $room_id = $pdo->lastInsertId();
                
                // Create beds for the room
                $stmt = $pdo->prepare("INSERT INTO beds (room_id, bed_number, status) VALUES (?, ?, 'available')");
                for ($i = 1; $i <= $capacity; $i++) {
                    $bed_number = chr(64 + $i); // A, B, C, etc.
                    $stmt->execute([$room_id, $bed_number]);
                }
                
                $pdo->commit();
                log_activity($pdo, $_SESSION['user_id'], 'add_room', "Added room $room_number with $capacity beds");
                echo json_encode(['success' => true, 'message' => 'Room added successfully with ' . $capacity . ' beds']);
            } catch (Exception $e) {
                $pdo->rollback();
                if (strpos($e->getMessage(), 'unique_room_per_hostel') !== false) {
                    echo json_encode(['success' => false, 'message' => 'Room number already exists in this hostel']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add room: ' . $e->getMessage()]);
                }
            }
            break;
            
        case 'get_rooms':
            $hostel_id = $_POST['hostel_id'] ?? $_SESSION['hostel_id'];
            $where_clause = $hostel_id ? "WHERE r.hostel_id = $hostel_id" : "";
            
            $stmt = $pdo->prepare("
                SELECT r.*, h.name as hostel_name,
                       COUNT(b.id) as total_beds,
                       SUM(CASE WHEN b.status = 'available' THEN 1 ELSE 0 END) as available_beds,
                       SUM(CASE WHEN b.status = 'occupied' THEN 1 ELSE 0 END) as occupied_beds
                FROM rooms r 
                JOIN hostels h ON r.hostel_id = h.id
                LEFT JOIN beds b ON r.id = b.room_id
                $where_clause
                GROUP BY r.id, h.name
                ORDER BY r.room_number
            ");
            $stmt->execute();
            $rooms = $stmt->fetchAll();
            echo json_encode(['success' => true, 'rooms' => $rooms]);
            break;
            
        case 'get_room_details':
            $room_id = $_POST['room_id'];
            
            // Get room details
            $stmt = $pdo->prepare("
                SELECT r.*, h.name as hostel_name, h.address as hostel_address
                FROM rooms r 
                JOIN hostels h ON r.hostel_id = h.id
                WHERE r.id = ?
            ");
            $stmt->execute([$room_id]);
            $room = $stmt->fetch();
            
            // Get beds in the room
            $stmt = $pdo->prepare("
                SELECT b.*, 
                       CASE WHEN t.id IS NOT NULL THEN t.name ELSE NULL END as tenant_name,
                       CASE WHEN t.id IS NOT NULL THEN t.phone ELSE NULL END as tenant_phone
                FROM beds b 
                LEFT JOIN tenants t ON b.id = t.bed_id AND t.status = 'active'
                WHERE b.room_id = ?
                ORDER BY b.bed_number
            ");
            $stmt->execute([$room_id]);
            $beds = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'room' => $room, 'beds' => $beds]);
            break;
            
        case 'update_room':
            $room_id = $_POST['room_id'];
            $room_number = sanitize_input($_POST['room_number']);
            $capacity = $_POST['capacity'];
            $monthly_rent = $_POST['monthly_rent'];
            $description = sanitize_input($_POST['description']);
            
            $pdo->beginTransaction();
            try {
                // Update room
                $stmt = $pdo->prepare("UPDATE rooms SET room_number = ?, capacity = ?, monthly_rent = ?, description = ? WHERE id = ?");
                $stmt->execute([$room_number, $capacity, $monthly_rent, $description, $room_id]);
                
                // Get current bed count
                $stmt = $pdo->prepare("SELECT COUNT(*) as current_beds FROM beds WHERE room_id = ?");
                $stmt->execute([$room_id]);
                $current_beds = $stmt->fetch()['current_beds'];
                
                // Adjust bed count if needed
                if ($capacity > $current_beds) {
                    // Add more beds
                    $stmt = $pdo->prepare("INSERT INTO beds (room_id, bed_number, status) VALUES (?, ?, 'available')");
                    for ($i = $current_beds + 1; $i <= $capacity; $i++) {
                        $bed_number = chr(64 + $i);
                        $stmt->execute([$room_id, $bed_number]);
                    }
                } elseif ($capacity < $current_beds) {
                    // Remove excess beds (only if they're not occupied)
                    $stmt = $pdo->prepare("DELETE FROM beds WHERE room_id = ? AND status = 'available' AND bed_number > ? ORDER BY bed_number DESC LIMIT ?");
                    $beds_to_remove = $current_beds - $capacity;
                    $last_bed = chr(64 + $capacity);
                    $stmt->execute([$room_id, $last_bed, $beds_to_remove]);
                }
                
                $pdo->commit();
                log_activity($pdo, $_SESSION['user_id'], 'update_room', "Updated room $room_number");
                echo json_encode(['success' => true, 'message' => 'Room updated successfully']);
            } catch (Exception $e) {
                $pdo->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to update room: ' . $e->getMessage()]);
            }
            break;
            
        case 'delete_room':
            $room_id = $_POST['room_id'];
            
            // Check if room has any occupied beds
            $stmt = $pdo->prepare("SELECT COUNT(*) as occupied_beds FROM beds WHERE room_id = ? AND status = 'occupied'");
            $stmt->execute([$room_id]);
            $occupied_beds = $stmt->fetch()['occupied_beds'];
            
            if ($occupied_beds > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete room with occupied beds']);
                break;
            }
            
            $pdo->beginTransaction();
            try {
                // Get room details for logging
                $stmt = $pdo->prepare("SELECT room_number FROM rooms WHERE id = ?");
                $stmt->execute([$room_id]);
                $room = $stmt->fetch();
                
                // Delete room (beds will be deleted automatically due to foreign key constraint)
                $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
                $stmt->execute([$room_id]);
                
                $pdo->commit();
                log_activity($pdo, $_SESSION['user_id'], 'delete_room', "Deleted room " . $room['room_number']);
                echo json_encode(['success' => true, 'message' => 'Room deleted successfully']);
            } catch (Exception $e) {
                $pdo->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to delete room: ' . $e->getMessage()]);
            }
            break;
            
        case 'update_bed_status':
            $bed_id = $_POST['bed_id'];
            $status = $_POST['status'];
            
            // Only allow status changes for available/maintenance beds
            $stmt = $pdo->prepare("SELECT status FROM beds WHERE id = ?");
            $stmt->execute([$bed_id]);
            $current_status = $stmt->fetch()['status'];
            
            if ($current_status === 'occupied' && $status !== 'occupied') {
                echo json_encode(['success' => false, 'message' => 'Cannot change status of occupied bed. Please check out tenant first.']);
                break;
            }
            
            $stmt = $pdo->prepare("UPDATE beds SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $bed_id])) {
                log_activity($pdo, $_SESSION['user_id'], 'update_bed_status', "Changed bed status to $status");
                echo json_encode(['success' => true, 'message' => 'Bed status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update bed status']);
            }
            break;
            
        case 'get_occupancy_report':
            $hostel_id = $_POST['hostel_id'] ?? $_SESSION['hostel_id'];
            $where_clause = $hostel_id ? "WHERE h.id = $hostel_id" : "";
            
            $stmt = $pdo->prepare("
                SELECT h.name as hostel_name,
                       COUNT(b.id) as total_beds,
                       SUM(CASE WHEN b.status = 'available' THEN 1 ELSE 0 END) as available_beds,
                       SUM(CASE WHEN b.status = 'occupied' THEN 1 ELSE 0 END) as occupied_beds,
                       SUM(CASE WHEN b.status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_beds,
                       ROUND((SUM(CASE WHEN b.status = 'occupied' THEN 1 ELSE 0 END) / COUNT(b.id)) * 100, 2) as occupancy_rate
                FROM hostels h
                LEFT JOIN rooms r ON h.id = r.hostel_id
                LEFT JOIN beds b ON r.id = b.room_id
                $where_clause
                GROUP BY h.id, h.name
                ORDER BY h.name
            ");
            $stmt->execute();
            $occupancy_data = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'occupancy' => $occupancy_data]);
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room & Bed Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #007bff, #17a2b8);
            color: white;
            padding: 1rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .room-card {
            border-left: 4px solid #007bff;
        }
        
        .bed-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .bed-card {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .bed-card.available {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .bed-card.occupied {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        .bed-card.maintenance {
            border-color: #ffc107;
            background: #fff3cd;
        }
        
        .occupancy-bar {
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        
        .occupancy-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #ffc107, #dc3545);
            transition: width 0.5s ease;
        }
        
        .btn {
            border-radius: 10px;
        }
        
        .modal-content {
            border-radius: 15px;
        }
        
        @media (max-width: 768px) {
            .bed-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-bed"></i> Room & Bed Management</h1>
                <a href="mobile_app.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Back to App
                </a>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary" id="totalRooms">0</h3>
                        <p class="mb-0">Total Rooms</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success" id="availableBeds">0</h3>
                        <p class="mb-0">Available Beds</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-danger" id="occupiedBeds">0</h3>
                        <p class="mb-0">Occupied Beds</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info" id="occupancyRate">0%</h3>
                        <p class="mb-0">Occupancy Rate</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Controls -->
        <div class="row mb-4">
            <div class="col-md-6">
                <select class="form-select" id="hostelFilter">
                    <option value="">All Hostels</option>
                </select>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="showAddRoomModal()">
                        <i class="fas fa-plus"></i> Add Room
                    </button>
                    <button class="btn btn-success" onclick="generateOccupancyReport()">
                        <i class="fas fa-chart-bar"></i> Report
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Rooms List -->
        <div id="roomsList">
            <!-- Rooms will be loaded here -->
        </div>
    </div>
    
    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRoomForm">
                        <div class="mb-3">
                            <label class="form-label">Select Hostel</label>
                            <select class="form-select" name="hostel_id" required>
                                <option value="">Select Hostel</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Number</label>
                            <input type="text" class="form-control" name="room_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity (Number of Beds)</label>
                            <input type="number" class="form-control" name="capacity" min="1" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monthly Rent (₹)</label>
                            <input type="number" class="form-control" name="monthly_rent" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="addRoom()">Add Room</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editRoomForm">
                        <input type="hidden" name="room_id">
                        <div class="mb-3">
                            <label class="form-label">Room Number</label>
                            <input type="text" class="form-control" name="room_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity (Number of Beds)</label>
                            <input type="number" class="form-control" name="capacity" min="1" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monthly Rent (₹)</label>
                            <input type="number" class="form-control" name="monthly_rent" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateRoom()">Update Room</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Room Details Modal -->
    <div class="modal fade" id="roomDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle"></i> Room Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="roomDetailsContent">
                    <!-- Room details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let currentHostelId = '';
        
        // Utility functions
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'block';
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Load hostels for dropdown
        function loadHostels() {
            fetch('mobile_app.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_hostels'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const hostelFilter = document.getElementById('hostelFilter');
                    const addRoomHostelSelect = document.querySelector('#addRoomForm select[name="hostel_id"]');
                    
                    let options = '<option value="">All Hostels</option>';
                    let addRoomOptions = '<option value="">Select Hostel</option>';
                    
                    data.hostels.forEach(hostel => {
                        options += `<option value="${hostel.id}">${hostel.name}</option>`;
                        addRoomOptions += `<option value="${hostel.id}">${hostel.name}</option>`;
                    });
                    
                    hostelFilter.innerHTML = options;
                    addRoomHostelSelect.innerHTML = addRoomOptions;
                }
            });
        }
        
        // Load rooms
        function loadRooms() {
            const hostelId = document.getElementById('hostelFilter').value;
            
            const formData = new FormData();
            formData.append('action', 'get_rooms');
            if (hostelId) {
                formData.append('hostel_id', hostelId);
            }
            
            fetch('room_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayRooms(data.rooms);
                    updateStats(data.rooms);
                }
            });
        }
        
        // Display rooms
        function displayRooms(rooms) {
            let html = '';
            
            rooms.forEach(room => {
                const occupancyRate = room.total_beds > 0 ? Math.round((room.occupied_beds / room.total_beds) * 100) : 0;
                
                html += `
                    <div class="card room-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title">Room ${room.room_number}</h5>
                                    <p class="card-text">
                                        <i class="fas fa-building"></i> ${room.hostel_name}<br>
                                        <i class="fas fa-bed"></i> ${room.total_beds} beds (${room.available_beds} available, ${room.occupied_beds} occupied)<br>
                                        <i class="fas fa-rupee-sign"></i> ₹${parseFloat(room.monthly_rent).toLocaleString()} per month
                                    </p>
                                    <div class="occupancy-bar">
                                        <div class="occupancy-fill" style="width: ${occupancyRate}%"></div>
                                    </div>
                                    <small class="text-muted">Occupancy: ${occupancyRate}%</small>
                                </div>
                                <div class="text-end">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="showRoomDetails(${room.id})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="editRoom(${room.id})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom(${room.id}, '${room.room_number}')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            document.getElementById('roomsList').innerHTML = html || '<p class="text-muted text-center">No rooms found.</p>';
        }
        
        // Update stats
        function updateStats(rooms) {
            let totalRooms = rooms.length;
            let totalBeds = rooms.reduce((sum, room) => sum + parseInt(room.total_beds), 0);
            let availableBeds = rooms.reduce((sum, room) => sum + parseInt(room.available_beds), 0);
            let occupiedBeds = rooms.reduce((sum, room) => sum + parseInt(room.occupied_beds), 0);
            let occupancyRate = totalBeds > 0 ? Math.round((occupiedBeds / totalBeds) * 100) : 0;
            
            document.getElementById('totalRooms').textContent = totalRooms;
            document.getElementById('availableBeds').textContent = availableBeds;
            document.getElementById('occupiedBeds').textContent = occupiedBeds;
            document.getElementById('occupancyRate').textContent = occupancyRate + '%';
        }
        
        // Show add room modal
        function showAddRoomModal() {
            const modal = new bootstrap.Modal(document.getElementById('addRoomModal'));
            modal.show();
        }
        
        // Add room
        function addRoom() {
            const formData = new FormData(document.getElementById('addRoomForm'));
            formData.append('action', 'add_room');
            
            showLoading();
            fetch('room_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addRoomModal')).hide();
                    document.getElementById('addRoomForm').reset();
                    loadRooms();
                } else {
                    showAlert(data.message, 'danger');
                }
            });
        }
        
        // Edit room
        function editRoom(roomId) {
            // First get room details
            const formData = new FormData();
            formData.append('action', 'get_room_details');
            formData.append('room_id', roomId);
            
            fetch('room_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const room = data.room;
                    const form = document.getElementById('editRoomForm');
                    
                    form.querySelector('input[name="room_id"]').value = room.id;
                    form.querySelector('input[name="room_number"]').value = room.room_number;
                    form.querySelector('input[name="capacity"]').value = room.capacity;
                    form.querySelector('input[name="monthly_rent"]').value = room.monthly_rent;
                    form.querySelector('textarea[name="description"]').value = room.description || '';
                    
                    const modal = new bootstrap.Modal(document.getElementById('editRoomModal'));
                    modal.show();
                }
            });
        }
        
        // Update room
        function updateRoom() {
            const formData = new FormData(document.getElementById('editRoomForm'));
            formData.append('action', 'update_room');
            
            showLoading();
            fetch('room_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editRoomModal')).hide();
                    loadRooms();
                } else {
                    showAlert(data.message, 'danger');
                }
            });
        }
        
        // Delete room
        function deleteRoom(roomId, roomNumber) {
            if (confirm(`Are you sure you want to delete Room ${roomNumber}? This action cannot be undone.`)) {
                const formData = new FormData();
                formData.append('action', 'delete_room');
                formData.append('room_id', roomId);
                
                showLoading();
                fetch('room_management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showAlert(data.message, 'success');
                        loadRooms();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                });
            }
        }
        
        // Show room details
        function showRoomDetails(roomId) {
            const formData = new FormData();
            formData.append('action', 'get_room_details');
            formData.append('room_id', roomId);
            
            showLoading();
            fetch('room_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    const room = data.room;
                    const beds = data.beds;
                    
                    let bedsHtml = '';
                    beds.forEach(bed => {
                        let statusClass = bed.status;
                        let statusText = bed.status.charAt(0).toUpperCase() + bed.status.slice(1);
                        let tenantInfo = '';
                        
                        if (bed.tenant_name) {
                            tenantInfo = `<br><small><strong>${bed.tenant_name}</strong><br>${bed.tenant_phone}</small>`;
                        }
                        
                        bedsHtml += `
                            <div class="bed-card ${statusClass}">
                                <h6>Bed ${bed.bed_number}</h6>
                                <span class="badge bg-${statusClass === 'available' ? 'success' : statusClass === 'occupied' ? 'danger' : 'warning'}">${statusText}</span>
                                ${tenantInfo}
                                <div class="mt-2">
                                    <select class="form-select form-select-sm" onchange="updateBedStatus(${bed.id}, this.value)">
                                        <option value="available" ${bed.status === 'available' ? 'selected' : ''}>Available</option>
                                        <option value="occupied" ${bed.status === 'occupied' ? 'selected' : ''} disabled>Occupied</option>
                                        <option value="maintenance" ${bed.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                    </select>
                                </div>
                            </div>
                        `;
                    });
                    
                    const content = `
                        <div class="mb-4">
                            <h5>Room ${room.room_number}</h5>
                            <p><i class="fas fa-building"></i> ${room.hostel_name}</p>
                            <p><i class="fas fa-map-marker-alt"></i> ${room.hostel_address}</p>
                            <p><i class="fas fa-bed"></i> Capacity: ${room.capacity} beds</p>
                            <p><i class="fas fa-rupee-sign"></i> Monthly Rent: ₹${parseFloat(room.monthly_rent).toLocaleString()}</p>
                            ${room.description ? `<p><i class="fas fa-info-circle"></i> ${room.description}</p>` : ''}
                        </div>
                        
                        <h6>Bed Layout</h6>
                        <div class="bed-grid">
                            ${bedsHtml}
                        </div>
                    `;
                    
                    document.getElementById('roomDetailsContent').innerHTML = content;
                    const modal = new bootstrap.Modal(document.getElementById('roomDetailsModal'));
                    modal.show();
                }
            });
        }
        
        // Update bed status
        function updateBedStatus(bedId, status) {
            const formData = new FormData();
            formData.append('action', 'update_bed_status');
            formData.append('bed_id', bedId);
            formData.append('status', status);
            
            fetch('room_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadRooms();
                } else {
                    showAlert(data.message, 'danger');
                }
            });
        }
        
        // Generate occupancy report
        function generateOccupancyReport() {
            const hostelId = document.getElementById('hostelFilter').value;
            
            const formData = new FormData();
            formData.append('action', 'get_occupancy_report');
            if (hostelId) {
                formData.append('hostel_id', hostelId);
            }
            
            showLoading();
            fetch('room_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    let reportHtml = '<h5>Occupancy Report</h5><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Hostel</th><th>Total Beds</th><th>Available</th><th>Occupied</th><th>Maintenance</th><th>Occupancy Rate</th></tr></thead><tbody>';
                    
                    data.occupancy.forEach(item => {
                        reportHtml += `
                            <tr>
                                <td>${item.hostel_name}</td>
                                <td>${item.total_beds}</td>
                                <td><span class="badge bg-success">${item.available_beds}</span></td>
                                <td><span class="badge bg-danger">${item.occupied_beds}</span></td>
                                <td><span class="badge bg-warning">${item.maintenance_beds}</span></td>
                                <td><strong>${item.occupancy_rate}%</strong></td>
                            </tr>
                        `;
                    });
                    
                    reportHtml += '</tbody></table></div>';
                    
                    showAlert(reportHtml, 'info');
                }
            });
        }
        
        // Event listeners
        document.getElementById('hostelFilter').addEventListener('change', loadRooms);
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadHostels();
            loadRooms();
        });
    </script>
</body>
</html>