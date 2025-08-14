<?php
// download.php - Handle file downloads
require_once 'config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $form_id = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT form_name, file_path, file_name FROM forms WHERE id = ?");
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();
    
    if ($form && file_exists($form['file_path'])) {
        $file_path = $form['file_path'];
        $file_name = $form['form_name'] . '.' . pathinfo($form['file_name'], PATHINFO_EXTENSION);
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
        exit();
    } else {
        http_response_code(404);
        echo "File not found";
    }
} else {
    http_response_code(400);
    echo "Invalid request";
}
?>

<?php
// SQL dump to create database structure
/*
-- Create database
CREATE DATABASE IF NOT EXISTS government_portal;
USE government_portal;

-- Create tables
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_key VARCHAR(50) UNIQUE NOT NULL,
    dept_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE forms (
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

-- Insert default admin
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert departments
INSERT INTO departments (dept_key, dept_name) VALUES 
('health', 'Health & Medical Services'),
('education', 'Education Department'),
('transport', 'Transport & Road Safety'),
('revenue', 'Revenue & Taxation'),
('social', 'Social Welfare'),
('agriculture', 'Agriculture & Farming'),
('police', 'Police & Security'),
('municipal', 'Municipal Services');
*/
?>