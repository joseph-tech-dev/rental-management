// payment.js - Handle all payment-related functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize payment data
    const payments = JSON.parse(localStorage.getItem('payments')) || [];
    
    // DOM Elements
    const paymentForm = document.getElementById('payment-form');
    const paymentList = document.getElementById('payment-list');
    const paymentSearchInput = document.getElementById('payment-search');
    const paymentSummary = document.getElementById('payment-summary');
    
    // Add new payment
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPayment = {
                id: Date.now(),
                tenantId: parseInt(document.getElementById('payment-tenant').value),
                propertyId: parseInt(document.getElementById('payment-property').value),
                amount: parseFloat(document.getElementById('payment-amount').value),
                date: document.getElementById('payment-date').value,
                method: document.getElementById('payment-method').value,
                status: document.getElementById('payment-status').value,
                notes: document.getElementById('payment-notes').value,
                recordedAt: new Date().toISOString()
            };
            
            // If editing existing payment, remove the old one
            const editId = document.getElementById('payment-id').value;
            if (editId) {
                const index = payments.findIndex(p => p.id === parseInt(editId));
                if (index !== -1) {
                    payments.splice(index, 1);
                }
                newPayment.id = parseInt(editId);
                document.getElementById('payment-submit').textContent = 'Record Payment';
                document.getElementById('payment-id').value = '';
            }
            
            payments.push(newPayment);
            savePayments();
            displayPayments();
            paymentForm.reset();
            
            // Update summary if it exists
            if (paymentSummary) {
                updatePaymentSummary();
            }
        });
    }
    
    // Display payments
    function displayPayments() {
        if (!paymentList) return;
        
        paymentList.innerHTML = '';
        
        if (payments.length === 0) {
            paymentList.innerHTML = '<tr><td colspan="8" class="text-center">No payments found</td></tr>';
            return;
        }
        
        // Get tenants and properties for name display
        const tenants = window.tenantUtils ? window.tenantUtils.getTenants() : [];
        const properties = window.propertyUtils ? window.propertyUtils.getProperties() : [];
        
        // Sort payments by date (newest first)
        const sortedPayments = [...payments].sort((a, b) => 
            new Date(b.date) - new Date(a.date)
        );
        
        sortedPayments.forEach(payment => {
            const tenant = tenants.find(t => t.id === payment.tenantId) || { name: 'Unknown' };
            const property = properties.find(p => p.id === payment.propertyId) || { name: 'Unknown' };
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${formatDate(payment.date)}</td>
                <td>${tenant.name}</td>
                <td>${property.name}</td>
                <td>$${payment.amount.toFixed(2)}</td>
                <td>${payment.method}</td>
                <td>
                    <span class="status-badge ${payment.status.toLowerCase()}">${payment.status}</span>
                </td>
                <td>${payment.notes}</td>
                <td>
                    <button class="btn-edit" data-id="${payment.id}">Edit</button>
                    <button class="btn-delete" data-id="${payment.id}">Delete</button>
                </td>
            `;
            paymentList.appendChild(row);
        });
        
        // Add event listeners to action buttons
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', editPayment);
        });
        
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', deletePayment);
        });
    }
    
    // Edit payment
    function editPayment(e) {
        const paymentId = parseInt(e.target.getAttribute('data-id'));
        const payment = payments.find(p => p.id === paymentId);
        
        // Populate form for editing
        document.getElementById('payment-tenant').value = payment.tenantId;
        document.getElementById('payment-property').value = payment.propertyId;
        document.getElementById('payment-amount').value = payment.amount;
        document.getElementById('payment-date').value = payment.date;
        document.getElementById('payment-method').value = payment.method;
        document.getElementById('payment-status').value = payment.status;
        document.getElementById('payment-notes').value = payment.notes || '';
        
        // Change form to update mode
        document.getElementById('payment-id').value = paymentId;
        document.getElementById('payment-submit').textContent = 'Update Payment';
        
        // Scroll to form
        paymentForm.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Delete payment
    function deletePayment(e) {
        if (confirm('Are you sure you want to delete this payment record?')) {
            const paymentId = parseInt(e.target.getAttribute('data-id'));
            const index = payments.findIndex(p => p.id === paymentId);
            
            if (index !== -1) {
                payments.splice(index, 1);
                savePayments();
                displayPayments();
                
                // Update summary if it exists
                if (paymentSummary) {
                    updatePaymentSummary();
                }
            }
        }
    }
    
    // Search payments
    if (paymentSearchInput) {
        paymentSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Get tenants and properties for searching by name
            const tenants = window.tenantUtils ? window.tenantUtils.getTenants() : [];
            const properties = window.propertyUtils ? window.propertyUtils.getProperties() : [];
            
            const filteredPayments = payments.filter(payment => {
                const tenant = tenants.find(t => t.id === payment.tenantId) || { name: '' };
                const property = properties.find(p => p.id === payment.propertyId) || { name: '' };
                
                return tenant.name.toLowerCase().includes(searchTerm) ||
                       property.name.toLowerCase().includes(searchTerm) ||
                       payment.method.toLowerCase().includes(searchTerm) ||
                       payment.status.toLowerCase().includes(searchTerm) ||
                       payment.notes?.toLowerCase().includes(searchTerm);
            });
            
            displayFilteredPayments(filteredPayments);
        });
    }
    
    // Display filtered payments
    function displayFilteredPayments(filteredPayments) {
        if (!paymentList) return;
        
        paymentList.innerHTML = '';
        
        if (filteredPayments.length === 0) {
            paymentList.innerHTML = '<tr><td colspan="8" class="text-center">No matching payments found</td></tr>';
            return;
        }
        
        // Get tenants and properties for name display
        const tenants = window.tenantUtils ? window.tenantUtils.getTenants() : [];
        const properties = window.propertyUtils ? window.propertyUtils.getProperties() : [];
        
        // Sort payments by date (newest first)
        const sortedPayments = [...filteredPayments].sort((a, b) => 
            new Date(b.date) - new Date(a.date)
        );
        
        sortedPayments.forEach(payment => {
            const tenant = tenants.find(t => t.id === payment.tenantId) || { name: 'Unknown' };
            const property = properties.find(p => p.id === payment.propertyId) || { name: 'Unknown' };
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${formatDate(payment.date)}</td>
                <td>${tenant.name}</td>
                <td>${property.name}</td>
                <td>$${payment.amount.toFixed(2)}</td>
                <td>${payment.method}</td>
                <td>
                    <span class="status-badge ${payment.status.toLowerCase()}">${payment.status}</span>
                </td>
                <td>${payment.notes}</td>
                <td>
                    <button class="btn-edit" data-id="${payment.id}">Edit</button>
                    <button class="btn-delete" data-id="${payment.id}">Delete</button>
                </td>
            `;
            paymentList.appendChild(row);
        });
        
        // Reattach event listeners
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', editPayment);
        });
        
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', deletePayment);
        });
    }
    
    // Update payment summary
    function updatePaymentSummary() {
        if (!paymentSummary) return;
        
        // Calculate total received payments
        const totalReceived = payments
            .filter(p => p.status === 'Paid')
            .reduce((sum, payment) => sum + payment.amount, 0);
        
        // Calculate pending payments
        const totalPending = payments
            .filter(p => p.status === 'Pending')
            .reduce((sum, payment) => sum + payment.amount, 0);
        
        // Calculate monthly totals
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();
        
        const thisMonthTotal = payments
            .filter(p => {
                const paymentDate = new Date(p.date);
                return paymentDate.getMonth() === currentMonth && 
                       paymentDate.getFullYear() === currentYear &&
                       p.status === 'Paid';
            })
            .reduce((sum, payment) => sum + payment.amount, 0);
        
        // Display summary
        paymentSummary.innerHTML = `
            <div class="summary-card">
                <h3>Total Received</h3>
                <p class="amount">$${totalReceived.toFixed(2)}</p>
            </div>
            <div class="summary-card">
                <h3>Pending Payments</h3>
                <p class="amount">$${totalPending.toFixed(2)}</p>
            </div>
            <div class="summary-card">
                <h3>This Month</h3>
                <p class="amount">$${thisMonthTotal.toFixed(2)}</p>
            </div>
        `;
    }
    
    // Populate tenant and property dropdowns if they exist
    const tenantDropdown = document.getElementById('payment-tenant');
    if (tenantDropdown && window.tenantUtils) {
        const tenants = window.tenantUtils.getTenants();
        tenantDropdown.innerHTML = '<option value="">Select a tenant</option>';
        
        tenants.forEach(tenant => {
            const option = document.createElement('option');
            option.value = tenant.id;
            option.textContent = tenant.name;
            tenantDropdown.appendChild(option);
        });
    }
    
    const propertyDropdown = document.getElementById('payment-property');
    if (propertyDropdown && window.propertyUtils) {
        const properties = window.propertyUtils.getProperties();
        propertyDropdown.innerHTML = '<option value="">Select a property</option>';
        
        properties.forEach(property => {
            const option = document.createElement('option');
            option.value = property.id;
            option.textContent = property.name;
            propertyDropdown.appendChild(option);
        });
    }
    
    // Format date for display
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }
    
    // Save payments to localStorage
    function savePayments() {
        localStorage.setItem('payments', JSON.stringify(payments));
    }
    
    // Initialize the display
    displayPayments();
    
    // Initialize the summary if it exists
    if (paymentSummary) {
        updatePaymentSummary();
    }
    
    // Export functions for use in other scripts
    window.paymentUtils = {
        getPayments: () => payments,
        getPaymentById: (id) => payments.find(p => p.id === id),
        getPaymentsByTenantId: (tenantId) => payments.filter(p => p.tenantId === tenantId),
        getPaymentsByPropertyId: (propertyId) => payments.filter(p => p.propertyId === propertyId),
        savePayments: savePayments,
        getTotalReceived: () => payments
            .filter(p => p.status === 'Paid')
            .reduce((sum, payment) => sum + payment.amount, 0)
    };
});document.addEventListener('DOMContentLoaded', function() {
    const floorSelect = document.getElementById('Floor-select');
    const roomSelect = document.getElementById('Room-select');

    floorSelect.addEventListener('change', function() {
        const selectedFloor = floorSelect.value;
        let roomOptions = '';

        switch (selectedFloor) {
            case 'Ground Floor':
                roomOptions = '<option value="Bedsitter">Bedsitter</option>';
                break;
            case 'Floor 1':
                roomOptions = '<option value="1 Bedroom">1 Bedroom</option>';
                break;
            case 'Floor 2':
                roomOptions = '<option value="2 Bedroom">2 Bedroom</option>';
                break;
            case 'Floor 3':
                roomOptions = '<option value="3 Bedroom">3 Bedroom</option>';
                break;
            default:
                roomOptions = '<option value="">Select Room</option>';
        }

        roomSelect.innerHTML = roomOptions;
    });
});