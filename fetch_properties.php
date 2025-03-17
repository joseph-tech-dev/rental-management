<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config/db.php'; // Ensure this file exists and has correct credentials

try {
    // Prepare and execute query
    $sql = "SELECT p.property_id, p.name, p.address, p.type, p.status, p.rent_amount, u.full_name AS landlord_name 
            FROM properties p
            JOIN users u ON p.landlord_id = u.user_id
            WHERE p.status = 'available'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $properties = [];

    // Fetch properties as associative array
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $property_id = $row['property_id'];

        // Fetch tenants for this property
        $tenants_sql = "SELECT u.full_name, t.lease_start_date, t.lease_end_date 
                        FROM tenants t
                        JOIN users u ON t.user_id = u.user_id
                        WHERE t.property_id = :property_id";
        $tenants_stmt = $conn->prepare($tenants_sql);
        $tenants_stmt->bindParam(":property_id", $property_id, PDO::PARAM_INT);
        $tenants_stmt->execute();
        $tenants = $tenants_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format tenants data
        $formatted_tenants = array_map(function ($tenant) {
            return [
                'name' => $tenant['full_name'],
                'lease_start' => $tenant['lease_start_date'],
                'lease_end' => $tenant['lease_end_date']
            ];
        }, $tenants);

        // Store property with tenants
        $properties[] = [
            'id' => $property_id,
            'name' => $row['name'],
            'address' => $row['address'],
            'type' => $row['type'],
            'status' => $row['status'],
            'rent' => $row['rent_amount'],
            'landlord' => $row['landlord_name'],
            'tenants' => $formatted_tenants
        ];
    }

    // Send JSON response
    echo json_encode(["success" => true, "data" => $properties], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
