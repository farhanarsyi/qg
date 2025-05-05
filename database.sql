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

-- NEW TABLES FOR LOCAL MONITORING DATA

-- Table to store monitoring activities from API
CREATE TABLE monitoring_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(50) NOT NULL,
    year VARCHAR(4) NOT NULL,
    prov VARCHAR(2) NOT NULL DEFAULT '00',
    kab VARCHAR(2) NOT NULL DEFAULT '00',
    activity_id VARCHAR(50) NOT NULL,
    activity_name VARCHAR(255) NOT NULL,
    start_date DATE,
    end_date DATE,
    plan_date DATE,
    realization_date DATE,
    progress INT DEFAULT 0,
    status VARCHAR(50),
    notes TEXT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY monitoring_unique (project_id, year, prov, kab, activity_id)
);

-- Table to track synchronization details
CREATE TABLE sync_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sync_start TIMESTAMP,
    sync_end TIMESTAMP,
    sync_by INT,
    is_complete BOOLEAN DEFAULT FALSE,
    projects_synced INT DEFAULT 0,
    regions_synced INT DEFAULT 0,
    activities_synced INT DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (sync_by) REFERENCES users(id)
);

-- Table for detailed sync logs during synchronization process
CREATE TABLE sync_progress_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sync_id INT,
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    log_message TEXT,
    log_type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
    FOREIGN KEY (sync_id) REFERENCES sync_status(id)
);

-- Table to track selected sync items
CREATE TABLE sync_selections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sync_id INT,
    year VARCHAR(4),
    project_id VARCHAR(50) NULL,
    prov VARCHAR(2) DEFAULT '00',
    kab VARCHAR(2) DEFAULT '00',
    activity_id VARCHAR(50) NULL,
    is_processed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (sync_id) REFERENCES sync_status(id)
);

-- Create view for monitoring data
CREATE VIEW v_monitoring AS
SELECT 
    m.id,
    p.name AS project_name,
    m.project_id,
    m.year,
    m.prov,
    m.kab,
    c.name AS region_name,
    m.activity_id,
    m.activity_name,
    m.start_date,
    m.end_date,
    m.plan_date,
    m.realization_date,
    m.progress,
    m.status,
    m.notes,
    m.last_updated
FROM 
    monitoring_data m
    JOIN projects p ON m.project_id = p.id AND m.year = p.year
    JOIN coverages c ON m.project_id = c.project_id AND m.year = c.year AND m.prov = c.prov AND m.kab = c.kab;

-- Create indexes for better performance
CREATE INDEX idx_users_level ON users(user_level);
CREATE INDEX idx_coverages_project ON coverages(project_id, year);
CREATE INDEX idx_coverages_area ON coverages(prov, kab);
CREATE INDEX idx_monitoring_project ON monitoring_data(project_id, year);
CREATE INDEX idx_monitoring_region ON monitoring_data(prov, kab);
CREATE INDEX idx_monitoring_activity ON monitoring_data(activity_id);
CREATE INDEX idx_monitoring_status ON monitoring_data(status);
CREATE INDEX idx_sync_status_time ON sync_status(sync_start, sync_end); 