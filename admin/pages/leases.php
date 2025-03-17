<?php
include '/opt/lampp/htdocs/project/config/db.php'; // Database connection

// Fetch leases
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM leases";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $leases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($leases as $row) {
        echo "<tr>
                <td>{$row['lease_id']}</td>
                <td>{$row['tenant_id']}</td>
                <td>{$row['property_id']}</td>
                <td>{$row['start_date']}</td>
                <td>{$row['end_date']}</td>
                <td>{$row['status']}</td>
                <td>
                    <button class='editBtn' data-id='{$row['lease_id']}' data-tenant='{$row['tenant_id']}' data-property='{$row['property_id']}' data-start='{$row['start_date']}' data-end='{$row['end_date']}' data-status='{$row['status']}'>Edit</button>
                    <button class='deleteBtn' data-id='{$row['lease_id']}'>Delete</button>
                </td>
              </tr>";
    }
}

// Add or Edit Lease
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lease_id = $_POST['lease_id'] ?? null;
    $tenant_id = $_POST['tenant_id'];
    $property_id = $_POST['property_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    if ($lease_id) {
        $query = "UPDATE leases SET tenant_id=:tenant_id, property_id=:property_id, start_date=:start_date, end_date=:end_date, status=:status WHERE lease_id=:lease_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':lease_id', $lease_id);
    } else {
        $query = "INSERT INTO leases (tenant_id, property_id, start_date, end_date, status) VALUES (:tenant_id, :property_id, :start_date, :end_date, :status)";
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':tenant_id', $tenant_id);
    $stmt->bindParam(':property_id', $property_id);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        echo "Lease saved successfully!";
    } else {
        echo "Error saving lease!";
    }
}

// Delete Lease
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $lease_id = $_DELETE['lease_id'];

    $query = "DELETE FROM leases WHERE lease_id = :lease_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':lease_id', $lease_id);

    if ($stmt->execute()) {
        echo "Lease deleted successfully!";
    } else {
        echo "Error deleting lease!";
    }
}
?>
