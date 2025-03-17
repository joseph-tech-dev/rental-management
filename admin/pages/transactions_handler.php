<?php
include '/opt/lampp/htdocs/project/config/db.php'; // Database connection

// Fetch transactions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM transactions";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($transactions as $row) {
        echo "<tr>
                <td>{$row['transaction_id']}</td>
                <td>{$row['tenant_id']}</td>
                <td>{$row['property_id']}</td>
                <td>{$row['amount']}</td>
                <td>{$row['transaction_date']}</td>
                <td>{$row['status']}</td>
                <td>
                    <button class='editBtn' data-id='{$row['transaction_id']}' data-tenant='{$row['tenant_id']}' data-property='{$row['property_id']}' data-amount='{$row['amount']}' data-status='{$row['status']}'>Edit</button>
                    <button class='deleteBtn' data-id='{$row['transaction_id']}'>Delete</button>
                </td>
              </tr>";
    }
}

// Add or Edit Transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'] ?? null;
    $tenant_id = $_POST['tenant_id'];
    $property_id = $_POST['property_id'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];

    if ($transaction_id) {
        $query = "UPDATE transactions SET tenant_id=:tenant_id, property_id=:property_id, amount=:amount, status=:status WHERE transaction_id=:transaction_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':transaction_id', $transaction_id);
    } else {
        $query = "INSERT INTO transactions (tenant_id, property_id, amount, status) VALUES (:tenant_id, :property_id, :amount, :status)";
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':tenant_id', $tenant_id);
    $stmt->bindParam(':property_id', $property_id);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        echo "Transaction saved successfully!";
    } else {
        echo "Error saving transaction!";
    }
}

// Delete Transaction
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $transaction_id = $_DELETE['transaction_id'];

    $query = "DELETE FROM transactions WHERE transaction_id = :transaction_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':transaction_id', $transaction_id);

    if ($stmt->execute()) {
        echo "Transaction deleted successfully!";
    } else {
        echo "Error deleting transaction!";
    }
}
?>
