<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration
require_once 'config.php';

// Initialize variables
$error = '';
$success = '';
$booking = null;
$room = null;

// Check if booking_id is provided
if (isset($_GET['booking_id']) && !empty($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];
    
    // Retrieve booking details
    $stmt = $conn->prepare("SELECT b.*, r.room_number, r.price 
                           FROM bookings b 
                           JOIN rooms r ON b.room_id = r.id 
                           WHERE b.id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        
        // Check if booking belongs to the logged-in user or if user is admin
        if ($booking['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] != 'admin') {
            $error = "You don't have permission to access this booking.";
        } else {
            // Calculate total days and amount
            $check_in_date = new DateTime($booking['check_in_date']);
            $check_out_date = new DateTime($booking['check_out_date']);
            $interval = $check_in_date->diff($check_out_date);
            $total_days = $interval->days;
            
            // If checkout is today and no days calculated, set to 1 day minimum
            if ($total_days == 0) {
                $total_days = 1;
            }
            
            $total_amount = $total_days * $booking['price'];
            
            // Process checkout if form is submitted
            if (isset($_POST['checkout'])) {
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Update room status to vacant
                    $updateRoomStmt = $conn->prepare("UPDATE rooms SET status = 'vacant' WHERE id = ?");
                    $updateRoomStmt->bind_param("i", $booking['room_id']);
                    $updateRoomStmt->execute();
                    
                    // Update booking status to completed
                    $updateBookingStmt = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
                    $updateBookingStmt->bind_param("i", $booking_id);
                    $updateBookingStmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    
                    $success = "Checkout successful! Room is now vacant.";
                    
                    // Redirect to payment page if requested
                    if (isset($_POST['proceed_to_payment'])) {
                        header("Location: payment.php?booking_id=" . $booking_id . "&amount=" . $total_amount);
                        exit();
                    }
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $error = "Error during checkout: " . $e->getMessage();
                }
            }
        }
    } else {
        $error = "Booking not found.";
    }
} else {
    $error = "Booking ID is required.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Room Checkout</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($booking && empty($error)): ?>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>Booking Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Room Number</h6>
                            <p><?php echo htmlspecialchars($booking['room_number']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Booking Status</h6>
                            <p>
                                <span class="badge <?php echo ($booking['status'] == 'active') ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Check-In Date</h6>
                            <p><?php echo date('F j, Y', strtotime($booking['check_in_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Check-Out Date</h6>
                            <p><?php echo date('F j, Y', strtotime($booking['check_out_date'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Total Days</h6>
                            <p><?php echo $total_days; ?> day(s)</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Price Per Day</h6>
                            <p>$<?php echo number_format($booking['price'], 2); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Total Amount Due</h5>
                            <h3 class="text-primary">$<?php echo number_format($total_amount, 2); ?></h3>
                        </div>
                    </div>
                    
                    <?php if ($booking['status'] == 'active'): ?>
                        <form method="post" action="">
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="checkout" class="btn btn-primary">Confirm Checkout</button>
                                <button type="submit" name="checkout" value="1" class="btn btn-success" name="proceed_to_payment">Checkout & Proceed to Payment</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            This booking has already been checked out.
                            <?php if ($booking['status'] == 'completed'): ?>
                                <a href="payment.php?booking_id=<?php echo $booking_id; ?>&amount=<?php echo $total_amount; ?>" class="btn btn-sm btn-success ms-3">View/Make Payment</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

