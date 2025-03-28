-- Users table (landlords, tenants, admin, maintenance staff)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'landlord', 'tenant', 'maintenance_staff') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Properties table (Apartments, rooms, offices, etc.)
CREATE TABLE properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    landlord_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    type ENUM('apartment', 'house', 'office', 'shop') NOT NULL,
    status ENUM('available', 'occupied', 'under_maintenance') DEFAULT 'available',
    rent_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (landlord_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Tenants table
CREATE TABLE tenants (
    tenant_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    lease_start_date DATE NOT NULL,
    lease_end_date DATE NOT NULL,
    property_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Leases table (tracks rental agreements)
CREATE TABLE leases (
    lease_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    property_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'terminated', 'pending') DEFAULT 'active',
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Rent Payments table
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    property_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('paid', 'pending', 'overdue') DEFAULT 'pending',
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Maintenance Requests table
CREATE TABLE maintenance_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    property_id INT NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Transactions table (tracks all financial transactions)
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_type ENUM('rent_payment', 'deposit', 'refund', 'other') NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Complaints table (tracks tenant complaints)
CREATE TABLE complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    property_id INT NOT NULL,
    complaint_text TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Notifications table (for rent reminders, maintenance updates, etc.)
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Property Images table (stores images for properties)
CREATE TABLE property_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

ALTER TABLE tenants DROP FOREIGN KEY tenants_ibfk_2;
ALTER TABLE tenants ADD CONSTRAINT tenants_ibfk_2 FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE;

ALTER TABLE leases DROP FOREIGN KEY leases_ibfk_2;
ALTER TABLE leases ADD CONSTRAINT leases_ibfk_2 FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE;

ALTER TABLE payments DROP FOREIGN KEY payments_ibfk_2;
ALTER TABLE payments ADD CONSTRAINT payments_ibfk_2 FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE;

ALTER TABLE maintenance_requests DROP FOREIGN KEY maintenance_requests_ibfk_2;
ALTER TABLE maintenance_requests ADD CONSTRAINT maintenance_requests_ibfk_2 FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE;

ALTER TABLE complaints DROP FOREIGN KEY complaints_ibfk_2;
ALTER TABLE complaints ADD CONSTRAINT complaints_ibfk_2 FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE;

ALTER TABLE property_images DROP FOREIGN KEY property_images_ibfk_1;
ALTER TABLE property_images ADD CONSTRAINT property_images_ibfk_1 FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE;
