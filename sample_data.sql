
-- Insert Into USERS
INSERT INTO users (user_id, full_name, email, phone, password_hash, role) VALUES
(1, 'Alice Johnson', 'alice@example.com', '1234567890', 'hashed_password_1', 'admin'),
(2, 'Bob Smith', 'bob@example.com', '0987654321', 'hashed_password_2', 'landlord'),
(3, 'Charlie Brown', 'charlie@example.com', '1122334455', 'hashed_password_3', 'tenant'),
(4, 'Diana White', 'diana@example.com', '2233445566', 'hashed_password_4', 'maintenance_staff'),
(5, 'Ethan Williams', 'ethan@example.com', '3344556677', 'hashed_password_5', 'landlord'),
(6, 'Fiona Green', 'fiona@example.com', '4455667788', 'hashed_password_6', 'tenant'),
(7, 'George Martin', 'george@example.com', '5566778899', 'hashed_password_7', 'admin'),
(8, 'Hannah Scott', 'hannah@example.com', '6677889900', 'hashed_password_8', 'maintenance_staff'),
(9, 'Ian Clark', 'ian@example.com', '7788990011', 'hashed_password_9', 'tenant'),
(10, 'Jessica Adams', 'jessica@example.com', '8899001122', 'hashed_password_10', 'landlord'),
(11, 'Kevin Baker', 'kevin@example.com', '9900112233', 'hashed_password_11', 'tenant'),
(12, 'Laura Carter', 'laura@example.com', '1112223344', 'hashed_password_12', 'admin'),
(13, 'Michael Roberts', 'michael@example.com', '2223334455', 'hashed_password_13', 'landlord'),
(14, 'Natalie Hall', 'natalie@example.com', '3334445566', 'hashed_password_14', 'tenant'),
(15, 'Oliver Evans', 'oliver@example.com', '4445556677', 'hashed_password_15', 'maintenance_staff'),
(16, 'Paula Lewis', 'paula@example.com', '5556667788', 'hashed_password_16', 'landlord'),
(17, 'Quincy Turner', 'quincy@example.com', '6667778899', 'hashed_password_17', 'tenant'),
(18, 'Rachel Allen', 'rachel@example.com', '7778889900', 'hashed_password_18', 'admin'),
(19, 'Samuel Young', 'samuel@example.com', '8889990011', 'hashed_password_19', 'maintenance_staff'),
(20, 'Tina Hernandez', 'tina@example.com', '9990001122', 'hashed_password_20', 'tenant');



--Insert Into Properties
INSERT INTO properties (property_id, landlord_id, name, address, type, status, rent_amount) VALUES
(1, 1, 'Sunrise Apartments', '123 Main St, City Center', 'apartment', 'available', 4500.00),
(2, 2, 'Maplewood Residence', '456 Oak Dr, Suburb', 'house', 'occupied', 8500.00),
(3, 3, 'Skyline Plaza', '789 High St, Downtown', 'office', 'available', 15000.00),
(4, 4, 'Greenwood Mall', '101 Commerce Ave, Market District', 'shop', 'under_maintenance', 12000.00),
(5, 1, 'Riverside Apartments', '202 River Rd, Waterfront', 'apartment', 'available', 5000.00),
(6, 2, 'Hilltop Cottage', '303 Hillcrest Blvd, Suburb', 'house', 'occupied', 7800.00),
(7, 3, 'Tech Park Offices', '404 Innovation St, Tech Hub', 'office', 'available', 18000.00),
(8, 4, 'City Square Mall', '505 Plaza St, Downtown', 'shop', 'available', 20000.00),
(9, 1, 'Sunset Villas', '606 Beachside Dr, Coastal Area', 'house', 'available', 9500.00),
(10, 2, 'Grand Tower', '707 Business Ln, CBD', 'office', 'occupied', 22000.00),
(11, 3, 'Garden Apartments', '808 Park Ave, Green District', 'apartment', 'available', 4200.00),
(12, 4, 'Metro Retail Hub', '909 Central Blvd, Market Square', 'shop', 'occupied', 17500.00),
(13, 1, 'Silvercrest Homes', '1011 Sunset Blvd, Suburb', 'house', 'available', 8200.00),
(14, 2, 'Westwood Plaza', '1112 Skyline Dr, Downtown', 'office', 'available', 16000.00),
(15, 3, 'Lakeside Residences', '1213 Lakeview Rd, Riverside', 'apartment', 'under_maintenance', 6800.00),
(16, 4, 'Uptown Market', '1314 Uptown Ave, Shopping District', 'shop', 'available', 14000.00),
(17, 1, 'Sunny Apartments', '1415 Sunshine St, Residential Area', 'apartment', 'available', 4700.00),
(18, 2, 'Elmwood Homes', '1516 Elm St, Suburb', 'house', 'occupied', 9000.00),
(19, 3, 'Downtown Business Center', '1617 Financial St, CBD', 'office', 'available', 19000.00),
(20, 4, 'City Supermall', '1718 Commerce Rd, Market Hub', 'shop', 'occupied', 25000.00);

-- Corrected Sample Data for Tenants Table

INSERT INTO tenants (user_id, lease_start_date, lease_end_date, property_id) VALUES
(3, '2023-01-01', '2024-01-01', 1),
(6, '2023-02-15', '2024-02-15', 5),
(9, '2023-03-10', '2024-03-10', 11),
(11, '2023-04-05', '2024-04-05', 17),
(14, '2023-05-20', '2024-05-20', 2),
(17, '2023-06-01', '2024-06-01', 6),
(20, '2023-07-15', '2024-07-15', 18),
(1, '2023-08-10', '2024-08-10', 1), -- Corrected: user_id 1
(2, '2023-09-05', '2024-09-05', 5), -- Corrected: user_id 2
(4, '2023-10-20', '2024-10-20', 11), -- Corrected: user_id 4
(5, '2023-11-01', '2024-11-01', 17), -- Corrected: user_id 5
(7, '2023-12-15', '2024-12-15', 2), -- Corrected: user_id 7
(8, '2024-01-10', '2025-01-10', 6), -- Corrected: user_id 8
(10, '2024-02-05', '2025-02-05', 18), -- Corrected: user_id 10
(15, '2023-09-01', '2024-09-01', 15),
(12, '2023-06-01', '2024-06-01', 12),
(13, '2023-10-01', '2024-10-01', 10),
(18, '2023-12-01', '2024-12-01', 20),
(19, '2023-08-15', '2024-08-15', 15),
(16, '2024-01-05', '2025-01-05', 4); -- corrected: user_id 16