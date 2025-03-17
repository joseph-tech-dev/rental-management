<?php
include '/opt/lampp/htdocs/project/config/db.php'; // Database connection

// Fetch property images
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM property_images";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($images as $row) {
        echo "<tr>
                <td>{$row['image_id']}</td>
                <td>{$row['property_id']}</td>
                <td><img src='{$row['image_url']}' alt='Property Image' width='100'></td>
                <td>
                    <button class='editBtn' data-id='{$row['image_id']}' data-property='{$row['property_id']}' data-url='{$row['image_url']}'>Edit</button>
                    <button class='deleteBtn' data-id='{$row['image_id']}'>Delete</button>
                </td>
              </tr>";
    }
}

// Add or Edit Property Image
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_id = $_POST['image_id'] ?? null;
    $property_id = $_POST['property_id'];
    $image_url = $_POST['image_url'];

    if ($image_id) {
        // Update existing image
        $query = "UPDATE property_images SET property_id=:property_id, image_url=:image_url WHERE image_id=:image_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':image_id', $image_id);
    } else {
        // Insert new image
        $query = "INSERT INTO property_images (property_id, image_url) VALUES (:property_id, :image_url)";
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':property_id', $property_id);
    $stmt->bindParam(':image_url', $image_url);

    if ($stmt->execute()) {
        echo "Property image saved successfully!";
    } else {
        echo "Error saving property image!";
    }
}

// Delete Property Image
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $image_id = $_DELETE['image_id'];

    $query = "DELETE FROM property_images WHERE image_id = :image_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':image_id', $image_id);

    if ($stmt->execute()) {
        echo "Property image deleted successfully!";
    } else {
        echo "Error deleting property image!";
    }
}
?>
