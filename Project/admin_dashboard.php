<?php
// admin_dashboard.php - Main admin dashboard
require_once 'config.php';
require_once 'auth.php';

requireLogin();

// Handle form upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_form'])) {
    $department_id = $_POST['department_id'];
    $form_name = $_POST['form_name'];
    
    if (isset($_FILES['form_file']) && $_FILES['form_file']['error'] == 0) {
        $file = $_FILES['form_file'];
        $upload_dir = 'uploads/';
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $file_path = $upload_dir . $file_name;
        $file_size = formatBytes($file['size']);
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $stmt = $pdo->prepare("INSERT INTO forms (department_id, form_name, file_name, file_size, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$department_id, $form_name, $file_name, $file_size, $file_path, $_SESSION['admin_id']]);
            $success = "Form uploaded successfully!";
        } else {
            $error = "Failed to upload file.";
        }
    } else {
        $error = "Please select a file to upload.";
    }
}

// Handle form deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $form_id = $_GET['delete'];
    
    // Get file path before deletion
    $stmt = $pdo->prepare("SELECT file_path FROM forms WHERE id = ?");
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();
    
    if ($form) {
        // Delete file from server
        if (file_exists($form['file_path'])) {
            unlink($form['file_path']);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM forms WHERE id = ?");
        $stmt->execute([$form_id]);
        $success = "Form deleted successfully!";
    }
}

// Get departments
$stmt = $pdo->query("SELECT * FROM departments ORDER BY dept_name");
$departments = $stmt->fetchAll();

// Get all forms with department info
$stmt = $pdo->query("
    SELECT f.*, d.dept_name, a.username as uploaded_by_name
    FROM forms f
    JOIN departments d ON f.department_id = d.id
    LEFT JOIN admins a ON f.uploaded_by = a.id
    ORDER BY f.created_at DESC
");
$forms = $stmt->fetchAll();

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    $base = log($size, 1024);
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Government Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #1e40af;
            font-size: 2rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .upload-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .upload-section h2 {
            color: #1e40af;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            color: #374151;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .upload-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .forms-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .forms-section h2 {
            color: #1e40af;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .forms-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .forms-table th {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .forms-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            background: rgba(255, 255, 255, 0.8);
        }

        .forms-table tr:hover td {
            background: rgba(59, 130, 246, 0.05);
        }

        .delete-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .forms-table {
                font-size: 0.9rem;
            }

            .forms-table th,
            .forms-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="upload-section">
            <h2>Upload New Form</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select name="department_id" id="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['dept_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="form_name">Form Name</label>
                        <input type="text" name="form_name" id="form_name" required 
                               placeholder="e.g., Medical Registration Form">
                    </div>

                    <div class="form-group">
                        <label for="form_file">Select File</label>
                        <input type="file" name="form_file" id="form_file" required
                               accept=".pdf,.doc,.docx,.xls,.xlsx">
                    </div>
                </div>

                <button type="submit" name="upload_form" class="upload-btn">
                    Upload Form
                </button>
            </form>
        </div>

        <div class="forms-section">
            <h2>Manage Forms (<?php echo count($forms); ?> total)</h2>
            
            <?php if (empty($forms)): ?>
                <p style="text-align: center; color: #64748b; font-size: 1.1rem; padding: 40px;">
                    No forms uploaded yet. Upload your first form above!
                </p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="forms-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Form Name</th>
                                <th>Department</th>
                                <th>File Size</th>
                                <th>Uploaded By</th>
                                <th>Upload Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($forms as $form): ?>
                                <tr>
                                    <td><?php echo $form['id']; ?></td>
                                    <td><?php echo htmlspecialchars($form['form_name']); ?></td>
                                    <td><?php echo htmlspecialchars($form['dept_name']); ?></td>
                                    <td><?php echo htmlspecialchars($form['file_size']); ?></td>
                                    <td><?php echo htmlspecialchars($form['uploaded_by_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($form['created_at'])); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $form['id']; ?>" 
                                           class="delete-btn"
                                           onclick="return confirm('Are you sure you want to delete this form?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>