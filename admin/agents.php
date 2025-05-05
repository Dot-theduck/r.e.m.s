<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'rems';
$username = 'root';
$password = '';

// Initialize variables
$landlord = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'profile_image' => ''
];
$errors = [];
$success = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete'])) {
            // DELETE operation
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'landlord'");
            $stmt->execute([$_POST['id']]);
            $_SESSION['success'] = 'Landlord deleted successfully';
            header('Location: landlord_management.php');
            exit();
        } elseif (isset($_POST['save'])) {
            // Validate input
            $landlord = array_merge($landlord, $_POST);
            
            if (empty($landlord['first_name'])) $errors['first_name'] = 'First name is required';
            if (empty($landlord['last_name'])) $errors['last_name'] = 'Last name is required';
            if (empty($landlord['email'])) $errors['email'] = 'Email is required';
            if (!filter_var($landlord['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
            
            // Check if email exists (for new landlords)
            if (empty($landlord['id'])) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND user_type = 'landlord'");
                $stmt->execute([$landlord['email']]);
                if ($stmt->fetch()) $errors['email'] = 'Email already exists';
            }

            // Handle file upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/landlords/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                    $landlord['profile_image'] = $destination;
                } else {
                    $errors['profile_image'] = 'Failed to upload image';
                }
            }

            if (empty($errors)) {
                if (empty($landlord['id'])) {
                    // CREATE operation
                    $hashedPassword = password_hash($landlord['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (
                            first_name, last_name, email, phone, password, 
                            user_type, profile_image, created_at
                        ) VALUES (?, ?, ?, ?, ?, 'landlord', ?, NOW())
                    ");
                    $stmt->execute([
                        $landlord['first_name'], $landlord['last_name'], $landlord['email'], 
                        $landlord['phone'], $hashedPassword, $landlord['profile_image']
                    ]);
                    $_SESSION['success'] = 'Landlord added successfully';
                } else {
                    // UPDATE operation
                    $sql = "UPDATE users SET 
                            first_name = ?, last_name = ?, email = ?, phone = ?, 
                            profile_image = COALESCE(?, profile_image)
                            WHERE id = ? AND user_type = 'landlord'";
                    $params = [
                        $landlord['first_name'], $landlord['last_name'], $landlord['email'], 
                        $landlord['phone'], $landlord['profile_image'], $landlord['id']
                    ];
                    
                    // Only update password if provided
                    if (!empty($landlord['password'])) {
                        $hashedPassword = password_hash($landlord['password'], PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET 
                                first_name = ?, last_name = ?, email = ?, phone = ?, 
                                password = ?, profile_image = COALESCE(?, profile_image)
                                WHERE id = ? AND user_type = 'landlord'";
                        $params = [
                            $landlord['first_name'], $landlord['last_name'], $landlord['email'], 
                            $landlord['phone'], $hashedPassword, $landlord['profile_image'], $landlord['id']
                        ];
                    }
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $_SESSION['success'] = 'Landlord updated successfully';
                }
                header('Location: landlord_management.php');
                exit();
            }
        }
    } elseif ($action === 'edit' && isset($_GET['id'])) {
        // READ operation for edit form
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'landlord'");
        $stmt->execute([$_GET['id']]);
        $landlord = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$landlord) {
            header('Location: landlord_management.php');
            exit();
        }
        $landlord['password'] = ''; // Never show the hashed password
    } elseif ($action === 'delete' && isset($_GET['id'])) {
        // Show delete confirmation
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE id = ? AND user_type = 'landlord'");
        $stmt->execute([$_GET['id']]);
        $landlord = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$landlord) {
            header('Location: landlord_management.php');
            exit();
        }
    }

    // Fetch all landlords with property count
    $stmt = $pdo->query("
        SELECT u.*, 
               (SELECT COUNT(*) FROM properties WHERE landlord_id = u.id) AS property_count
        FROM users u
        WHERE u.user_type = 'landlord'
        ORDER BY u.last_name, u.first_name
    ");
    $landlords = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check for success message from session
    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Management | REMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Base Styles */
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --secondary: #64748b;
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --agent-primary: #6366f1;
            --agent-primary-dark: #4f46e5;
            --agent-primary-light: #a5b4fc;
            --agent-secondary: #8b5cf6;
            --border-radius: 0.5rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            color: var(--gray-800);
            background-color: var(--gray-100);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .agent-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background-color: white;
            border-right: 1px solid var(--gray-200);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 50;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--agent-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .sidebar-nav-item {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--gray-600);
            text-decoration: none;
            transition: all 0.2s;
            border-radius: 0.25rem;
            margin: 0 0.5rem;
        }

        .sidebar-nav-link:hover {
            background-color: var(--gray-100);
            color: var(--gray-800);
        }

        .sidebar-nav-link.active {
            background-color: var(--agent-primary-light);
            color: var(--agent-primary-dark);
            font-weight: 500;
        }

        .sidebar-nav-icon {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.625rem 1.25rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
            font-size: 0.875rem;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--agent-primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--agent-primary-dark);
        }

        .btn-secondary {
            background-color: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background-color: var(--gray-300);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e69009;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--gray-300);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            background-color: var(--gray-100);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-circle {
            border-radius: 50%;
            width: 2.5rem;
            height: 2.5rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            background-color: var(--agent-primary-light);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--agent-primary-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            color: var(--gray-800);
            background-color: white;
            background-clip: padding-box;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--agent-primary);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .form-select {
            display: block;
            width: 100%;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            color: var(--gray-800);
            background-color: white;
            background-clip: padding-box;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        .form-select:focus {
            border-color: var(--agent-primary);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .input-group {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            width: 100%;
        }

        .input-group-text {
            display: flex;
            align-items: center;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 400;
            line-height: 1.5;
            color: var(--gray-700);
            text-align: center;
            white-space: nowrap;
            background-color: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius) 0 0 var(--border-radius);
        }

        .input-group > .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* Landlord Card Styles */
        .landlord-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .landlord-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .landlord-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .card-banner {
            height: 80px;
            background: linear-gradient(to right, var(--agent-primary), var(--agent-primary-light));
        }

        .profile-img-container {
            position: relative;
            width: 110px;
            height: 110px;
            margin: 0 auto;
            margin-top: -55px;
            margin-bottom: 1rem;
        }

        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: var(--shadow);
        }

        .landlord-info {
            padding: 1rem 1.5rem;
            text-align: center;
        }

        .landlord-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .landlord-contact {
            color: var(--gray-600);
            margin-bottom: 1rem;
        }

        .landlord-contact p {
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .badge-property {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: var(--success-light);
            color: var(--success);
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .card-actions {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            border-left: 4px solid transparent;
        }

        .alert-success {
            background-color: var(--success-light);
            border-left-color: var(--success);
            color: var(--success);
        }

        .alert-danger {
            background-color: var(--danger-light);
            border-left-color: var(--danger);
            color: var(--danger);
        }

        /* Delete Confirmation */
        .delete-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .delete-card-header {
            background-color: var(--danger);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }

        .delete-card-body {
            padding: 1.5rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1.5rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .empty-description {
            color: var(--gray-600);
            margin-bottom: 1.5rem;
        }

        /* Preview Image */
        .preview-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin-top: 1rem;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background-color: var(--agent-primary);
            color: white;
            align-items: center;
            justify-content: center;
            z-index: 100;
            box-shadow: var(--shadow-md);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .landlord-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="agent-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-building"></i>
                <span>REMS ADMIN</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-nav-item">
                <a href="dashboard.php" class="sidebar-nav-link active">
                    <i class="fas fa-tachometer-alt sidebar-nav-icon"></i>
                    Dashboard
                </a>
            </div>
            <div class="sidebar-nav-item">
                <a href="properties.php" class="sidebar-nav-link">
                    <i class="fas fa-building sidebar-nav-icon"></i>
                    Properties
                </a>
            </div>
            <div class="sidebar-nav-item">
                <a href="tenants.php" class="sidebar-nav-link">
                    <i class="fas fa-users sidebar-nav-icon"></i>
                    Tenants
                </a>
            </div>
            <div class="sidebar-nav-item">
                <a href="payment.php" class="sidebar-nav-link">
                    <i class="fas fa-money-bill-wave sidebar-nav-icon"></i>
                    Payments
                </a>
            </div>
            
            <div class="sidebar-nav-item">
                <a href="../index.php" class="sidebar-nav-link">
                    <i class="fas fa-sign-out-alt sidebar-nav-icon"></i>
                    Logout
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Landlord Management</h1>
            <div class="header-actions">
                <?php if ($action === 'list'): ?>
                    <a href="landlord_management.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Landlord
                    </a>
                <?php else: ?>
                    <a href="landlord_management.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <!-- Landlord Listing View -->
            <div class="landlord-grid">
                <?php if (count($landlords) > 0): ?>
                    <?php foreach ($landlords as $l): ?>
                        <div class="landlord-card">
                            <div class="card-banner"></div>
                          
                            
                            <div class="landlord-info">
                                <h3 class="landlord-name"><?php echo htmlspecialchars($l['first_name'] . ' ' . $l['last_name']); ?></h3>
                                <div class="landlord-contact">
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($l['email']); ?></p>
                                    
                                </div>
                                <span class="badge-property">
                                    <i class="fas fa-building"></i> <?php echo $l['property_count']; ?> Properties
                                </span>
                                <div class="card-actions">
                                    <a href="landlord_properties.php?landlord_id=<?php echo $l['id']; ?>" class="btn btn-sm btn-primary btn-circle" title="View Properties">
                                        <i class="fas fa-building"></i>
                                    </a>
                                    <a href="landlord_management.php?action=edit&id=<?php echo $l['id']; ?>" class="btn btn-sm btn-warning btn-circle" title="Edit Landlord">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="landlord_management.php?action=delete&id=<?php echo $l['id']; ?>" class="btn btn-sm btn-danger btn-circle" title="Delete Landlord">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="empty-title">No Landlords Found</h3>
                        <p class="empty-description">
                            There are no landlords in the system yet. Get started by adding your first landlord.
                        </p>
                        <a href="landlord_management.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Landlord
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($action === 'delete' && isset($landlord['id'])): ?>
            <!-- Delete Confirmation -->
            <div class="card delete-card">
                <div class="delete-card-header">
                    <h2><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h2>
                </div>
                <div class="delete-card-body">
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <i class="fas fa-user-slash" style="font-size: 4rem; color: var(--danger); margin-bottom: 1rem;"></i>
                        <h3>Are you sure you want to delete this landlord?</h3>
                        <p style="color: var(--gray-600);">
                            You are about to delete <strong><?php echo htmlspecialchars($landlord['first_name'] . ' ' . $landlord['last_name']); ?></strong>.<br>
                            This action cannot be undone.
                        </p>
                    </div>
                    <form method="post" style="display: flex; justify-content: space-between;">
                        <input type="hidden" name="id" value="<?php echo $landlord['id']; ?>">
                        <a href="landlord_management.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" name="delete" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Permanently
                        </button>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <!-- Add/Edit Landlord Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-<?php echo empty($landlord['id']) ? 'plus' : 'edit'; ?>"></i>
                        <?php echo empty($landlord['id']) ? 'Add New Landlord' : 'Edit Landlord'; ?>
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul style="margin-top: 0.5rem; margin-bottom: 0;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $landlord['id']; ?>">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($landlord['first_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($landlord['last_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($landlord['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($landlord['phone']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <?php echo empty($landlord['id']) ? 'Password' : 'New Password (leave blank to keep current)'; ?>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           <?php echo empty($landlord['id']) ? 'required' : ''; ?>>
                                    <button class="btn btn-outline" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile_image" class="form-label">Profile Image</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                
                                <?php if (!empty($landlord['profile_image'])): ?>
                                    <div style="margin-top: 1rem;">
                                        <p>Current Image:</p>
                                        <div class="preview-image-container">
                                            <img src="<?php echo htmlspecialchars($landlord['profile_image']); ?>" class="preview-image" id="currentImage">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="preview-image-container" id="previewContainer" style="display: none;">
                                    <img src="#" class="preview-image" id="imagePreview">
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                            <a href="landlord_management.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" name="save" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Landlord
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Mobile menu toggle -->
<div class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</div>

<script>
    // Mobile menu toggle functionality
    document.getElementById('mobileMenuToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
        
        const icon = this.querySelector('i');
        if (icon.classList.contains('fa-bars')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });

    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    // Preview image before upload
    const profileImage = document.getElementById('profile_image');
    const imagePreview = document.getElementById('imagePreview');
    const previewContainer = document.getElementById('previewContainer');
    
    if (profileImage && imagePreview && previewContainer) {
        profileImage.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
</script>
</body>
</html>