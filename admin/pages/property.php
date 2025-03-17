<?php
include '/opt/lampp/htdocs/project/config/db.php'; // Database connection

// Fetch properties
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM properties";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($properties as $row) {
        echo "<tr>
                <td>{$row['property_id']}</td>
                <td>{$row['landlord_id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['address']}</td>
                <td>{$row['type']}</td>
                <td>{$row['status']}</td>
                <td>{$row['rent_amount']}</td>
                <td>
                    <button class='editBtn' data-id='{$row['property_id']}' data-landlord='{$row['landlord_id']}' data-name='{$row['name']}' data-address='{$row['address']}' data-type='{$row['type']}' data-status='{$row['status']}' data-rent='{$row['rent_amount']}'>Edit</button>
                    <button class='deleteBtn' data-id='{$row['property_id']}'>Delete</button>
                </td>
              </tr>";
    }
}

// Add or Edit Property
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = $_POST['property_id'] ?? null;
    $landlord_id = $_POST['landlord_id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $type = $_POST['type'];
    $status = $_POST['status'];
    $rent_amount = $_POST['rent_amount'];

    if ($property_id) {
        // Update existing property
        $query = "UPDATE properties SET landlord_id=:landlord_id, name=:name, address=:address, type=:type, status=:status, rent_amount=:rent_amount WHERE property_id=:property_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
    } else {
        // Insert new property
        $query = "INSERT INTO properties (landlord_id, name, address, type, status, rent_amount) VALUES (:landlord_id, :name, :address, :type, :status, :rent_amount)";
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':landlord_id', $landlord_id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':rent_amount', $rent_amount);

    if ($stmt->execute()) {
        echo "Property saved successfully!";
    } else {
        echo "Error saving property!";
    }
}

// Delete Property
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $property_id = $_DELETE['property_id'];

    $query = "DELETE FROM properties WHERE property_id = :property_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':property_id', $property_id);

    if ($stmt->execute()) {
        echo "Property deleted successfully!";
    } else {
        echo "Error deleting property!";
    }
}
?>
