<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Maintenance Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Maintenance Requests</h2>
        <button id="addRequestBtn" class="btn">+ Add Request</button>
        <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Tenant ID</th>
                    <th>Property ID</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="requestTable">
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="requestModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Add Maintenance Request</h3>
            <form id="requestForm">
                <input type="hidden" id="request_id">
                <label>Tenant ID:</label>
                <input type="number" id="tenant_id" required>
                <label>Property ID:</label>
                <input type="number" id="property_id" required>
                <label>Description:</label>
                <textarea id="description" required></textarea>
                <label>Status:</label>
                <select id="status">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                </select>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadRequests() {
                $.ajax({
                    url: 'pages/maintenance_requests_handler.php',
                    method: 'GET',
                    success: function(response) {
                        $('#requestTable').html(response);
                    }
                });
            }
            loadRequests();

            $('#addRequestBtn').click(function() {
                $('#requestForm')[0].reset();
                $('#modalTitle').text('Add Maintenance Request');
                $('#requestModal').show();
            });

            $('.close').click(function() {
                $('#requestModal').hide();
            });

            $('#requestForm').submit(function(e) {
                e.preventDefault();
                $.post('pages/maintenance_requests_handler.php', {
                    request_id: $('#request_id').val(),
                    tenant_id: $('#tenant_id').val(),
                    property_id: $('#property_id').val(),
                    description: $('#description').val(),
                    status: $('#status').val()
                }, function(response) {
                    Swal.fire('Success', response, 'success');
                    $('#requestModal').hide();
                    loadRequests();
                });
            });

            $(document).on('click', '.editBtn', function() {
                let data = $(this).data();
                $('#request_id').val(data.id);
                $('#tenant_id').val(data.tenant);
                $('#property_id').val(data.property);
                $('#description').val(data.description);
                $('#status').val(data.status);
                $('#modalTitle').text('Edit Maintenance Request');
                $('#requestModal').show();
            });

            $(document).on('click', '.deleteBtn', function() {
                let requestId = $(this).data('id');
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
                        $.post('pages/maintenance_requests_handler.php', { request_id: requestId }, function(response) {
                            Swal.fire('Deleted!', response, 'success');
                            loadRequests();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
