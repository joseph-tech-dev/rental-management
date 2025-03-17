<?php
include '/opt/lampp/htdocs/project/config/db.php'; // Database connection

// Fetch notifications
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM notifications";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notifications as $row) {
        echo "<tr>
                <td>{$row['notification_id']}</td>
                <td>{$row['user_id']}</td>
                <td>{$row['message']}</td>
                <td>{$row['status']}</td>
                <td>{$row['created_at']}</td>
                <td>
                    <button class='editBtn' data-id='{$row['notification_id']}' data-user='{$row['user_id']}' data-message='{$row['message']}' data-status='{$row['status']}'>Edit</button>
                    <button class='deleteBtn' data-id='{$row['notification_id']}'>Delete</button>
                </td>
              </tr>";
    }
}

// Add or Edit notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification_id = $_POST['notification_id'] ?? null;
    $user_id = $_POST['user_id'];
    $message = $_POST['message'];
    $status = $_POST['status'];

    if ($notification_id) {
        // Update existing notification
        $query = "UPDATE notifications SET user_id=:user_id, message=:message, status=:status WHERE notification_id=:notification_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':notification_id', $notification_id);
    } else {
        // Insert new notification
        $query = "INSERT INTO notifications (user_id, message, status) VALUES (:user_id, :message, :status)";
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        echo "Complaint saved successfully!";
    } else {
        echo "Error saving complaint!";
    }
}

// Delete notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $notification_id = $_POST['delete_id'];

    $query = "DELETE FROM notifications WHERE notification_id = :notification_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':notification_id', $notification_id);

    if ($stmt->execute()) {
        echo "Complaint deleted successfully!";
    } else {
        echo "Error deleting complaint!";
    }
}
?>
