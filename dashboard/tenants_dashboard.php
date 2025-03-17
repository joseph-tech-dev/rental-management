<?php
session_start();

// Enable error reporting for development
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Include database connection (adjust the path as needed)
require_once '/opt/lampp/htdocs/project/config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get tenant's user_id
$user_id = $_SESSION['user_id'];

try {
    // Prepare query to fetch tenant information
    $query = "SELECT * FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the user exists
    if (!$user) {
        throw new Exception("User not found");
    }

    // Fetch properties available for the tenant to view
    $query = "SELECT * FROM properties WHERE status = 'available'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch tenant's leases
    $query = "SELECT * FROM leases WHERE tenant_id = :tenant_id AND status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':tenant_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $leases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch maintenance requests made by the tenant
    $query = "SELECT * FROM maintenance_requests WHERE tenant_id = :tenant_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':tenant_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $maintenance_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch tenant's rent payments
    $query = "SELECT * FROM payments WHERE tenant_id = :tenant_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':tenant_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch tenant's complaints
    $query = "SELECT * FROM complaints WHERE tenant_id = :tenant_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':tenant_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch notifications for the tenant
    $query = "SELECT * FROM notifications WHERE user_id = :user_id AND status = 'unread'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle database errors gracefully (log them, display a user-friendly message)
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    // Handle other exceptions
    die("An error occurred: " . $e->getMessage());
}

// Include header file (adjust the path as needed)
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./tenants_dashboard.css">
</head>
<body>

<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="fw-bold text-primary">Tenant Dashboard</h1>
        </div>
        </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-user me-2"></i>Welcome, <?= htmlspecialchars($user['full_name']); ?></h5>
            <p class="card-text">
                <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user['email']); ?>
            </p>
            </div>
    </div>

    <section class="mb-5">
        <h2 class="fw-semibold text-secondary"><i class="fas fa-home me-2"></i>Available Properties</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (count($properties) > 0): ?>
                <?php foreach ($properties as $property): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($property['name']); ?></h5>
                                <p class="card-text"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($property['address']); ?></p>
                                <p class="card-text"><i class="fas fa-money-bill-wave me-2"></i>Rent: $<?= number_format($property['rent_amount'], 2); ?></p>
                                <div class="d-grid">
                                    <a href="./property_details.php?property_id=<?= $property['property_id']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-info-circle me-2"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No available properties at the moment.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="fw-semibold text-secondary"><i class="fas fa-info-circle me-2"></i>Your Information</h2>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <div class="col">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><i class="fas fa-tools me-2"></i>Maintenance Requests</h5>
                        <?php if (count($maintenance_requests) > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($maintenance_requests as $request): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars(substr($request['description'], 0, 50)) . "..."; ?>
                                        <span class="badge bg-primary rounded-pill"><?= ucfirst(htmlspecialchars($request['status'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-warning">No maintenance requests found.</div>
                        <?php endif; ?>
                        <div class="d-grid mt-3">
                            <a href="../maintenance/maintenance.php" class="btn btn-outline-secondary">
                                <i class="fas fa-eye me-2"></i>View All Requests
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><i class="fas fa-file-contract me-2"></i>Lease Details</h5>
                        <?php if (count($leases) > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($leases as $lease): ?>
                                    <li class="list-group-item">
                                        Property ID: <?= htmlspecialchars($lease['property_id']); ?><br>
                                        Start: <?= htmlspecialchars($lease['start_date']); ?>, End: <?= htmlspecialchars($lease['end_date']); ?><br>
                                        Status: <span class="badge bg-success rounded-pill"><?= ucfirst(htmlspecialchars($lease['status'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-warning">No active leases found.</div>
                        <?php endif; ?>
                        <div class="d-grid mt-3">
                            <a href="../lease/lease_details.php" class="btn btn-outline-secondary">
                                 <i class="fas fa-eye me-2"></i>View Lease Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold"><i class="fas fa-money-bill-alt me-2"></i>Rent Payments</h5>
                    <?php if (count($payments) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($payments as $payment): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    $<?= number_format($payment['amount'], 2); ?>
                                    <span class="badge bg-
                                        <?= htmlspecialchars($payment['payment_status']) == 'paid' ? 'success' : 'warning'; ?> 
                                        rounded-pill">
                                        <?= ucfirst(htmlspecialchars($payment['payment_status'])); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-warning">No rent payments recorded.</div>
                    <?php endif; ?>
                    <div class="d-grid mt-3">
                        <a href="../payment/payment.php" class="btn btn-outline-secondary">
                             <i class="fas fa-eye me-2"></i>View Payment History
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Complaints</h5>
                    <?php if (count($complaints) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($complaints as $complaint): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars(substr($complaint['complaint_text'], 0, 50)) . "..."; ?>
                                    <span class="badge bg-info rounded-pill"><?= ucfirst(htmlspecialchars($complaint['status'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-warning">No complaints filed.</div>
                    <?php endif; ?>
                    <div class="d-grid mt-3">
                        <a href="complaints.php" class="btn btn-outline-secondary">
                            <i class="fas fa-eye me-2"></i>View All Complaints
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold"><i class="fas fa-bell me-2"></i>Notifications</h5>
                    <?php if (count($notifications) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($notification['message']); ?>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= htmlspecialchars($notification['created_at']); ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-warning">No unread notifications.</div>
                    <?php endif; ?>
                     <div class="d-grid mt-3">
                        <a href="notifications.php" class="btn btn-outline-secondary">
                            <i class="fas fa-eye me-2"></i>View All Notifications
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="./tenants_dashboard.js"></script>

</body>
<?php include '/opt/lampp/htdocs/project/includes/footer.php'; ?>

</html>