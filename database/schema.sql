CREATE DATABASE IF NOT EXISTS valuechain;
USE valuechain;

CREATE TABLE industries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE value_chain_nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    industry_id INT,
    node_type ENUM('raw_materials', 'suppliers', 'manufacturers', 'distributors', 'retailers', 'customers') NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    stage_order INT NOT NULL,
    pain_points TEXT,
    opportunities TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (industry_id) REFERENCES industries(id) ON DELETE CASCADE
);

CREATE TABLE node_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_node_id INT,
    target_node_id INT,
    relationship_type VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (source_node_id) REFERENCES value_chain_nodes(id) ON DELETE CASCADE,
    FOREIGN KEY (target_node_id) REFERENCES value_chain_nodes(id) ON DELETE CASCADE
);

CREATE TABLE ip_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    request_count INT DEFAULT 1,
    last_request TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    first_request_of_day DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_date (ip_address, first_request_of_day)
);
