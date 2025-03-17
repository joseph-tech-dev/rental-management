<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Payments</h2>
        <button id="addPaymentBtn" class="btn">+ Add Payment</button>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Tenant ID</th>
                    <th>Property ID</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="paymentTable">
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Add Payment</h3>
            <form id="paymentForm">
                <input type="hidden" id="payment_id">
                <label>Tenant ID:</label>
                <input type="number" id="tenant_id" required>
                <label>Property ID:</label>
                <input type="number" id="property_id" required>
                <label>Amount:</label>
                <input type="number" id="amount" step="0.01" required>
                <label>Status:</label>
                <select id="payment_status">
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="overdue">Overdue</option>
                </select>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadPayments() {
                $.ajax({
                    url: 'pages/payment.php',
                    method: 'GET',
                    success: function(response) {
                        $('#paymentTable').html(response);
                    }
                });
            }
            loadPayments();

            $('#addPaymentBtn').click(function() {
                $('#paymentForm')[0].reset();
                $('#modalTitle').text('Add Payment');
                $('#paymentModal').show();
            });

            $('.close').click(function() {
                $('#paymentModal').hide();
            });

            $('#paymentForm').submit(function(e) {
                e.preventDefault();
                $.post('pages/payment.php', {
                    payment_id: $('#payment_id').val(),
                    tenant_id: $('#tenant_id').val(),
                    property_id: $('#property_id').val(),
                    amount: $('#amount').val(),
                    payment_status: $('#payment_status').val()
                }, function(response) {
                    Swal.fire('Success', response, 'success');
                    $('#paymentModal').hide();
                    loadPayments();
                });
            });

            $(document).on('click', '.editBtn', function() {
                let data = $(this).data();
                $('#payment_id').val(data.id);
                $('#tenant_id').val(data.tenant);
                $('#property_id').val(data.property);
                $('#amount').val(data.amount);
                $('#payment_status').val(data.status);
                $('#modalTitle').text('Edit Payment');
                $('#paymentModal').show();
            });

            $(document).on('click', '.deleteBtn', function() {
                let paymentId = $(this).data('id');
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
                        $.post('pages/payment.php', { payment_id: paymentId }, function(response) {
                            Swal.fire('Deleted!', response, 'success');
                            loadPayments();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>