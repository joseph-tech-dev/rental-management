<?php
require '../config/db.php';

// Read PayPal IPN message
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = [];
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}

// Prepare data for verification with PayPal
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $req .= "&$key=" . urlencode($value);
}

// Send data back to PayPal for verification
$paypal_url = "https://ipnpb.paypal.com/cgi-bin/webscr";
$ch = curl_init($paypal_url);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$res = curl_exec($ch);
curl_close($ch);

// Process verified IPN message
if (strcmp($res, "VERIFIED") == 0) {
    $transaction_id = $_POST['txn_id'] ?? '';
    $payment_status = $_POST['payment_status'] ?? '';
    $amount_paid = $_POST['mc_gross'] ?? 0;
    $currency = $_POST['mc_currency'] ?? '';
    $custom_data = $_POST['custom'] ?? '';
    list($tenant_id, $property_id) = explode('|', $custom_data);

    if ($payment_status === 'Completed') {
        // Check if transaction already exists
        $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $stmt->execute([$transaction_id]);
        if ($stmt->rowCount() > 0) {
            exit(); // Duplicate transaction, exit
        }

        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO payments (tenant_id, property_id, amount, payment_status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tenant_id, $property_id, $amount_paid, 'paid']);

        // Log transaction
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_type) VALUES (?, ?, ?)");
        $stmt->execute([$tenant_id, $amount_paid, 'rent_payment']);
    }
}
http_response_code(200);
