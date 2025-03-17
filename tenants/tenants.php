<!-- filepath: c:\xampp\htdocs\project\tenants.php -->
<?php
include 'db.php';
class Tenant {
    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $phone;
    public $propertyId;
    public $leaseStart;
    public $leaseEnd;
    public $paymentHistory;
    public $documents;
    public $notes;

    public function __construct($id, $firstName, $lastName, $email, $phone, $propertyId, $leaseStart, $leaseEnd) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->propertyId = $propertyId;
        $this->leaseStart = $leaseStart;
        $this->leaseEnd = $leaseEnd;
        $this->paymentHistory = [];
        $this->documents = [];
        $this->notes = [];
    }

    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function addPayment($payment) {
        $this->paymentHistory[] = $payment;
    }

    public function addDocument($document) {
        $this->documents[] = $document;
    }

    public function addNote($note) {
        $this->notes[] = [
            'text' => $note,
            'date' => date('Y-m-d H:i:s'),
        ];
    }

    public function isLeaseActive() {
        $today = new DateTime();
        $startDate = new DateTime($this->leaseStart);
        $endDate = new DateTime($this->leaseEnd);
        return $today >= $startDate && $today <= $endDate;
    }

    public function daysUntilLeaseExpiration() {
        $today = new DateTime();
        $endDate = new DateTime($this->leaseEnd);
        $diff = $endDate->diff($today);
        return $diff->days;
    }
}

class TenantManager {
    private $tenants = [];

    public function __construct() {
        $this->loadTenants();
    }

    private function loadTenants() {
        // Load tenants from database
        $conn = $this->getConnection();
        $result = $conn->query("SELECT * FROM tenants");

        while ($row = $result->fetch_assoc()) {
            $tenant = new Tenant(
                $row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['email'],
                $row['phone'],
                $row['property_id'],
                $row['lease_start'],
                $row['lease_end']
            );
            $this->tenants[] = $tenant;
        }

        $conn->close();
    }

    private function saveTenant($tenant) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("INSERT INTO tenants (first_name, last_name, email, phone, property_id, lease_start, lease_end) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiss", $tenant->firstName, $tenant->lastName, $tenant->email, $tenant->phone, $tenant->propertyId, $tenant->leaseStart, $tenant->leaseEnd);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    private function getConnection() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "house_rental_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

    public function addTenant($tenantData) {
        $tenant = new Tenant(
            null,
            $tenantData['firstName'],
            $tenantData['lastName'],
            $tenantData['email'],
            $tenantData['phone'],
            $tenantData['propertyId'],
            $tenantData['leaseStart'],
            $tenantData['leaseEnd']
        );

        if (isset($tenantData['notes'])) {
            foreach ($tenantData['notes'] as $note) {
                $tenant->addNote($note);
            }
        }

        if (isset($tenantData['documents'])) {
            foreach ($tenantData['documents'] as $document) {
                $tenant->addDocument($document);
            }
        }

        $this->tenants[] = $tenant;
        $this->saveTenant($tenant);
    }

    public function getTenant($id) {
        foreach ($this->tenants as $tenant) {
            if ($tenant->id == $id) {
                return $tenant;
            }
        }
        return null;
    }

    public function getTenantsByProperty($propertyId) {
        $result = [];
        foreach ($this->tenants as $tenant) {
            if ($tenant->propertyId == $propertyId) {
                $result[] = $tenant;
            }
        }
        return $result;
    }

    public function updateTenant($id, $updatedData) {
        foreach ($this->tenants as $tenant) {
            if ($tenant->id == $id) {
                foreach ($updatedData as $key => $value) {
                    if (property_exists($tenant, $key)) {
                        $tenant->$key = $value;
                    }
                }
                $this->saveTenant($tenant);
                return true;
            }
        }
        return false;
    }

    public function deleteTenant($id) {
        foreach ($this->tenants as $index => $tenant) {
            if ($tenant->id == $id) {
                unset($this->tenants[$index]);
                $this->deleteTenantFromDatabase($id);
                return true;
            }
        }
        return false;
    }

    private function deleteTenantFromDatabase($id) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("DELETE FROM tenants WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

// Example usage
$tenantManager = new TenantManager();
$tenantManager->addTenant([
    'firstName' => 'John',
    'lastName' => 'Doe',
    'email' => 'john.doe@example.com',
    'phone' => '123-456-7890',
    'propertyId' => 1,
    'leaseStart' => '2025-01-01',
    'leaseEnd' => '2025-12-31',
    'notes' => ['First note', 'Second note'],
    'documents' => ['Document 1', 'Document 2']
]);

$tenant = $tenantManager->getTenant(1);
if ($tenant) {
    echo 'Tenant found: ' . $tenant->getFullName();
} else {
    echo 'Tenant not found';
}
?>