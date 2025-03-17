<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tenants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Tenants</h2>
        <button id="addTenantBtn" class="btn">+ Add Tenant</button>
        <table>
            <thead>
                <tr>
                    <th>Tenant ID</th>
                    <th>User ID</th>
                    <th>Lease Start</th>
                    <th>Lease End</th>
                    <th>Property ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tenantTable">
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="tenantModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Add Tenant</h3>
            <form id="tenantForm">
                <input type="hidden" id="tenant_id">
                <label>User ID:</label>
                <input type="number" id="user_id" required>
                <label>Lease Start:</label>
                <input type="date" id="lease_start" required>
                <label>Lease End:</label>
                <input type="date" id="lease_end" required>
                <label>Property ID:</label>
                <input type="number" id="property_id" required>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadTenants() {
                $.ajax({
                    url: 'pages/tenants.php',
                    method: 'GET',
                    success: function(response) {
                        $('#tenantTable').html(response);
                    }
                });
            }
            loadTenants();

            $('#addTenantBtn').click(function() {
                $('#tenantForm')[0].reset();
                $('#modalTitle').text('Add Tenant');
                $('#tenantModal').show();
            });

            $('.close').click(function() {
                $('#tenantModal').hide();
            });

            $('#tenantForm').submit(function(e) {
                e.preventDefault();
                $.post('pages/tenants.php', {
                    tenant_id: $('#tenant_id').val(),
                    user_id: $('#user_id').val(),
                    lease_start: $('#lease_start').val(),
                    lease_end: $('#lease_end').val(),
                    property_id: $('#property_id').val()
                }, function(response) {
                    Swal.fire('Success', response, 'success');
                    $('#tenantModal').hide();
                    loadTenants();
                });
            });

            $(document).on('click', '.editBtn', function() {
                let data = $(this).data();
                $('#tenant_id').val(data.id);
                $('#user_id').val(data.user);
                $('#lease_start').val(data.start);
                $('#lease_end').val(data.end);
                $('#property_id').val(data.property);
                $('#modalTitle').text('Edit Tenant');
                $('#tenantModal').show();
            });

            $(document).on('click', '.deleteBtn', function() {
                let tenantId = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('pages/tenants.php', { tenant_id: tenantId }, function(response) {
                            Swal.fire('Deleted!', response, 'success');
                            loadTenants();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
