<?php
session_start();
require '../config/db.php';

// Check if PayPal response is valid
if (!isset($_GET['tx']) || !isset($_GET['st'])) {
    die("Invalid payment response.");
}

$transaction_id = $_GET['tx'];
$payment_status = $_GET['st'];
$amount_paid = $_GET['amt'] ?? 0;
$currency = $_GET['cc'] ?? '';
$tenant_property = isset($_GET['cm']) ? explode('|', $_GET['cm']) : [0, 0];
$tenant_id = $tenant_property[0];
$property_id = $tenant_property[1];

if ($payment_status === 'Completed') {
    // Verify the transaction doesn't already exist
    $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ?");
    $stmt->execute([$transaction_id]);
    if ($stmt->rowCount() > 0) {
        die("Duplicate transaction detected.");
    }

    // Insert payment record
    $stmt = $conn->prepare("INSERT INTO payments (tenant_id, property_id, amount, payment_status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$tenant_id, $property_id, $amount_paid, 'paid']);

    // Log transaction
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_type) VALUES (?, ?, ?)");
    $stmt->execute([$tenant_id, $amount_paid, 'rent_payment']);

    echo "<h2>Payment Successful</h2>";
    echo "<p>Transaction ID: $transaction_id</p>";
    echo "<p>Amount Paid: $amount_paid $currency</p>";
    echo "<a href='dashboard.php'>Go to Dashboard</a>";
} else {
    echo "<h2>Payment Canceled</h2>";
    echo "<p>Your transaction was not completed.</p>";
    echo "<a href='dashboard.php'>Go Back</a>";
}
?>
