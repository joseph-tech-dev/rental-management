<?php
include '/opt/lampp/htdocs/project/config/db.php'; // Database connection

// Fetch maintenance requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM maintenance_requests";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($requests as $row) {
        echo "<tr>
                <td>{$row['request_id']}</td>
                <td>{$row['tenant_id']}</td>
                <td>{$row['property_id']}</td>
                <td>{$row['description']}</td>
                <td>{$row['status']}</td>
                <td>{$row['created_at']}</td>
                <td>{$row['updated_at']}</td>
                <td>
                    <button class='editBtn' data-id='{$row['request_id']}' data-tenant='{$row['tenant_id']}' data-property='{$row['property_id']}' data-description='{$row['description']}' data-status='{$row['status']}'>Edit</button>
                    <button class='deleteBtn' data-id='{$row['request_id']}'>Delete</button>
                </td>
              </tr>";
    }
}

// Add or Edit Maintenance Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $tenant_id = $_POST['tenant_id'];
    $property_id = $_POST['property_id'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    if ($request_id) {
        $query = "UPDATE maintenance_requests SET tenant_id=:tenant_id, property_id=:property_id, description=:description, status=:status WHERE request_id=:request_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':request_id', $request_id);
    } else {
        $query = "INSERT INTO maintenance_requests (tenant_id, property_id, description, status) VALUES (:tenant_id, :property_id, :description, :status)";
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':tenant_id', $tenant_id);
    $stmt->bindParam(':property_id', $property_id);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        echo "Maintenance request saved successfully!";
    } else {
        echo "Error saving maintenance request!";
    }
}

// Delete Maintenance Request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $request_id = $_DELETE['request_id'];

    $query = "DELETE FROM maintenance_requests WHERE request_id = :request_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':request_id', $request_id);

    if ($stmt->execute()) {
        echo "Maintenance request deleted successfully!";
    } else {
        echo "Error deleting maintenance request!";
    }
}
?>
