-- ============================================
-- Food Fest Ordering System - Database Schema
-- ============================================
-- Created: 2025-12-15
-- Description: MySQL database schema for local food ordering system
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS foodfest;
USE foodfest;

-- ============================================
-- Table: admins
-- Description: Store admin user credentials
-- ============================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: items
-- Description: Store food items available for ordering
-- ============================================
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    availability TINYINT(1) DEFAULT 1 COMMENT '1=available, 0=out of stock',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_availability (availability)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: orders
-- Description: Store order header information
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_id VARCHAR(20) NOT NULL UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Preparing', 'Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_token (token_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: order_items
-- Description: Store individual items in each order
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL COMMENT 'Price at time of order',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sample Data
-- ============================================

-- Insert default admin account
-- Username: admin
-- Password: admin123 (hashed using PASSWORD_DEFAULT in PHP)
INSERT INTO admins (username, password_hash) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Note: Change this password after first login for security!

-- Insert sample food items
INSERT INTO items (name, price, availability) VALUES 
('Chicken Burger', 150.00, 1),
('Veg Burger', 120.00, 1),
('French Fries', 80.00, 1),
('Chicken Momo', 100.00, 1),
('Veg Momo', 80.00, 1),
('Chicken Pizza', 250.00, 1),
('Veg Pizza', 200.00, 1),
('Coke', 50.00, 1),
('Sprite', 50.00, 1),
('Water Bottle', 20.00, 1),
('Chicken Chowmein', 120.00, 1),
('Veg Chowmein', 100.00, 1),
('Samosa', 30.00, 1),
('Spring Roll', 40.00, 1),
('Ice Cream', 60.00, 1);

-- ============================================
-- Sample order for testing (optional)
-- ============================================
-- Uncomment below to add a test order

-- INSERT INTO orders (token_id, customer_name, total_price, status) VALUES 
-- ('FF2025-001', 'Test Customer', 270.00, 'Pending');

-- INSERT INTO order_items (order_id, item_id, quantity, price) VALUES 
-- (1, 1, 1, 150.00),  -- 1x Chicken Burger
-- (1, 3, 1, 80.00),   -- 1x French Fries
-- (1, 8, 1, 50.00);   -- 1x Coke

-- ============================================
-- Verification Queries
-- ============================================
-- Run these queries to verify the setup:

-- SELECT * FROM admins;
-- SELECT * FROM items;
-- SELECT * FROM orders;
-- SELECT * FROM order_items;

-- ============================================
-- End of Schema
-- ============================================
