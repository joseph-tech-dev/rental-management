<!-- filepath: c:\xampp\htdocs\project\index.php -->
<?php
include 'db.php';

// Fetch properties from the database
$properties = [];
$rooms = [];
$tenants = [];

$sql = "SELECT * FROM properties";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}

// Fetch rooms from the database
$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}

// Fetch tenants from the database
$sql = "SELECT * FROM tenants";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tenants[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Selection and Tenant Registration</title>
    <style>
        .room-card { /* Add your styles here */ }
        .vacant { /* Add your styles here */ }
        .occupied { /* Add your styles here */ }
        .form-group { /* Add your styles here */ }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Room Selection and Tenant Registration</h1>
        
        <!-- Property Selection -->
        <div>
            <label for="property-select">Select Property:</label>
            <select id="property-select">
                <option value="">-- Select a property --</option>
                <?php foreach ($properties as $property): ?>
                    <option value="<?php echo $property['id']; ?>"><?php echo $property['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Rooms Section -->
        <div id="rooms-section" style="display: none;">
            <h2>Available Rooms</h2>
            <div id="rooms-container"></div>
        </div>
        
        <!-- Tenant Form -->
        <div id="tenant-form" style="display: none;">
            <h2>Tenant Registration</h2>
            <form id="reservation-form" method="post" action="reserve.php">
                <input type="hidden" id="room-id" name="room-id">
                <div class="form-group">
                    <label for="tenant-name">Name:</label>
                    <input type="text" id="tenant-name" name="tenant-name" required>
                </div>
                <div class="form-group">
                    <label for="tenant-email">Email:</label>
                    <input type="email" id="tenant-email" name="tenant-email" required>
                </div>
                <div class="form-group">
                    <label for="tenant-phone">Phone:</label>
                    <input type="tel" id="tenant-phone" name="tenant-phone" required>
                </div>
                <div class="form-group">
                    <label for="move-in-date">Move-in Date:</label>
                    <input type="date" id="move-in-date" name="move-in-date" required>
                </div>
                <div class="form-group">
                    <label for="lease-duration">Lease Duration (months):</label>
                    <input type="number" id="lease-duration" name="lease-duration" required>
                </div>
                <button type="submit">Reserve Room</button>
            </form>
            <button id="back-to-rooms">Back to Rooms</button>
        </div>
        
        <!-- Error and Success Messages -->
        <div id="error-container" class="error" style="display: none;"></div>
        <div id="success-message" class="success" style="display: none;"></div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const propertySelect = document.getElementById('property-select');
            const roomsSection = document.getElementById('rooms-section');
            const roomsContainer = document.getElementById('rooms-container');
            const tenantForm = document.getElementById('tenant-form');
            const reservationForm = document.getElementById('reservation-form');
            const backToRoomsBtn = document.getElementById('back-to-rooms');
            const roomIdInput = document.getElementById('room-id');
            const errorContainer = document.getElementById('error-container');
            const successMessage = document.getElementById('success-message');
            
            propertySelect.addEventListener('change', function() {
                const propertyId = this.value;
                
                if (propertyId) {
                    roomsSection.style.display = 'block';
                    displayRooms(propertyId);
                } else {
                    roomsSection.style.display = 'none';
                }
            });
            
            backToRoomsBtn.addEventListener('click', function() {
                tenantForm.style.display = 'none';
                roomsSection.style.display = 'block';
                reservationForm.reset();
                roomIdInput.value = '';
            });
            
            function displayRooms(propertyId) {
                roomsContainer.innerHTML = '';
                
                const propertyRooms = <?php echo json_encode($rooms); ?>.filter(room => room.propertyId == propertyId);
                
                if (propertyRooms.length === 0) {
                    roomsContainer.innerHTML = '<p>No rooms available for this property.</p>';
                    return;
                }
                
                propertyRooms.forEach(room => {
                    const roomCard = document.createElement('div');
                    roomCard.className = 'room-card';
                    
                    const statusClass = room.status === 'Vacant' ? 'vacant' : 'occupied';
                    const isDisabled = room.status !== 'Vacant';
                    
                    roomCard.innerHTML = `
                        <div class="room-header">Room ${room.roomNumber}</div>
                        <div class="room-details">
                            <p>${room.description}</p>
                            <p>Type: ${room.type}</p>
                            <p>Floor: ${room.floor}</p>
                            <p class="room-price">$${room.rentAmount.toFixed(2)}/month</p>
                            <div class="room-status ${statusClass}">${room.status}</div>
                        </div>
                        <button class="select-btn" data-room-id="${room.id}" ${isDisabled ? 'disabled' : ''}>
                            ${isDisabled ? 'Not Available' : 'Select Room'}
                        </button>
                    `;
                    
                    roomsContainer.appendChild(roomCard);
                });
                
                document.querySelectorAll('.select-btn').forEach(btn => {
                    if (!btn.disabled) {
                        btn.addEventListener('click', function(e) {
                            const roomId = e.target.getAttribute('data-room-id');
                            roomIdInput.value = roomId;
                            tenantForm.style.display = 'block';
                            roomsSection.style.display = 'none';
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>