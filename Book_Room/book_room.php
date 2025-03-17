<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$roomId = isset($_GET['room_id']) ? $_GET['room_id'] : null;
$errors = [];
$success = '';

// Fetch room details
if ($roomId) {
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $stmt->close();
    
    if (!$room) {
        $errors[] = "Room not found";
    }
}

// Process booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_room'])) {
    $roomId = $_POST['room_id'];
    $checkInDate = $_POST['check_in_date'];
    $checkOutDate = $_POST['check_out_date'];
    
    // Validation
    if (empty($checkInDate)) {
        $errors[] = "Check-in date is required";
    }
    
    if (empty($checkOutDate)) {
        $errors[] = "Check-out date is required";
    }
    
    // Validate check-in date is not in the past
    $today = date('Y-m-d');
    if ($checkInDate < $today) {
        $errors[] = "Check-in date cannot be in the past";
    }
    
    // Validate check-out date is after check-in date
    if ($checkOutDate <= $checkInDate) {
        $errors[] = "Check-out date must be after check-in date";
    }
    
    // Check if room exists and is vacant
    $stmt = $conn->prepare("SELECT status FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $stmt->close();
    
    if (!$room) {
        $errors[] = "Room not found";
    } elseif ($room['status'] !== 'vacant') {
        $errors[] = "This room is already occupied";
    }
    
    // Check if room is available for the selected dates
    $stmt = $conn->prepare("SELECT * FROM bookings 
                           WHERE room_id = ? 
                           AND status = 'active'
                           AND (
                               (check_in_date <= ? AND check_out_date >= ?) OR
                               (check_in_date <= ? AND check_out_date >= ?) OR
                               (check_in_date >= ? AND check_out_date <= ?)
                           )");
    $stmt->bind_param("issssss", $roomId, $checkOutDate, $checkInDate, $checkInDate, $checkInDate, $checkInDate, $checkOutDate);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Room is not available for the selected dates";
    }
    $stmt->close();
    
    // If no errors, create booking and update room status
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        try {
            // Create booking record
            $stmt = $conn->prepare("INSERT INTO bookings (room_id, user_id, check_in_date, check_out_date, status) 
                                  VALUES (?, ?, ?, ?, 'active')");
            $stmt->bind_param("iiss", $roomId, $userId, $checkInDate, $checkOutDate);
            $stmt->execute();
            $bookingId = $conn->insert_id;
            $stmt->close();
            
            // Update room status to occupied
            $stmt = $conn->prepare("UPDATE rooms SET status = 'occupied' WHERE id = ?");
            $stmt->bind_param("i", $roomId);
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            $success = "Room booked successfully! Your booking ID is: " . $bookingId;
        } catch (Exception $e) {
            // Rollback in case of error
            $conn->rollback();
            $errors[] = "Booking failed: " . $e->getMessage();
        }
    }
}

// Fetch all available rooms for selection
$stmt = $conn->prepare("SELECT * FROM rooms WHERE status = 'vacant'");
$stmt->execute();
$availableRooms = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3>Book a Room</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                                <p class="mt-2">
                                    <a href="dashboard.php" class="btn btn-primary btn-sm">Return to Dashboard</a>
                                </p>
                            </div>
                        <?php else: ?>
                            <form method="post" action="book_room.php">
                                <div class="mb-3">
                                    <label for="room_id" class="form-label">Select Room</label>
                                    <select class="form-select" id="room_id" name="room_id" required>
                                        <option value="" selected disabled>-- Select a Room --</option>
                                        <?php while ($room = $availableRooms->fetch_assoc()): ?>
                                            <option value="<?php echo $room['id']; ?>" <?php echo (isset($roomId) && $roomId == $room['id']) ? 'selected' : ''; ?>>
                                                Room <?php echo htmlspecialchars($room['room_number']); ?> - $<?php echo htmlspecialchars($room['price']); ?> per night
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="check_in_date" class="form-label">Check-in Date</label>
                                    <input type="date" class="form-control" id="check_in_date" name="check_in_date" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="check_out_date" class="form-label">Check-out Date</label>
                                    <input type="date" class="form-control" id="check_out_date" name="check_out_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="book_room" class="btn btn-primary">Book Room</button>
                                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add client-side validation
        document.addEventListener('DOMContentLoaded', function() {
            const checkInDateInput = document.getElementById('check_in_date');
            const checkOutDateInput = document.getElementById('check_out_date');
            
            checkInDateInput.addEventListener('change', function() {
                // Set min value for checkout date to be one day after check-in
                const checkInDate = new Date(this.value);
                const nextDay = new Date(checkInDate);
                nextDay.setDate(checkInDate.getDate() + 1);
                
                // Format the date to YYYY-MM-DD
                const nextDayStr = nextDay.toISOString().split('T')[0];
                checkOutDateInput.min = nextDayStr;
                
                // If check-out date is now invalid, reset it
                if (checkOutDateInput.value && new Date(checkOutDateInput.value) <= new Date(this.value)) {
                    checkOutDateInput.value = nextDayStr;
                }
            });
        });
    </script>
</body>
</html>

