<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Properties</h2>
        <button id="addPropertyBtn" class="btn">+ Add Property</button>
        <table>
            <thead>
                <tr>
                    <th>Property ID</th>
                    <th>Landlord ID</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Rent Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="propertyTable">
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="propertyModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Add Property</h3>
            <form id="propertyForm">
                <input type="hidden" id="property_id">
                <label>Landlord ID:</label>
                <input type="number" id="landlord_id" required>
                <label>Name:</label>
                <input type="text" id="name" required>
                <label>Address:</label>
                <input type="text" id="address" required>
                <label>Type:</label>
                <input type="text" id="type" required>
                <label>Status:</label>
                <input type="text" id="status" required>
                <label>Rent Amount:</label>
                <input type="number" id="rent_amount" required>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadProperties() {
                $.ajax({
                    url: 'pages/property.php',
                    method: 'GET',
                    success: function(response) {
                        $('#propertyTable').html(response);
                    }
                });
            }
            loadProperties();

            $('#addPropertyBtn').click(function() {
                $('#propertyForm')[0].reset();
                $('#modalTitle').text('Add Property');
                $('#propertyModal').show();
            });

            $('.close').click(function() {
                $('#propertyModal').hide();
            });

            $('#propertyForm').submit(function(e) {
                e.preventDefault();
                $.post('pages/property.php', {
                    property_id: $('#property_id').val(),
                    landlord_id: $('#landlord_id').val(),
                    name: $('#name').val(),
                    address: $('#address').val(),
                    type: $('#type').val(),
                    status: $('#status').val(),
                    rent_amount: $('#rent_amount').val()
                }, function(response) {
                    Swal.fire('Success', response, 'success');
                    $('#propertyModal').hide();
                    loadProperties();
                });
            });

            $(document).on('click', '.editBtn', function() {
                let data = $(this).data();
                $('#property_id').val(data.id);
                $('#landlord_id').val(data.landlord);
                $('#name').val(data.name);
                $('#address').val(data.address);
                $('#type').val(data.type);
                $('#status').val(data.status);
                $('#rent_amount').val(data.rent);
                $('#modalTitle').text('Edit Property');
                $('#propertyModal').show();
            });

            $(document).on('click', '.deleteBtn', function() {
                let propertyId = $(this).data('id');
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
                        $.post('pages/property.php', { property_id: propertyId }, function(response) {
                            Swal.fire('Deleted!', response, 'success');
                            loadProperties();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
