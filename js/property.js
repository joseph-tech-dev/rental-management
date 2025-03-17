// property.js - Handle all property-related functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize property data
    const properties = JSON.parse(localStorage.getItem('properties')) || [];
    
    // DOM Elements
    const propertyForm = document.getElementById('property-form');
    const propertyList = document.getElementById('property-list');
    const propertySearchInput = document.getElementById('property-search');
    
    // Add new property
    if (propertyForm) {
        propertyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newProperty = {
                id: Date.now(),
                name: document.getElementById('property-name').value,
                address: document.getElementById('property-address').value,
                type: document.getElementById('property-type').value,
                units: parseInt(document.getElementById('property-units').value),
                rentAmount: parseFloat(document.getElementById('property-rent').value),
                status: document.getElementById('property-status').value,
                dateAdded: new Date().toISOString()
            };
            
            properties.push(newProperty);
            saveProperties();
            displayProperties();
            propertyForm.reset();
        });
    }
    
    // Display properties
    function displayProperties() {
        if (!propertyList) return;
        
        propertyList.innerHTML = '';
        
        if (properties.length === 0) {
            propertyList.innerHTML = '<tr><td colspan="6" class="text-center">No properties found</td></tr>';
            return;
        }
        
        properties.forEach(property => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${property.name}</td>
                <td>${property.address}</td>
                <td>${property.type}</td>
                <td>${property.units}</td>
                <td>$${property.rentAmount.toFixed(2)}</td>
                <td>
                    <span class="status-badge ${property.status.toLowerCase()}">${property.status}</span>
                </td>
                <td>
                    <button class="btn-edit" data-id="${property.id}">Edit</button>
                    <button class="btn-delete" data-id="${property.id}">Delete</button>
                </td>
            `;
            propertyList.appendChild(row);
        });
        
        // Add event listeners to action buttons
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', editProperty);
        });
        
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', deleteProperty);
        });
    }
    
    // Edit property
    function editProperty(e) {
        const propertyId = parseInt(e.target.getAttribute('data-id'));
        const property = properties.find(p => p.id === propertyId);
        
        // Populate form for editing
        document.getElementById('property-name').value = property.name;
        document.getElementById('property-address').value = property.address;
        document.getElementById('property-type').value = property.type;
        document.getElementById('property-units').value = property.units;
        document.getElementById('property-rent').value = property.rentAmount;
        document.getElementById('property-status').value = property.status;
        
        // Change form to update mode
        document.getElementById('property-id').value = propertyId;
        document.getElementById('property-submit').textContent = 'Update Property';
        
        // Scroll to form
        propertyForm.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Delete property
    function deleteProperty(e) {
        if (confirm('Are you sure you want to delete this property?')) {
            const propertyId = parseInt(e.target.getAttribute('data-id'));
            const index = properties.findIndex(p => p.id === propertyId);
            
            if (index !== -1) {
                properties.splice(index, 1);
                saveProperties();
                displayProperties();
            }
        }
    }
    
    // Search properties
    if (propertySearchInput) {
        propertySearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            const filteredProperties = properties.filter(property => 
                property.name.toLowerCase().includes(searchTerm) ||
                property.address.toLowerCase().includes(searchTerm) ||
                property.type.toLowerCase().includes(searchTerm)
            );
            
            propertyList.innerHTML = '';
            
            if (filteredProperties.length === 0) {
                propertyList.innerHTML = '<tr><td colspan="6" class="text-center">No matching properties found</td></tr>';
                return;
            }
            
            filteredProperties.forEach(property => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${property.name}</td>
                    <td>${property.address}</td>
                    <td>${property.type}</td>
                    <td>${property.units}</td>
                    <td>$${property.rentAmount.toFixed(2)}</td>
                    <td>
                        <span class="status-badge ${property.status.toLowerCase()}">${property.status}</span>
                    </td>
                    <td>
                        <button class="btn-edit" data-id="${property.id}">Edit</button>
                        <button class="btn-delete" data-id="${property.id}">Delete</button>
                    </td>
                `;
                propertyList.appendChild(row);
            });
            
            // Reattach event listeners to action buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', editProperty);
            });
            
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', deleteProperty);
            });
        });
    }
    
    // Save properties to localStorage
    function saveProperties() {
        localStorage.setItem('properties', JSON.stringify(properties));
    }
    
    // Initialize the display
    displayProperties();
    
    // Export functions for use in other scripts
    window.propertyUtils = {
        getProperties: () => properties,
        getPropertyById: (id) => properties.find(p => p.id === id),
        saveProperties: saveProperties
    };
});