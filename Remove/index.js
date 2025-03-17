// index.js - Handle room selection and tenant registration
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const propertySelect = document.getElementById('property-select');
    const roomsSection = document.getElementById('rooms-section');
    const roomsContainer = document.getElementById('rooms-container');
    const tenantForm = document.getElementById('tenant-form');
    const reservationForm = document.getElementById('reservation-form');
    const backToRoomsBtn = document.getElementById('back-to-rooms');
    const selectedRoomNumber = document.getElementById('selected-room-number');
    const roomIdInput = document.getElementById('room-id');
    const errorContainer = document.getElementById('error-container');
    const successMessage = document.getElementById('success-message');
    
    // Data storage
    let properties = JSON.parse(localStorage.getItem('properties')) || [];
    let rooms = JSON.parse(localStorage.getItem('rooms')) || [];
    let tenants = JSON.parse(localStorage.getItem('tenants')) || [];
    
    // Initialize with some sample data if none exists
    if (properties.length === 0) {
        initializeSampleData();
    }
    
    // Populate property dropdown
    function populatePropertySelect() {
        propertySelect.innerHTML = '<option value="">-- Select a property --</option>';
        
        properties.forEach(property => {
            const option = document.createElement('option');
            option.value = property.id;
            option.textContent = property.name;
            propertySelect.appendChild(option);
        });
    }
    
    // Display rooms for selected property
    function displayRooms(propertyId) {
        roomsContainer.innerHTML = '';
        
        const propertyRooms = rooms.filter(room => room.propertyId === parseInt(propertyId));
        
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
        
        // Add event listeners to select buttons
        document.querySelectorAll('.select-btn').forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', selectRoom);
            }
        });
    }
    
    // Handle room selection
    function selectRoom(e) {
        const roomId = parseInt(e.target.getAttribute('data-room-id'));
        const selectedRoom = rooms.find(room => room.id === roomId);
        
        if (selectedRoom) {
            // Show tenant form with selected room info
            roomIdInput.value = roomId;
            selectedRoomNumber.textContent = selectedRoom.roomNumber;
            
            // Set minimum move-in date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('move-in-date').min = today;
            
            // Show tenant form, hide rooms
            roomsSection.style.display = 'none';
            tenantForm.style.display = 'block';
            
            // Scroll to form
            tenantForm.scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    // Handle tenant form submission
    function handleReservation(e) {
        e.preventDefault();
        
        const roomId = parseInt(roomIdInput.value);
        const selectedRoom = rooms.find(room => room.id === roomId);
        
        if (!selectedRoom) {
            showError('Room not found. Please try again.');
            return;
        }
        
        // Get form data
        const name = document.getElementById('tenant-name').value;
        const email = document.getElementById('tenant-email').value;
        const phone = document.getElementById('tenant-phone').value;
        const moveInDate = document.getElementById('move-in-date').value;
        const leaseDuration = parseInt(document.getElementById('lease-duration').value);
        
        // Calculate lease end date
        const startDate = new Date(moveInDate);
        const endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + leaseDuration);
        
        // Create new tenant
        const newTenant = {
            id: Date.now(),
            name: name,
            email: email,
            phone: phone,
            propertyId: selectedRoom.propertyId,
            roomId: roomId,
            leaseStart: moveInDate,
            leaseEnd: endDate.toISOString().split('T')[0],
            rentAmount: selectedRoom.rentAmount,
            status: 'Active',
            dateAdded: new Date().toISOString()
        };
        
        // Update room status
        const roomIndex = rooms.findIndex(room => room.id === roomId);
        if (roomIndex !== -1) {
            rooms[roomIndex].status = 'Occupied';
            rooms[roomIndex].tenantId = newTenant.id;
        }
        
        // Save data
        tenants.push(newTenant);
        saveTenants();
        saveRooms();
        
        // Show success message
        showSuccess(`Room ${selectedRoom.roomNumber} has been reserved successfully for ${name}.`);
        
        // Reset form and go back to room selection
        resetForm();
    }
    
    // Go back to room selection
    function goBackToRooms() {
        tenantForm.style.display = 'none';
        roomsSection.style.display = 'block';
        resetForm();
    }
    
    // Reset tenant form
    function resetForm() {
        reservationForm.reset();
        roomIdInput.value = '';
        selectedRoomNumber.textContent = '';
    }
    
    // Show error message
    function showError(message) {
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            errorContainer.style.display = 'none';
        }, 5000);
    }
    
    // Show success message
    function showSuccess(message) {
        successMessage.textContent = message;
        successMessage.style.display = 'block';
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 5000);
    }
    
    // Initialize sample data if needed
    function initializeSampleData() {
        // Sample properties
        properties = [
            {
                id: 1,
                name: 'Sunset Apartments',
                address: '123 Main St, Anytown, USA',
                type: 'Apartment Complex',
                units: 20,
                rentAmount: 1200,
                status: 'Active',
                dateAdded: new Date().toISOString()
            },
            {
                id: 2,
                name: 'Riverside Residences',
                address: '456 River Rd, Anytown, USA',
                type: 'Apartment Complex',
                units: 15,
                rentAmount: 1500,
                status: 'Active',
                dateAdded: new Date().toISOString()
            }
        ];
        
        // Sample rooms for Sunset Apartments
        const sunsetRooms = [];
        for (let i = 1; i <= 10; i++) {
            sunsetRooms.push({
                id: i,
                propertyId: 1,
                roomNumber: `${Math.floor((i-1)/4) + 1}${String.fromCharCode(65 + ((i-1) % 4))}`, // 1A, 1B, 1C, 1D, 2A, etc.
                type: i % 3 === 0 ? 'Studio' : i % 3 === 1 ? '1 Bedroom' : '2 Bedroom',
                floor: Math.floor((i-1)/4) + 1,
                description: `Comfortable ${i % 3 === 0 ? 'studio' : i % 3 === 1 ? 'one bedroom' : 'two bedroom'} apartment with modern amenities.`,
                rentAmount: 1000 + (i % 3 * 200),
                status: i % 5 === 0 ? 'Occupied' : 'Vacant',
                tenantId: i % 5 === 0 ? 1000 + i : null
            });
        }
        
        // Sample rooms for Riverside Residences
        const riversideRooms = [];
        for (let i = 1; i <= 8; i++) {
            riversideRooms.push({
                id: i + 10,
                propertyId: 2,
                roomNumber: `${Math.floor((i-1)/4) + 1}${String.fromCharCode(65 + ((i-1) % 4))}`, // 1A, 1B, 1C, 1D, 2A, etc.
                type: i % 3 === 0 ? 'Studio' : i % 3 === 1 ? '1 Bedroom' : '2 Bedroom',
                floor: Math.floor((i-1)/4) + 1,
                description: `Spacious ${i % 3 === 0 ? 'studio' : i % 3 === 1 ? 'one bedroom' : 'two bedroom'} with river view.`,
                rentAmount: 1200 + (i % 3 * 250),
                status: i % 4 === 0 ? 'Occupied' : 'Vacant',
                tenantId: i % 4 === 0 ? 2000 + i : null
            });
        }
        
        rooms = [...sunsetRooms, ...riversideRooms];
        
        // Save to localStorage
        saveProperties();
        saveRooms();
    }
    
    // Save data to localStorage
    function saveProperties() {
        localStorage.setItem('properties', JSON.stringify(properties));
    }
    
    function saveRooms() {
        localStorage.setItem('rooms', JSON.stringify(rooms));
    }
    
    function saveTenants() {
        localStorage.setItem('tenants', JSON.stringify(tenants));
    }
    
    // Event listeners
    propertySelect.addEventListener('change', function() {
        const propertyId = this.value;
        
        if (propertyId) {
            roomsSection.style.display = 'block';
            displayRooms(propertyId);
        } else {
            roomsSection.style.display = 'none';
        }
    });
    
    reservationForm.addEventListener('submit', handleReservation);
    backToRoomsBtn.addEventListener('click', goBackToRooms);
    
    // Initialize the page
    populatePropertySelect();
    
    // Export functions for use in other scripts
    window.roomUtils = {
        getRooms: () => rooms,
        getRoomById: (id) => rooms.find(room => room.id === id),
        getRoomsByPropertyId: (propertyId) => rooms.filter(room => room.propertyId === propertyId),
        getVacantRooms: () => rooms.filter(room => room.status === 'Vacant'),
        getOccupiedRooms: () => rooms.filter(room => room.status === 'Occupied'),
        saveRooms: saveRooms
    };
});