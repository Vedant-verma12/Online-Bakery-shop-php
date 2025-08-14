<?php
// config.php - Database configuration
session_start();

$host = 'localhost';
$dbname = 'government_portal';
$username = 'root';
$password = ''; // Change this to your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
$createTables = "
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_key VARCHAR(50) UNIQUE NOT NULL,
    dept_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT,
    form_name VARCHAR(200) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size VARCHAR(20) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES admins(id) ON DELETE SET NULL
);

INSERT IGNORE INTO admins (username, password) VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "');

INSERT IGNORE INTO departments (dept_key, dept_name) VALUES 
('health', 'Health & Medical Services'),
('education', 'Education Department'),
('transport', 'Transport & Road Safety'),
('revenue', 'Revenue & Taxation'),
('social', 'Social Welfare'),
('agriculture', 'Agriculture & Farming'),
('police', 'Police & Security'),
('municipal', 'Municipal Services');
";

$pdo->exec($createTables);
?>