<?php
session_start();
require '../config/db.php';

// Check if the user is logged in as a tenant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    die("Unauthorized access");
}

$user_id = $_SESSION['user_id'];

// Fetch tenant details
$stmt = $conn->prepare("SELECT t.tenant_id, t.property_id, p.rent_amount FROM tenants t JOIN properties p ON t.property_id = p.property_id WHERE t.user_id = ?");
$stmt->execute([$user_id]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tenant) {
    die("Tenant details not found");
}

$tenant_id = $tenant['tenant_id'];
$property_id = $tenant['property_id'];
$rent_amount = $tenant['rent_amount'];

// PayPal API credentials
$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
$business_email = "your-paypal-business-email@example.com";
$return_url = "http://localhost/payment/payment_success.php";
$cancel_url = "http://localhost/payment/payment_success.php";
$notify_url = "http://localhost/payment/ipn_listener.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Payment</title>
    <link rel="stylesheet" href="style.css">
    <style type="text/css">
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    width: 400px;
    text-align: center;
    transition: transform 0.3s ease-in-out;
}

.container:hover {
    transform: scale(1.02);
}

h2 {
    color: #333;
    margin-bottom: 15px;
}

p {
    font-size: 16px;
    color: #555;
}

.payment-btn {
    display: inline-block;
    padding: 10px 20px;
    font-size: 18px;
    text-decoration: none;
    color: #fff;
    background: #0070ba;
    border-radius: 5px;
    transition: background 0.3s ease-in-out;
}

.payment-btn:hover {
    background: #005a9c;
}

.success {
    color: #28a745;
    font-weight: bold;
}

.error {
    color: #dc3545;
    font-weight: bold;
}

    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Pay Rent</h2>
        <p>Amount Due: $<?php echo number_format($rent_amount, 2); ?></p>
        <form action="<?php echo $paypal_url; ?>" method="post">
            <input type="hidden" name="business" value="<?php echo $business_email; ?>">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="item_name" value="Rent Payment">
            <input type="hidden" name="amount" value="<?php echo $rent_amount; ?>">
            <input type="hidden" name="currency_code" value="KSH">
            <input type="hidden" name="custom" value="<?php echo $tenant_id . '|' . $property_id; ?>">
            <input type="hidden" name="return" value="<?php echo $return_url; ?>">
            <input type="hidden" name="cancel_return" value="<?php echo $cancel_url; ?>">
            <input type="hidden" name="notify_url" value="<?php echo $notify_url; ?>">
            <button type="submit" class="pay-btn">Pay with PayPal</button>
        </form>
    </div>
</body>
</html>
