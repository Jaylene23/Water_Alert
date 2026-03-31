

-- 1. CREATE & SELECT DATABASE
DROP DATABASE IF EXISTS water_alert_db;
CREATE DATABASE water_alert_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE water_alert_db;

-- ============================================================
-- 2. USERS TABLE
-- ============================================================
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','manager','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 3. TANK STATUS TABLE
-- ============================================================
CREATE TABLE tank_status (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    water_level      INT DEFAULT 75,
    status           VARCHAR(20) DEFAULT 'SAFE',
    valve_state      VARCHAR(20) DEFAULT 'OPEN',
    battery          INT DEFAULT 100,
    signal_strength  VARCHAR(20) DEFAULT 'GOOD',
    last_updated     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 4. LEAK LOGS TABLE
-- ============================================================
CREATE TABLE leak_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    status     VARCHAR(20) NOT NULL,
    source     VARCHAR(50) DEFAULT 'SYSTEM',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 5. ALERTS TABLE
-- ============================================================
CREATE TABLE alerts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    alert_type VARCHAR(50) DEFAULT 'LEAK',
    message    TEXT,
    sent_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 6. SEED — DEFAULT TANK ROW
-- ============================================================
INSERT INTO tank_status (id, water_level, status, valve_state, battery, signal_strength)
VALUES (1, 75, 'SAFE', 'OPEN', 100, 'GOOD');

-- ============================================================
-- 7. SEED — DEFAULT USERS (plain text passwords)
-- ============================================================
INSERT INTO users (username, password, role) VALUES
    ('admin',   'admin123',   'admin'),
    ('manager', 'manager123', 'manager'),
    ('user1',   'user123',    'user');

-- ============================================================
-- 8. SEED — SAMPLE LEAK LOGS (so dashboard is not empty)
-- ============================================================
INSERT INTO leak_logs (status, source, created_at) VALUES
    ('SAFE', 'ESP32',          NOW() - INTERVAL 10 MINUTE),
    ('SAFE', 'ESP32',          NOW() - INTERVAL 20 MINUTE),
    ('LEAK', 'ESP32',          NOW() - INTERVAL 30 MINUTE),
    ('LEAK', 'WEB_SIMULATION', NOW() - INTERVAL 40 MINUTE),
    ('SAFE', 'ESP32',          NOW() - INTERVAL 50 MINUTE),
    ('SAFE', 'ESP32',          NOW() - INTERVAL 60 MINUTE),
    ('LEAK', 'ESP32',          NOW() - INTERVAL 90 MINUTE),
    ('SAFE', 'WEB_SIMULATION', NOW() - INTERVAL 2  HOUR),
    ('SAFE', 'ESP32',          NOW() - INTERVAL 3  HOUR),
    ('LEAK', 'ESP32',          NOW() - INTERVAL 5  HOUR);

-- ============================================================
-- 9. SEED — SAMPLE ALERTS
-- ============================================================
INSERT INTO alerts (alert_type, message, sent_at) VALUES
    ('LEAK', 'Leak Alert! Water level at 10%', NOW() - INTERVAL 30 MINUTE),
    ('LEAK', 'Leak Alert! Water level at 10%', NOW() - INTERVAL 90 MINUTE),
    ('LEAK', 'Leak Alert! Water level at 10%', NOW() - INTERVAL 5  HOUR);

-- ============================================================
-- DONE. Login credentials:
--   admin   / admin123   → Admin Panel    (admin.php)
--   manager / manager123 → Manager Panel  (manager.php)
--   user1   / user123    → User Dashboard (index.php)
-- ============================================================
