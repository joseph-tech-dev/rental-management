<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Transactions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Transactions</h2>
        <button id="addTransactionBtn" class="btn">+ Add Transaction</button>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Tenant ID</th>
                    <th>Property ID</th>
                    <th>Amount</th>
                    <th>Transaction Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="transactionTable">
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="transactionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Add Transaction</h3>
            <form id="transactionForm">
                <input type="hidden" id="transaction_id">
                <label>Tenant ID:</label>
                <input type="number" id="tenant_id" required>
                <label>Property ID:</label>
                <input type="number" id="property_id" required>
                <label>Amount:</label>
                <input type="number" id="amount" required>
                <label>Status:</label>
                <select id="status">
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadTransactions() {
                $.ajax({
                    url: 'pages/transactions_handler.php',
                    method: 'GET',
                    success: function(response) {
                        $('#transactionTable').html(response);
                    }
                });
            }
            loadTransactions();

            $('#addTransactionBtn').click(function() {
                $('#transactionForm')[0].reset();
                $('#modalTitle').text('Add Transaction');
                $('#transactionModal').show();
            });

            $('.close').click(function() {
                $('#transactionModal').hide();
            });

            $('#transactionForm').submit(function(e) {
                e.preventDefault();
                $.post('pages/transactions_handler.php', {
                    transaction_id: $('#transaction_id').val(),
                    tenant_id: $('#tenant_id').val(),
                    property_id: $('#property_id').val(),
                    amount: $('#amount').val(),
                    status: $('#status').val()
                }, function(response) {
                    Swal.fire('Success', response, 'success');
                    $('#transactionModal').hide();
                    loadTransactions();
                });
            });

            $(document).on('click', '.editBtn', function() {
                let data = $(this).data();
                $('#transaction_id').val(data.id);
                $('#tenant_id').val(data.tenant);
                $('#property_id').val(data.property);
                $('#amount').val(data.amount);
                $('#status').val(data.status);
                $('#modalTitle').text('Edit Transaction');
                $('#transactionModal').show();
            });

            $(document).on('click', '.deleteBtn', function() {
                let transactionId = $(this).data('id');
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
                        $.post('pages/transactions_handler.php', { transaction_id: transactionId }, function(response) {
                            Swal.fire('Deleted!', response, 'success');
                            loadTransactions();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
