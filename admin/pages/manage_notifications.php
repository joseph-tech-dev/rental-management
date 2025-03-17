<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Notifications</h2>
        <button id="addComplaintBtn" class="btn">+ Add Complaint</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="notificationTable">
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="complaintModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Add Notifications</h3>
            <form id="complaintForm">
                <input type="hidden" id="notification_id">
                <label>User ID:</label>
                <input type="number" id="user_id" required>
                <label>Message:</label>
                <textarea id="message" required></textarea>
                <label>Status:</label>
                <select id="status">
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadNotifications() {
                $.ajax({
                    url: 'pages/notifications_backend.php',
                    method: 'GET',
                    success: function(response) {
                        $('#notificationTable').html(response);
                    }
                });
            }
            loadNotifications();

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
                $.post('pages/notifications_backend.php', {
                    notification_id: $('#notification_id').val(),
                    user_id: $('#user_id').val(),
                    message: $('#message').val(),
                    status: $('#status').val()
                }, function(response) {
                    Swal.fire('Success', response, 'success');
                    $('#complaintModal').hide();
                    loadNotifications();
                });
            });

            $(document).on('click', '.editBtn', function() {
                let data = $(this).data();
                $('#notification_id').val(data.id);
                $('#user_id').val(data.user);
                $('#message').val(data.message);
                $('#status').val(data.status);
                $('#modalTitle').text('Edit Complaint');
                $('#complaintModal').show();
            });

            $(document).on('click', '.deleteBtn', function() {
                let notificationId = $(this).data('id');
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
                        $.post('pages/notifications_backend.php', { delete_id: notificationId }, function(response) {
                            Swal.fire('Deleted!', response, 'success');
                            loadNotifications();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
