<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leases</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Leases</h2>
        <button id="addLeaseBtn" class="btn">+ Add Lease</button>
        <table>
            <thead>
                <tr>
                    <th>Lease ID</th>
                    <th>Tenant ID</th>
                    <th>Property ID</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="leaseTable">
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="leaseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Add Lease</h3>
            <form id="leaseForm">
                <input type="hidden" id="lease_id">
                <label>Tenant ID:</label>
                <input type="number" id="tenant_id" required>
                <label>Property ID:</label>
                <input type="number" id="property_id" required>
                <label>Start Date:</label>
                <input type="date" id="start_date" required>
                <label>End Date:</label>
                <input type="date" id="end_date" required>
                <label>Status:</label>
                <select id="status">
                    <option value="active">Active</option>
                    <option value="terminated">Terminated</option>
                    <option value="pending">Pending</option>
                </select>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadLeases() {
                $.ajax({
                    url: 'pages/leases.php',
                    method: 'GET',
                    success: function(response) {
                        $('#leaseTable').html(response);
                    }
                });
            }
            loadLeases();

            $('#addLeaseBtn').click(function() {
                $('#leaseForm')[0].reset();
                $('#modalTitle').text('Add Lease');
                $('#leaseModal').show();
            });

            $('.close').click(function() {
                $('#leaseModal').hide();
            });

            $('#leaseForm').submit(function(e) {
                e.preventDefault();
                $.post('pages/leases.php', {
                    lease_id: $('#lease_id').val(),
                    tenant_id: $('#tenant_id').val(),
                    property_id: $('#property_id').val(),
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    status: $('#status').val()
                }, function(response) {
                    Swal.fire('Success', response, 'success');
                    $('#leaseModal').hide();
                    loadLeases();
                });
            });

            $(document).on('click', '.editBtn', function() {
                let data = $(this).data();
                $('#lease_id').val(data.id);
                $('#tenant_id').val(data.tenant);
                $('#property_id').val(data.property);
                $('#start_date').val(data.start);
                $('#end_date').val(data.end);
                $('#status').val(data.status);
                $('#modalTitle').text('Edit Lease');
                $('#leaseModal').show();
            });

            $(document).on('click', '.deleteBtn', function() {
                let leaseId = $(this).data('id');
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
                        $.post('pages/leases.php', { lease_id: leaseId }, function(response) {
                            Swal.fire('Deleted!', response, 'success');
                            loadLeases();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
