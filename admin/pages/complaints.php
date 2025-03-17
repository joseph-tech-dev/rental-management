<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Complaints</h2>
        <button id="addComplaintBtn" class="btn">+ Add Complaint</button>
        <table>
            <thead>
                <tr>
                    <th>Complaint ID</th>
                    <th>Tenant ID</th>
                    <th>Property ID</th>
                    <th>Complaint Text</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="complaintsTable">
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="complaintModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Add Complaint</h3>
            <form id="complaintForm">
                <input type="hidden" id="complaint_id">
                <label>Tenant ID:</label>
                <input type="number" id="tenant_id" required>
                <label>Property ID:</label>
                <input type="number" id="property_id" required>
                <label>Complaint:</label>
                <textarea id="complaint_text" required></textarea>
                <label>Status:</label>
                <select id="status">
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                </select>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadComplaints() {
                $.ajax({
                    url: 'pages/complaints_handler.php',
                    method: 'GET',
                    success: function(response) {
                        $('#complaintsTable').html(response);
                    }
                });
            }
            loadComplaints();

            $('#addComplaintBtn').click(function() {
                $('#complaintForm')[0].reset();
                $('#modalTitle').text('Add Complaint');
                $('#complaintModal').show();
            });

            $('.close').click(function() {
                $('#complaintModal').hide();
            });

            $('#complaintForm').submit(function(e) {
                e.preventDefault();
                $.post('pages/complaints_handler.php', {
                    complaint_id: $('#complaint_id').val(),
                    tenant_id: $('#tenant_id').val(),
                    property_id: $('#property_id').val(),
                    complaint_text: $('#complaint_text').val(),
                    status: $('#status').val()
                }, function(response) {
                    Swal.fire('Success', response, 'success');
                    $('#complaintModal').hide();
                    loadComplaints();
                });
            });

            $(document).on('click', '.editBtn', function() {
                let data = $(this).data();
                $('#complaint_id').val(data.id);
                $('#tenant_id').val(data.tenant);
                $('#property_id').val(data.property);
                $('#complaint_text').val(data.text);
                $('#status').val(data.status);
                $('#modalTitle').text('Edit Complaint');
                $('#complaintModal').show();
            });

            $(document).on('click', '.deleteBtn', function() {
                let complaintId = $(this).data('id');
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
                        $.post('pages/complaints_handler.php', { complaint_id: complaintId }, function(response) {
                            Swal.fire('Deleted!', response, 'success');
                            loadComplaints();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
