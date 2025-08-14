<?php
// api.php - API for frontend to fetch forms
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['department'])) {
        $dept_key = $_GET['department'];
        
        $stmt = $pdo->prepare("
            SELECT f.id, f.form_name, f.file_size, f.file_path, f.file_name
            FROM forms f
            JOIN departments d ON f.department_id = d.id
            WHERE d.dept_key = ?
            ORDER BY f.form_name
        ");
        $stmt->execute([$dept_key]);
        $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'forms' => $forms]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Department parameter required']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Only GET method allowed']);
}
?>