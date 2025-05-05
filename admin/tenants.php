<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'rems';
$username = 'root';
$password = '';

// Initialize variables
$tenant = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'password' => ''
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
            // DELETE operation with transaction
            try {
                $pdo->beginTransaction();
                
                // First, check if tenant has active leases
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenant_properties WHERE tenant_id = ? AND status = 'active'");
                $stmt->execute([$_POST['id']]);
                $activeLeases = $stmt->fetchColumn();
                
                if ($activeLeases > 0) {
                    throw new Exception('Cannot delete tenant with active leases');
                }
                
                // Delete tenant
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'tenant'");
                $stmt->execute([$_POST['id']]);
                
                if ($stmt->rowCount() === 0) {
                    throw new Exception('Tenant not found or already deleted');
                }
                
                $pdo->commit();
                $_SESSION['success'] = 'Tenant deleted successfully';
                header('Location: tenants.php');
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = $e->getMessage();
            }
        } elseif (isset($_POST['save'])) {
            // Validate input
            $tenant = array_merge($tenant, $_POST);
            
            // Basic validation
            if (empty(trim($tenant['first_name']))) $errors['first_name'] = 'First name is required';
            if (empty(trim($tenant['last_name']))) $errors['last_name'] = 'Last name is required';
            if (empty(trim($tenant['email']))) $errors['email'] = 'Email is required';
            
            // Email validation
            if (!filter_var($tenant['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
            
            // Password validation for new tenants
            if (empty($tenant['id']) && (empty($tenant['password']) || strlen($tenant['password']) < 8)) {
                $errors['password'] = 'Password must be at least 8 characters';
            }
            
            // Check if email exists (for new tenants or when email is changed)
            if (empty($errors['email'])) {
                $query = "SELECT id FROM users WHERE email = ? AND user_type = 'tenant'";
                $params = [$tenant['email']];
                
                if (!empty($tenant['id'])) {
                    $query .= " AND id != ?";
                    $params[] = $tenant['id'];
                }
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                
                if ($stmt->fetch()) {
                    $errors['email'] = 'Email already exists for another tenant';
                }
            }

            if (empty($errors)) {
                try {
                    $pdo->beginTransaction();
                    
                    if (empty($tenant['id'])) {
                        // CREATE operation
                        $hashedPassword = password_hash($tenant['password'], PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO users (
                                first_name, last_name, email, password, 
                                user_type, created_at
                            ) VALUES (?, ?, ?, ?, 'tenant', NOW())
                        ");
                        $stmt->execute([
                            trim($tenant['first_name']), 
                            trim($tenant['last_name']), 
                            trim($tenant['email']), 
                            $hashedPassword
                        ]);
                        
                        $tenantId = $pdo->lastInsertId();
                        $_SESSION['success'] = 'Tenant added successfully';
                        
                    } else {
                        // UPDATE operation
                        $sql = "UPDATE users SET 
                                first_name = ?, last_name = ?, email = ?
                                WHERE id = ? AND user_type = 'tenant'";
                        $params = [
                            trim($tenant['first_name']), 
                            trim($tenant['last_name']), 
                            trim($tenant['email']), 
                            $tenant['id']
                        ];
                        
                        // Only update password if provided
                        if (!empty($tenant['password'])) {
                            $hashedPassword = password_hash($tenant['password'], PASSWORD_DEFAULT);
                            $sql = "UPDATE users SET 
                                    first_name = ?, last_name = ?, email = ?, 
                                    password = ?
                                    WHERE id = ? AND user_type = 'tenant'";
                            $params = [
                                trim($tenant['first_name']), 
                                trim($tenant['last_name']), 
                                trim($tenant['email']), 
                                $hashedPassword, 
                                $tenant['id']
                            ];
                        }
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        
                        if ($stmt->rowCount() === 0) {
                            throw new Exception('Tenant not found or no changes made');
                        }
                        
                        $_SESSION['success'] = 'Tenant updated successfully';
                    }
                    
                    $pdo->commit();
                    header('Location: tenants.php');
                    exit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'edit' && isset($_GET['id'])) {
        // READ operation for edit form with input validation
        $tenantId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($tenantId === false || $tenantId <= 0) {
            header('Location: tenants.php');
            exit();
        }
        
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, email FROM users WHERE id = ? AND user_type = 'tenant'");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tenant) {
            $_SESSION['error'] = 'Tenant not found';
            header('Location: tenants.php');
            exit();
        }
        
        $tenant['password'] = ''; // Never show the hashed password
    } elseif ($action === 'delete' && isset($_GET['id'])) {
        // Show delete confirmation with validation
        $tenantId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($tenantId === false || $tenantId <= 0) {
            header('Location: tenants.php');
            exit();
        }
        
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE id = ? AND user_type = 'tenant'");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tenant) {
            $_SESSION['error'] = 'Tenant not found';
            header('Location: tenants.php');
            exit();
        }
        
        // Check for active leases
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenant_properties WHERE tenant_id = ? AND status = 'active'");
        $stmt->execute([$tenantId]);
        $activeLeases = $stmt->fetchColumn();
        
        if ($activeLeases > 0) {
            $errors[] = 'This tenant has active leases and cannot be deleted';
        }
    }

    // Fetch all tenants with pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    // Count total tenants
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'tenant'");
    $totalTenants = $totalStmt->fetchColumn();
    $totalPages = ceil($totalTenants / $perPage);
    
    // Fetch paginated tenants
    $stmt = $pdo->prepare("
        SELECT u.id, u.first_name, u.last_name, u.email, 
               (SELECT COUNT(*) FROM tenant_properties WHERE tenant_id = u.id AND status = 'active') AS active_leases
        FROM users u
        WHERE u.user_type = 'tenant'
        ORDER BY u.last_name, u.first_name
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check for messages from session
    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        $errors[] = $_SESSION['error'];
        unset($_SESSION['error']);
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
    <title>Tenant Management | REMS</title>
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

        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            background-color: var(--agent-primary-light);
        }

        .card-header.bg-danger {
            background-color: var(--danger-light);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--agent-primary-dark);
        }

        .card-header.bg-danger .card-title {
            color: var(--danger);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            background-color: var(--gray-50);
        }

        /* Form Styles */
        .form-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
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

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        .form-text {
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: var(--gray-600);
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

        /* Tenant Card Styles */
        .tenant-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .tenant-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .tenant-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
        }

        .tenant-info {
            margin-bottom: 1rem;
        }

        .tenant-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .tenant-contact {
            color: var(--gray-600);
            margin-bottom: 1rem;
        }

        .tenant-contact i {
            width: 1rem;
            text-align: center;
            margin-right: 0.5rem;
        }

        .badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 9999px;
        }

        .badge-lease {
            background-color: var(--agent-primary-light);
            color: var(--agent-primary-dark);
        }

        .tenant-actions {
            margin-top: auto;
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        /* Preview Image */
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
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
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .tenant-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .tenant-grid {
                grid-template-columns: 1fr;
            }
            
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
                <a href="dashboard.php" class="sidebar-nav-link">
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
                <a href="tenants.php" class="sidebar-nav-link active">
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
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0" style="padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <!-- Tenant Listing View -->
            <div class="page-header">
                <h1 class="page-title">Tenant Management</h1>
                <div class="header-actions">
                    <a href="tenants.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Tenant
                    </a>
                </div>
            </div>

            <div class="tenant-grid">
                <?php foreach ($tenants as $t): ?>
                    <div class="card tenant-card">
                        <div class="card-body">
                            <div class="tenant-info">
                                <h3 class="tenant-name"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></h3>
                                
                                <div class="tenant-contact">
                                    <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($t['email']); ?></div>
                                </div>
                                
                                <span class="badge badge-lease">
                                    <i class="fas fa-key"></i> <?php echo $t['active_leases']; ?> Active Lease<?php echo $t['active_leases'] != 1 ? 's' : ''; ?>
                                </span>
                            </div>
                            
                            <div class="tenant-actions">
                                <a href="tenants.php?action=edit&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="tenants.php?action=delete&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($tenants)): ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h3>No Tenants Found</h3>
                            <p class="text-muted">Start by adding your first tenant</p>
                            <a href="tenants.php?action=add" class="btn btn-primary mt-3">
                                <i class="fas fa-plus"></i> Add New Tenant
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="pagination-container">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="tenants.php?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="tenants.php?page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="tenants.php?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php elseif ($action === 'delete' && isset($tenant['id'])): ?>
            <!-- Delete Confirmation -->
            <div class="page-header">
                <h1 class="page-title">Delete Tenant</h1>
                <div class="header-actions">
                    <a href="tenants.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Tenants
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-danger">
                    <h2 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                    </h2>
                </div>
                <div class="card-body">
                    <p>Are you sure you want to delete tenant <strong><?php echo htmlspecialchars($tenant['first_name'] . ' ' . $tenant['last_name']); ?></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-circle"></i> This action cannot be undone.</p>
                    
                    <form method="post" class="mt-4">
                        <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
                        <div class="d-flex justify-content-between">
                            <a href="tenants.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" name="delete" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete Tenant
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <!-- Add/Edit Tenant Form -->
            <div class="page-header">
                <h1 class="page-title"><?php echo empty($tenant['id']) ? 'Add New Tenant' : 'Edit Tenant'; ?></h1>
                <div class="header-actions">
                    <a href="tenants.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Tenants
                    </a>
                </div>
            </div>
            
            <div class="form-section">
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
                    
                    <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -0.75rem;">
                        <div class="col" style="flex: 1; padding: 0 0.75rem; margin-bottom: 1.5rem; min-width: 250px;">
                            <label for="first_name" class="form-label">First Name*</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($tenant['first_name']); ?>" required>
                        </div>
                        <div class="col" style="flex: 1; padding: 0 0.75rem; margin-bottom: 1.5rem; min-width: 250px;">
                            <label for="last_name" class="form-label">Last Name*</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($tenant['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -0.75rem;">
                        <div class="col" style="flex: 1; padding: 0 0.75rem; margin-bottom: 1.5rem; min-width: 250px;">
                            <label for="email" class="form-label">Email*</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($tenant['email']); ?>" required>
                        </div>
                        <div class="col" style="flex: 1; padding: 0 0.75rem; margin-bottom: 1.5rem; min-width: 250px;">
                            <label for="password" class="form-label">Password<?php echo empty($tenant['id']) ? '*' : ''; ?></label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   <?php echo empty($tenant['id']) ? 'required' : ''; ?>>
                            <?php if (!empty($tenant['id'])): ?>
                                <div class="form-text">Leave blank to keep current password</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                        <a href="tenants.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" name="save" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo empty($tenant['id']) ? 'Add Tenant' : 'Update Tenant'; ?>
                        </button>
                    </div>
                </form>
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

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });
</script>
</body>
</html>