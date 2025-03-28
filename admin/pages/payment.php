<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '/opt/lampp/htdocs/project/config/db.php'; // Ensure you have a PDO database connection file

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch all payments
    $query = "SELECT * FROM payments";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($payments as $row) {
        echo "<tr>
                <td>{$row['payment_id']}</td>
                <td>{$row['tenant_id']}</td>
                <td>{$row['property_id']}</td>
                <td>{$row['amount']}</td>
                <td>{$row['payment_date']}</td>
                <td>{$row['payment_status']}</td>
                <td>
                    <button class='editBtn' data-id='{$row['payment_id']}' data-tenant='{$row['tenant_id']}' 
                        data-property='{$row['property_id']}' data-amount='{$row['amount']}' 
                        data-status='{$row['payment_status']}'>Edit</button>
                    <button class='deleteBtn' data-id='{$row['payment_id']}'>Delete</button>
                </td>
            </tr>";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'] ?? '';
    $tenant_id = $_POST['tenant_id'];
    $property_id = $_POST['property_id'];
    $amount = $_POST['amount'];
    $status = $_POST['payment_status'];

    if (empty($payment_id)) {
        // Insert new payment
        $query = "INSERT INTO payments (tenant_id, property_id, amount, payment_status, payment_date) 
                  VALUES (:tenant_id, :property_id, :amount, :payment_status, NOW())";
        $stmt = $conn->prepare($query);
    } else {
        // Update existing payment
        $query = "UPDATE payments SET tenant_id=:tenant_id, property_id=:property_id, amount=:amount, 
                  payment_status=:payment_status WHERE payment_id=:payment_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
    }

    $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
    $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);
    $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
    $stmt->bindParam(':payment_status', $status, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "Payment saved successfully!";
    } else {
        echo "Error: " . implode(" ", $stmt->errorInfo());
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $payment_id = $_DELETE['payment_id'];

    $query = "DELETE FROM payments WHERE payment_id=:payment_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "Payment deleted successfully!";
    } else {
        echo "Error: " . implode(" ", $stmt->errorInfo());
    }
}

$conn = null;
?>

