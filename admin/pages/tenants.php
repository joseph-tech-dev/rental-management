//tenants.php
<?php
include '/opt/lampp/htdocs/project/config/db.php'; // Database connection

// Fetch tenants
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM tenants";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tenants as $row) {
        echo "<tr>
                <td>{$row['tenant_id']}</td>
                <td>{$row['user_id']}</td>
                <td>{$row['lease_start_date']}</td>
                <td>{$row['lease_end_date']}</td>
                <td>{$row['property_id']}</td>
                <td>
                    <button class='editBtn' data-id='{$row['tenant_id']}' data-user='{$row['user_id']}' data-start='{$row['lease_start_date']}' data-end='{$row['lease_end_date']}' data-property='{$row['property_id']}'>Edit</button>
                    <button class='deleteBtn' data-id='{$row['tenant_id']}'>Delete</button>
                </td>
              </tr>";
    }
}

// Add or Edit Tenant
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = $_POST['tenant_id'] ?? null;
    $user_id = $_POST['user_id'];
    $lease_start = $_POST['lease_start'];
    $lease_end = $_POST['lease_end'];
    $property_id = $_POST['property_id'];

    if ($tenant_id) {
        // Update existing tenant
        $query = "UPDATE tenants SET user_id=:user_id, lease_start_date=:lease_start, lease_end_date=:lease_end, property_id=:property_id WHERE tenant_id=:tenant_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':tenant_id', $tenant_id);
    } else {
        // Insert new tenant
        $query = "INSERT INTO tenants (user_id, lease_start_date, lease_end_date, property_id) VALUES (:user_id, :lease_start, :lease_end, :property_id)";
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':lease_start', $lease_start);
    $stmt->bindParam(':lease_end', $lease_end);
    $stmt->bindParam(':property_id', $property_id);

    if ($stmt->execute()) {
        echo "Tenant saved successfully!";
    } else {
        echo "Error saving tenant!";
    }
}

// Delete Tenant
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $tenant_id = $_DELETE['tenant_id'];

    $query = "DELETE FROM tenants WHERE tenant_id = :tenant_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':tenant_id', $tenant_id);

    if ($stmt->execute()) {
        echo "Tenant deleted successfully!";
    } else {
        echo "Error deleting tenant!";
    }
}
?>
