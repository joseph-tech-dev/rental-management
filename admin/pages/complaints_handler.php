<?php
include '/opt/lampp/htdocs/project/config/db.php'; // Database connection

// Fetch complaints
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM complaints";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($complaints as $row) {
        echo "<tr>
                <td>{$row['complaint_id']}</td>
                <td>{$row['tenant_id']}</td>
                <td>{$row['property_id']}</td>
                <td>{$row['complaint_text']}</td>
                <td>{$row['status']}</td>
                <td>
                    <button class='editBtn' data-id='{$row['complaint_id']}' data-tenant='{$row['tenant_id']}' data-property='{$row['property_id']}' data-text='{$row['complaint_text']}' data-status='{$row['status']}'>Edit</button>
                    <button class='deleteBtn' data-id='{$row['complaint_id']}'>Delete</button>
                </td>
              </tr>";
    }
}

// Add or Edit Complaint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaint_id = $_POST['complaint_id'] ?? null;
    $tenant_id = $_POST['tenant_id'];
    $property_id = $_POST['property_id'];
    $complaint_text = $_POST['complaint_text'];
    $status = $_POST['status'];

    if ($complaint_id) {
        $query = "UPDATE complaints SET tenant_id=:tenant_id, property_id=:property_id, complaint_text=:complaint_text, status=:status WHERE complaint_id=:complaint_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':complaint_id', $complaint_id);
    } else {
        $query = "INSERT INTO complaints (tenant_id, property_id, complaint_text, status) VALUES (:tenant_id, :property_id, :complaint_text, :status)";
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':tenant_id', $tenant_id);
    $stmt->bindParam(':property_id', $property_id);
    $stmt->bindParam(':complaint_text', $complaint_text);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        echo "Complaint saved successfully!";
    } else {
        echo "Error saving complaint!";
    }
}

// Delete Complaint
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $complaint_id = $_DELETE['complaint_id'];

    $query = "DELETE FROM complaints WHERE complaint_id = :complaint_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':complaint_id', $complaint_id);

    if ($stmt->execute()) {
        echo "Complaint deleted successfully!";
    } else {
        echo "Error deleting complaint!";
    }
}
?>
