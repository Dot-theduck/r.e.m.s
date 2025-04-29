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
    <title>REMS - Landlord Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f94144;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border: #dee2e6;
            --shadow: rgba(0, 0, 0, 0.1);
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: #f0f4f8;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* Layout */
        .layout {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: #2c3e50;
            color: white;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            height: var(--header-height);
        }
        
        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
            list-style: none;
        }
        
        .sidebar-item {
            margin-bottom: 5px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid var(--primary);
        }
        
        .sidebar-icon {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-collapsed .sidebar-text {
            display: none;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }
        
        .main-content-expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .container-fluid {
            padding: 20px;
        }
        
        header {
            background-color: white;
            box-shadow: 0 2px 10px var(--shadow);
            padding: 0 20px;
            margin-bottom: 30px;
            height: var(--header-height);
            display: flex;
            align-items: center;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        
        .page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            color: var(--primary);
            font-weight: 600;
            margin: 0;
            font-size: 1.8rem;
        }
        
        /* Landlord Cards */
        .landlord-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 24px;
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .landlord-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.25);
        }
        
        .card-banner {
            height: 80px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
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
            border: 4px solid #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            background-color: #f8f9fc;
        }
        
        .landlord-info {
            padding: 1rem 1.5rem;
            text-align: center;
        }
        
        .landlord-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .landlord-contact {
            color: var(--gray);
            margin-bottom: 1rem;
        }
        
        .landlord-contact i {
            width: 20px;
            color: var(--primary);
            margin-right: 0.25rem;
        }
        
        .badge-property {
            background-color: var(--success);
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 10rem;
            font-size: 0.75rem;
            margin-bottom: 1rem;
            display: inline-block;
        }
        
        .card-actions {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-circle {
            border-radius: 100%;
            height: 2.5rem;
            width: 2.5rem;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Form Styling */
        .form-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 2rem;
        }
        
        .form-section h2 {
            color: var(--dark);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-control {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            border: 1px solid var(--border);
        }
        
        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        .preview-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin-top: 1rem;
            border-radius: 0.35rem;
            overflow: hidden;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Alerts */
        .alert {
            border-radius: 0.35rem;
            border: 1px solid transparent;
        }
        
        .alert-success {
            color: #0f6848;
            background-color: #d1f7e9;
            border-color: #bff2de;
        }
        
        .alert-danger {
            color: #78261f;
            background-color: #fadbd8;
            border-color: #f8ccc8;
        }
        
        /* Delete Confirmation */
        .delete-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow: hidden;
        }
        
        .delete-card-header {
            background-color: var(--danger);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 700;
        }
        
        .delete-card-body {
            padding: 1.5rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--border);
            margin-bottom: 1.5rem;
        }
        
        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .empty-description {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }
        
        /* Buttons */
        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.35rem;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
        }
        
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-danger {
            color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-outline-danger:hover {
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        /* Overlay for mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .overlay-visible {
            display: block;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }
            
            .sidebar-text {
                display: none;
            }
            
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }
            
            .sidebar-expanded {
                width: var(--sidebar-width);
                z-index: 1050;
            }
            
            .sidebar-expanded .sidebar-text {
                display: inline;
            }
        }
        
        @media (max-width: 768px) {
            .profile-img-container {
                width: 90px;
                height: 90px;
                margin-top: -45px;
            }
            
            .landlord-name {
                font-size: 1.1rem;
            }
            
            .card-banner {
                height: 60px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="#" class="sidebar-logo">
                    <i class="fas fa-building"></i>
                    <span class="sidebar-text">REMS Admin</span>
                </a>
                <button class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="dashboard.php" class="sidebar-link">
                        <i class="fas fa-tachometer-alt sidebar-icon"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="tenant_management.php" class="sidebar-link">
                        <i class="fas fa-users sidebar-icon"></i>
                        <span class="sidebar-text">Tenant Management</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="landlord_management.php" class="sidebar-link active">
                        <i class="fas fa-user-tie sidebar-icon"></i>
                        <span class="sidebar-text">Agent Management</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="payment.php" class="sidebar-link">
                        <i class="fas fa-money-bill-wave sidebar-icon"></i>
                        <span class="sidebar-text">Payment</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="properties.php" class="sidebar-link">
                        <i class="fas fa-home sidebar-icon"></i>
                        <span class="sidebar-text">Properties</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt sidebar-icon"></i>
                    <span class="sidebar-text">Log Out</span>
                </a>
            </div>
        </aside>
        
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>
        
        <!-- Main Content -->
        <main class="main-content" id="main-content">
            <header>
                <div class="header-content">
                    <h1 class="page-title">
                        <i class="fas fa-user-tie me-2"></i> Agent Management
                    </h1>
                    <div class="header-actions">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-calendar"></i> <?= date('F j, Y') ?>
                        </span>
                    </div>
                </div>
            </header>

            <div class="container-fluid">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <!-- Landlord Listing View -->
                    <div class="page-header">
                        <h2 class="page-title">
                            <i class="fas fa-users me-2 text-primary"></i> Agent Directory
                        </h2>
                        <a href="landlord_management.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus fa-sm me-2"></i> Add New Agent
                        </a>
                    </div>

                    <div class="row">
                        <?php if (count($landlords) > 0): ?>
                            <?php foreach ($landlords as $l): ?>
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="landlord-card">
                                        <div class="card-banner"></div>
                                        <!-- <div class="profile-img-container">
                                            <img src="<?php echo htmlspecialchars($l['profile_image'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($l['first_name'] . '+' . $l['last_name']) . '&size=110&background=4361ee&color=fff'); ?>" 
                                                class="profile-img" 
                                                alt="<?php echo htmlspecialchars($l['first_name'] . ' ' . $l['last_name']); ?>">
                                        </div> -->
                                        
                                        <div class="landlord-info">
                                            <h5 class="landlord-name"><?php echo htmlspecialchars($l['first_name'] . ' ' . $l['last_name']); ?></h5>
                                            <div class="landlord-contact">
                                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($l['email']); ?></p>
                                                <!-- <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($l['phone'] ?: 'N/A'); ?></p> -->
                                            </div>
                                            <span class="badge-property">
                                                <i class="fas fa-building me-1"></i>
                                                <?php echo $l['property_count']; ?> Propert<?php echo $l['property_count'] != 1 ? 'ies' : 'y'; ?>
                                            </span>
                                            <div class="card-actions">
                                                <a href="landlord_properties.php?landlord_id=<?php echo $l['id']; ?>" class="btn btn-success btn-circle" title="View Properties">
                                                    <i class="fas fa-building"></i>
                                                </a>
                                                <a href="landlord_management.php?action=edit&id=<?php echo $l['id']; ?>" class="btn btn-primary btn-circle" title="Edit Agent">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="landlord_management.php?action=delete&id=<?php echo $l['id']; ?>" class="btn btn-danger btn-circle" title="Delete Agent">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <h3 class="empty-title">No Agents Found</h3>
                                    <p class="empty-description">
                                        There are no agents in the system yet. Get started by adding your first agent.
                                    </p>
                                    <a href="landlord_management.php?action=add" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Add New Agent
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($action === 'delete' && isset($landlord['id'])): ?>
                    <!-- Delete Confirmation -->
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="delete-card">
                                <div class="delete-card-header">
                                    <h3 class="m-0"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion</h3>
                                </div>
                                <div class="delete-card-body">
                                    <div class="text-center mb-4">
                                        <div class="mb-3">
                                            <i class="fas fa-user-slash fa-4x text-danger"></i>
                                        </div>
                                        <h4>Are you sure you want to delete this agent?</h4>
                                        <p class="text-muted">
                                            You are about to delete <strong><?php echo htmlspecialchars($landlord['first_name'] . ' ' . $landlord['last_name']); ?></strong>. 
                                            This action cannot be undone.
                                        </p>
                                    </div>
                                    <form method="post">
                                        <input type="hidden" name="id" value="<?php echo $landlord['id']; ?>">
                                        <div class="d-flex justify-content-between">
                                            <a href="landlord_management.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-arrow-left me-2"></i> Cancel
                                            </a>
                                            <button type="submit" name="delete" class="btn btn-danger">
                                                <i class="fas fa-trash me-2"></i> Delete Permanently
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Add/Edit Landlord Form -->
                    <div class="page-header">
                        <h2 class="page-title">
                            <i class="fas fa-<?php echo empty($landlord['id']) ? 'plus' : 'edit'; ?> me-2 text-primary"></i> 
                            <?php echo empty($landlord['id']) ? 'Add New Agent' : 'Edit Agent'; ?>
                        </h2>
                        <a href="landlord_management.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to List
                        </a>
                    </div>
                    
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="form-section">
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong>Please fix the following errors:</strong>
                                        <ul class="mb-0 mt-2">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <form method="post" enctype="multipart/form-data" id="landlordForm">
                                    <input type="hidden" name="id" value="<?php echo $landlord['id']; ?>">
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="first_name" class="form-label">First Name*</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                                       value="<?php echo htmlspecialchars($landlord['first_name']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label">Last Name*</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                                       value="<?php echo htmlspecialchars($landlord['last_name']); ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email Address*</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($landlord['email']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                <input type="tel" class="form-control" id="phone" name="phone" 
                                                       value="<?php echo htmlspecialchars($landlord['phone']); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">
                                                Password<?php echo empty($landlord['id']) ? '*' : ''; ?>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="password" name="password" 
                                                       <?php echo empty($landlord['id']) ? 'required' : ''; ?>>
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <?php if (!empty($landlord['id'])): ?>
                                                <small class="text-muted">Leave blank to keep current password</small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="profile_image" class="form-label">Profile Image</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-image"></i></span>
                                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                            </div>
                                            <div class="d-flex justify-content-center mt-3">
                                                <?php if (!empty($landlord['profile_image'])): ?>
                                                    <div class="preview-image-container">
                                                        <img src="<?php echo htmlspecialchars($landlord['profile_image']); ?>" class="preview-image" id="imagePreview">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="preview-image-container" style="display: none;">
                                                        <img src="#" class="preview-image" id="imagePreview">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                        <a href="landlord_management.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i> Cancel
                                        </a>
                                        <button type="submit" name="save" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Save Agent
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const overlay = document.getElementById('overlay');
            
            // Toggle sidebar on button click
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-expanded');
                    overlay.classList.toggle('overlay-visible');
                });
            }
            
            // Close sidebar when clicking overlay
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('sidebar-expanded');
                    overlay.classList.remove('overlay-visible');
                });
            }
            
            // Preview image before upload
            const imageInput = document.getElementById('profile_image');
            const imagePreview = document.getElementById('imagePreview');
            const previewContainer = document.querySelector('.preview-image-container');
            
            if (imageInput && imagePreview && previewContainer) {
                imageInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                            previewContainer.style.display = 'block';
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle eye icon
                    const eyeIcon = this.querySelector('i');
                    eyeIcon.classList.toggle('fa-eye');
                    eyeIcon.classList.toggle('fa-eye-slash');
                });
            }

            // Form validation with visual feedback
            const form = document.getElementById('landlordForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let valid = true;
                    const requiredFields = form.querySelectorAll('[required]');
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                            field.parentElement.classList.add('has-error');
                            valid = false;
                        } else {
                            field.classList.remove('is-invalid');
                            field.parentElement.classList.remove('has-error');
                        }
                    });

                    if (!valid) {
                        e.preventDefault();
                        // Scroll to the first error
                        const firstError = form.querySelector('.is-invalid');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstError.focus();
                        }
                    }
                });
                
                // Real-time validation
                const inputs = form.querySelectorAll('input');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        if (this.hasAttribute('required') && !this.value.trim()) {
                            this.classList.add('is-invalid');
                        } else {
                            this.classList.remove('is-invalid');
                        }
                        
                        // Email validation
                        if (this.type === 'email' && this.value.trim()) {
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(this.value)) {
                                this.classList.add('is-invalid');
                            } else {
                                this.classList.remove('is-invalid');
                            }
                        }
                    });
                });
            }
            
            // Responsive sidebar handling
            function handleResize() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('sidebar-expanded');
                    mainContent.classList.remove('main-content-expanded');
                    overlay.classList.remove('overlay-visible');
                }
            }
            
            // Initial check and event listener for window resize
            handleResize();
            window.addEventListener('resize', handleResize);
        });
    </script>
</body>
</html>

## Enhanced Tenant Management

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
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'tenant'");
            $stmt->execute([$_POST['id']]);
            $_SESSION['success'] = 'Tenant deleted successfully';
            header('Location: tenant_management.php');
            exit();
        } elseif (isset($_POST['save'])) {
            // Validate input
            $tenant = array_merge($tenant, $_POST);
            
            if (empty($tenant['first_name'])) $errors['first_name'] = 'First name is required';
            if (empty($tenant['last_name'])) $errors['last_name'] = 'Last name is required';
            if (empty($tenant['email'])) $errors['email'] = 'Email is required';
            if (!filter_var($tenant['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
            
            // Check if email exists (for new tenants)
            if (empty($tenant['id'])) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND user_type = 'tenant'");
                $stmt->execute([$tenant['email']]);
                if ($stmt->fetch()) $errors['email'] = 'Email already exists';
            }

            // Handle file upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/tenants/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                    $tenant['profile_image'] = $destination;
                } else {
                    $errors['profile_image'] = 'Failed to upload image';
                }
            }

            if (empty($errors)) {
                if (empty($tenant['id'])) {
                    // CREATE operation
                    $hashedPassword = password_hash($tenant['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (
                            first_name, last_name, email, phone, password, 
                            user_type, profile_image, created_at
                        ) VALUES (?, ?, ?, ?, ?, 'tenant', ?, NOW())
                    ");
                    $stmt->execute([
                        $tenant['first_name'], $tenant['last_name'], $tenant['email'], 
                        $tenant['phone'], $hashedPassword, $tenant['profile_image']
                    ]);
                    $_SESSION['success'] = 'Tenant added successfully';
                } else {
                    // UPDATE operation
                    $sql = "UPDATE users SET 
                            first_name = ?, last_name = ?, email = ?, phone = ?, 
                            profile_image = COALESCE(?, profile_image)
                            WHERE id = ? AND user_type = 'tenant'";
                    $params = [
                        $tenant['first_name'], $tenant['last_name'], $tenant['email'], 
                        $tenant['phone'], $tenant['profile_image'], $tenant['id']
                    ];
                    
                    // Only update password if provided
                    if (!empty($tenant['password'])) {
                        $hashedPassword = password_hash($tenant['password'], PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET 
                                first_name = ?, last_name = ?, email = ?, phone = ?, 
                                password = ?, profile_image = COALESCE(?, profile_image)
                                WHERE id = ? AND user_type = 'tenant'";
                        $params = [
                            $tenant['first_name'], $tenant['last_name'], $tenant['email'], 
                            $tenant['phone'], $hashedPassword, $tenant['profile_image'], $tenant['id']
                        ];
                    }
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $_SESSION['success'] = 'Tenant updated successfully';
                }
                header('Location: tenant_management.php');
                exit();
            }
        }
    } elseif ($action === 'edit' && isset($_GET['id'])) {
        // READ operation for edit form
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'tenant'");
        $stmt->execute([$_GET['id']]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tenant) {
            header('Location: tenant_management.php');
            exit();
        }
        $tenant['password'] = ''; // Never show the hashed password
    } elseif ($action === 'delete' && isset($_GET['id'])) {
        // Show delete confirmation
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE id = ? AND user_type = 'tenant'");
        $stmt->execute([$_GET['id']]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tenant) {
            header('Location: tenant_management.php');
            exit();
        }
    }

    // Fetch all tenants
    $stmt = $pdo->query("
        SELECT u.*, 
               (SELECT COUNT(*) FROM tenant_properties WHERE tenant_id = u.id AND status = 'active') AS active_leases
        FROM users u
        WHERE u.user_type = 'tenant'
        ORDER BY u.last_name, u.first_name
    ");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check for success message from session
    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

&lt;!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REMS - Tenant Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f94144;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border: #dee2e6;
            --shadow: rgba(0, 0, 0, 0.1);
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: #f0f4f8;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* Layout */
        .layout {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: #2c3e50;
            color: white;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            height: var(--header-height);
        }
        
        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
            list-style: none;
        }
        
        .sidebar-item {
            margin-bottom: 5px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid var(--primary);
        }
        
        .sidebar-icon {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-collapsed .sidebar-text {
            display: none;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }
        
        .main-content-expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .container-fluid {
            padding: 20px;
        }
        
        header {
            background-color: white;
            box-shadow: 0 2px 10px var(--shadow);
            padding: 0 20px;
            margin-bottom: 30px;
            height: var(--header-height);
            display: flex;
            align-items: center;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        
        .page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            color: var(--primary);
            font-weight: 600;
            margin: 0;
            font-size: 1.8rem;
        }
        
        /* Tenant Cards */
        .tenant-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 24px;
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .tenant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.25);
        }
        
        .card-banner {
            height: 80px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
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
            border: 4px solid #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            background-color: #f8f9fc;
        }
        
        .tenant-info {
            padding: 1rem 1.5rem;
            text-align: center;
        }
        
        .tenant-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .tenant-contact {
            color: var(--gray);
            margin-bottom: 1rem;
        }
        
        .tenant-contact i {
            width: 20px;
            color: var(--primary);
            margin-right: 0.25rem;
        }
        
        .badge-lease {
            background-color: var(--success);
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 10rem;
            font-size: 0.75rem;
            margin-bottom: 1rem;
            display: inline-block;
        }
        
        .card-actions {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-circle {
            border-radius: 100%;
            height: 2.5rem;
            width: 2.5rem;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Form Styling */
        .form-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 2rem;
        }
        
        .form-section h2 {
            color: var(--dark);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-control {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            border: 1px solid var(--border);
        }
        
        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        .preview-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin-top: 1rem;
            border-radius: 0.35rem;
            overflow: hidden;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Alerts */
        .alert {
            border-radius: 0.35rem;
            border: 1px solid transparent;
        }
        
        .alert-success {
            color: #0f6848;
            background-color: #d1f7e9;
            border-color: #bff2de;
        }
        
        .alert-danger {
            color: #78261f;
            background-color: #fadbd8;
            border-color: #f8ccc8;
        }
        
        /* Delete Confirmation */
        .delete-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow: hidden;
        }
        
        .delete-card-header {
            background-color: var(--danger);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 700;
        }
        
        .delete-card-body {
            padding: 1.5rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--border);
            margin-bottom: 1.5rem;
        }
        
        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .empty-description {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }
        
        /* Buttons */
        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.35rem;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
        }
        
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-danger {
            color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-outline-danger:hover {
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        /* Overlay for mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .overlay-visible {
            display: block;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }
            
            .sidebar-text {
                display: none;
            }
            
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }
            
            .sidebar-expanded {
                width: var(--sidebar-width);
                z-index: 1050;
            }
            
            .sidebar-expanded .sidebar-text {
                display: inline;
            }
        }
        
        @media (max-width: 768px) {
            .profile-img-container {
                width: 90px;
                height: 90px;
                margin-top: -45px;
            }
            
            .tenant-name {
                font-size: 1.1rem;
            }
            
            .card-banner {
                height: 60px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        &lt;!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="#" class="sidebar-logo">
                    <i class="fas fa-building"></i>
                    <span class="sidebar-text">REMS Admin</span>
                </a>
                <button class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="dashboard.php" class="sidebar-link">
                        <i class="fas fa-tachometer-alt sidebar-icon"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="tenant_management.php" class="sidebar-link active">
                        <i class="fas fa-users sidebar-icon"></i>
                        <span class="sidebar-text">Tenant Management</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="landlord_management.php" class="sidebar-link">
                        <i class="fas fa-user-tie sidebar-icon"></i>
                        <span class="sidebar-text">Agent Management</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="payment.php" class="sidebar-link">
                        <i class="fas fa-money-bill-wave sidebar-icon"></i>
                        <span class="sidebar-text">Payment</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="properties.php" class="sidebar-link">
                        <i class="fas fa-home sidebar-icon"></i>
                        <span class="sidebar-text">Properties</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt sidebar-icon"></i>
                    <span class="sidebar-text">Log Out</span>
                </a>
            </div>
        </aside>
        
        &lt;!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>
        
        &lt;!-- Main Content -->
        <main class="main-content" id="main-content">
            <header>
                <div class="header-content">
                    <h1 class="page-title">
                        <i class="fas fa-users me-2"></i> Tenant Management
                    </h1>
                    <div class="header-actions">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-calendar"></i> <?= date('F j, Y') ?>
                        </span>
                    </div>
                </div>
            </header>

            <div class="container-fluid">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($action === 'list'): ?>
                    &lt;!-- Tenant Listing View -->
                    <div class="page-header">
                        <h2 class="page-title">
                            <i class="fas fa-users me-2 text-primary"></i> Tenant Directory
                        </h2>
                        <a href="tenant_management.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus fa-sm me-2"></i> Add New Tenant
                        </a>
                    </div>

                    <div class="row">
                        <?php if (count($tenants) > 0): ?>
                            <?php foreach ($tenants as $t): ?>
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="tenant-card">
                                        <div class="card-banner"></div>
                                        <!-- <div class="profile-img-container">
                                            <img src="<?php echo htmlspecialchars($t['profile_image'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($t['first_name'] . '+' . $t['last_name']) . '&size=110&background=4361ee&color=fff'); ?>" 
                                                class="profile-img" 
                                                alt="<?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?>">
                                        </div> -->
                                        
                                        <div class="tenant-info">
                                            <h5 class="tenant-name"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></h5>
                                            <div class="tenant-contact">
                                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($t['email']); ?></p>
                                                <!-- <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($t['phone'] ?: 'N/A'); ?></p> -->
                                            </div>
                                            <span class="badge-lease">
                                                <i class="fas fa-file-contract me-1"></i>
                                                <?php echo $t['active_leases']; ?> Active Lease<?php echo $t['active_leases'] != 1 ? 's' : ''; ?>
                                            </span>
                                            <div class="card-actions">
                                                <a href="tenant_leases.php?tenant_id=<?php echo $t['id']; ?>" class="btn btn-success btn-circle" title="View Leases">
                                                    <i class="fas fa-file-contract"></i>
                                                </a>
                                                <a href="tenant_management.php?action=edit&id=<?php echo $t['id']; ?>" class="btn btn-primary btn-circle" title="Edit Tenant">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="tenant_management.php?action=delete&id=<?php echo $t['id']; ?>" class="btn btn-danger btn-circle" title="Delete Tenant">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h3 class="empty-title">No Tenants Found</h3>
                                    <p class="empty-description">
                                        There are no tenants in the system yet. Get started by adding your first tenant.
                                    </p>
                                    <a href="tenant_management.php?action=add" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Add New Tenant
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($action === 'delete' && isset($tenant['id'])): ?>
                    &lt;!-- Delete Confirmation -->
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="delete-card">
                                <div class="delete-card-header">
                                    <h3 class="m-0"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion</h3>
                                </div>
                                <div class="delete-card-body">
                                    <div class="text-center mb-4">
                                        <div class="mb-3">
                                            <i class="fas fa-user-slash fa-4x text-danger"></i>
                                        </div>
                                        <h4>Are you sure you want to delete this tenant?</h4>
                                        <p class="text-muted">
                                            You are about to delete <strong><?php echo htmlspecialchars($tenant['first_name'] . ' ' . $tenant['last_name']); ?></strong>. 
                                            This action cannot be undone.
                                        </p>
                                    </div>
                                    <form method="post">
                                        <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
                                        <div class="d-flex justify-content-between">
                                            <a href="tenant_management.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-arrow-left me-2"></i> Cancel
                                            </a>
                                            <button type="submit" name="delete" class="btn btn-danger">
                                                <i class="fas fa-trash me-2"></i> Delete Permanently
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    &lt;!-- Add/Edit Tenant Form -->
                    <div class="page-header">
                        <h2 class="page-title">
                            <i class="fas fa-<?php echo empty($tenant['id']) ? 'plus' : 'edit'; ?> me-2 text-primary"></i> 
                            <?php echo empty($tenant['id']) ? 'Add New Tenant' : 'Edit Tenant'; ?>
                        </h2>
                        <a href="tenant_management.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to List
                        </a>
                    </div>
                    
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="form-section">
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong>Please fix the following errors:</strong>
                                        <ul class="mb-0 mt-2">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <form method="post" enctype="multipart/form-data" id="tenantForm">
                                    <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="first_name" class="form-label">First Name*</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                                       value="<?php echo htmlspecialchars($tenant['first_name']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label">Last Name*</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                                       value="<?php echo htmlspecialchars($tenant['last_name']); ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email Address*</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($tenant['email']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                <input type="tel" class="form-control" id="phone" name="phone" 
                                                       value="<?php echo htmlspecialchars($tenant['phone']); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">
                                                Password<?php echo empty($tenant['id']) ? '*' : ''; ?>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="password" name="password" 
                                                       <?php echo empty($tenant['id']) ? 'required' : ''; ?>>
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <?php if (!empty($tenant['id'])): ?>
                                                <small class="text-muted">Leave blank to keep current password</small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="profile_image" class="form-label">Profile Image</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-image"></i></span>
                                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                            </div>
                                            <div class="d-flex justify-content-center mt-3">
                                                <?php if (!empty($tenant['profile_image'])): ?>
                                                    <div class="preview-image-container">
                                                        <img src="<?php echo htmlspecialchars($tenant['profile_image']); ?>" class="preview-image" id="imagePreview">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="preview-image-container" style="display: none;">
                                                        <img src="#" class="preview-image" id="imagePreview">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                        <a href="tenant_management.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i> Cancel
                                        </a>
                                        <button type="submit" name="save" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Save Tenant
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const overlay = document.getElementById('overlay');
            
            // Toggle sidebar on button click
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-expanded');
                    overlay.classList.toggle('overlay-visible');
                });
            }
            
            // Close sidebar when clicking overlay
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('sidebar-expanded');
                    overlay.classList.remove('overlay-visible');
                });
            }
            
            // Preview image before upload
            const imageInput = document.getElementById('profile_image');
            const imagePreview = document.getElementById('imagePreview');
            const previewContainer = document.querySelector('.preview-image-container');
            
            if (imageInput && imagePreview && previewContainer) {
                imageInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                            previewContainer.style.display = 'block';
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle eye icon
                    const eyeIcon = this.querySelector('i');
                    eyeIcon.classList.toggle('fa-eye');
                    eyeIcon.classList.toggle('fa-eye-slash');
                });
            }

            // Form validation with visual feedback
            const form = document.getElementById('tenantForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let valid = true;
                    const requiredFields = form.querySelectorAll('[required]');
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                            field.parentElement.classList.add('has-error');
                            valid = false;
                        } else {
                            field.classList.remove('is-invalid');
                            field.parentElement.classList.remove('has-error');
                        }
                    });

                    if (!valid) {
                        e.preventDefault();
                        // Scroll to the first error
                        const firstError = form.querySelector('.is-invalid');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstError.focus();
                        }
                    }
                });
                
                // Real-time validation
                const inputs = form.querySelectorAll('input');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        if (this.hasAttribute('required') && !this.value.trim()) {
                            this.classList.add('is-invalid');
                        } else {
                            this.classList.remove('is-invalid');
                        }
                        
                        // Email validation
                        if (this.type === 'email' && this.value.trim()) {
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(this.value)) {
                                this.classList.add('is-invalid');
                            } else {
                                this.classList.remove('is-invalid');
                            }
                        }
                    });
                });
            }
            
            // Responsive sidebar handling
            function handleResize() {
                if (window.innerWidth &lt; 992) {
                    sidebar.classList.remove('sidebar-expanded');
                    mainContent.classList.remove('main-content-expanded');
                    overlay.classList.remove('overlay-visible');
                }
            }
            
            // Initial check and event listener for window resize
            handleResize();
            window.addEventListener('resize', handleResize);
        });
    </script>
</body>
</html>