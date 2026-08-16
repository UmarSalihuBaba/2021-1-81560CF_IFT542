-- Task 2 migration: adds IP-based login throttling.
-- Run after schema.sql / seed.sql:
--   mysql -u root -p studentreg_db < db/schema_update_task2.sql

USE studentreg_db;

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
);
