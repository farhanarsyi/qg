-- Database structure for Quality Gate Monitoring System

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(10) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    user_level ENUM('superadmin', 'pusat', 'provinsi', 'kabkot') NOT NULL,
    prov_code VARCHAR(2) DEFAULT '00',
    kab_code VARCHAR(2) DEFAULT '00',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert superadmin user
INSERT INTO users (username, password, name, user_level) VALUES
('admin', '$2y$10$qSQi9DM5.RPLH8e1c9RlHeUuBCAAKEzDk9KVP7YLMMf76C4QlBzjS', 'Super Administrator', 'superadmin');
-- Default password: admin123

-- Projects table - synced from API
CREATE TABLE projects (
    id VARCHAR(50) NOT NULL,
    year VARCHAR(4) NOT NULL,
    name VARCHAR(255) NOT NULL,
    last_synced TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id, year)
);

-- Coverages table - synced from API
CREATE TABLE coverages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(50) NOT NULL,
    year VARCHAR(4) NOT NULL,
    prov VARCHAR(2) NOT NULL DEFAULT '00',
    kab VARCHAR(2) NOT NULL DEFAULT '00',
    name VARCHAR(255) NOT NULL,
    last_synced TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY coverage_unique (project_id, year, prov, kab)
);

-- Sync log for tracking API synchronization
CREATE TABLE sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sync_type VARCHAR(50) NOT NULL,
    status BOOLEAN NOT NULL DEFAULT TRUE,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_users_level ON users(user_level);
CREATE INDEX idx_coverages_project ON coverages(project_id, year);
CREATE INDEX idx_coverages_area ON coverages(prov, kab); 