<?php
require_once '../tenant/config.php';

// Handle image upload and property creation
if (isset($_POST['create'])) {
    // Initialize variables
    $landlord_id = $_POST['landlord_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $square_footage = $_POST['square_footage'];
    $monthly_rent = $_POST['monthly_rent'];
    $status = $_POST['status'];
    $featured_image = ''; // Initialize empty

    // Handle file upload
    if (!empty($_FILES['featured_image']['name'])) {
        $target_dir = "uploads/properties/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $filename = time() . '_' . basename($_FILES["featured_image"]["name"]);
        $target_file = $target_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is an actual image
        $check = getimagesize($_FILES["featured_image"]["tmp_name"]);
        if ($check === false) {
            $error_message = "File is not an image.";
        }
        
        // Check file size (max 2MB)
        elseif ($_FILES["featured_image"]["size"] > 2000000) {
            $error_message = "Sorry, your file is too large (max 2MB).";
        }
        
        // Allow certain file formats
        elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
        
        // Try to upload file
        elseif (move_uploaded_file($_FILES["featured_image"]["tmp_name"], $target_file)) {
            $featured_image = $target_file;
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }

    // Only proceed if no errors
    if (!isset($error_message)) {
        $stmt = $mysqli->prepare("INSERT INTO properties 
            (landlord_id, title, description, address, city, state, zip_code, 
             bedrooms, bathrooms, square_footage, monthly_rent, status, featured_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("issssssiidsss",
            $landlord_id,
            $title,
            $description,
            $address,
            $city,
            $state,
            $zip_code,
            $bedrooms,
            $bathrooms,
            $square_footage,
            $monthly_rent,
            $status,
            $featured_image
        );
        
        if ($stmt->execute()) {
            $success_message = "Property added successfully!";
            // Reset form by redirecting
            header("Location: properties.php?success=1");
            exit();
        } else {
            $error_message = "Error adding property: " . $stmt->error;
        }
        $stmt->close();
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($mysqli->query("DELETE FROM properties WHERE id = $id")) {
        $success_message = "Property deleted successfully!";
    } else {
        $error_message = "Error deleting property: " . $mysqli->error;
    }
}

// FETCH properties
$result = $mysqli->query("SELECT p.*, 
                         (SELECT COUNT(*) FROM tenant_properties WHERE property_id = p.id AND status = 'active') AS active_tenants 
                         FROM properties p ORDER BY p.created_at DESC");
$properties = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}

// Get landlords for dropdown
$landlords_result = $mysqli->query("SELECT id, first_name, last_name FROM users WHERE user_type = 'landlord' ORDER BY last_name, first_name");
$landlords = [];
if ($landlords_result) {
    while ($row = $landlords_result->fetch_assoc()) {
        $landlords[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success_message = "Property added successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management | REMS</title>
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

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .table th {
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            background-color: var(--gray-100);
        }

        .table tbody tr:hover {
            background-color: var(--gray-50);
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-available {
            background-color: var(--success-light);
            color: var(--success);
        }

        .status-occupied {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        .status-maintenance {
            background-color: var(--warning-light);
            color: var(--warning);
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
            <h1 class="page-title">Property Management</h1>
            <div class="header-actions">
                <button type="button" class="btn btn-primary" onclick="toggleAddPropertyForm()">
                    <i class="fas fa-plus"></i> Add New Property
                </button>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Add Property Form Card -->
        <div class="card" id="addPropertyForm" style="display: none;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-plus-circle"></i> Add New Property
                </h2>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="landlord_id" class="form-label">Landlord</label>
                            <select name="landlord_id" id="landlord_id" class="form-select" required>
                                <option value="">Select Landlord</option>
                                <?php foreach ($landlords as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Modern Apartment" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" id="address" class="form-control" placeholder="Street Address" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label">City</label>
                            <input type="text" name="city" id="city" class="form-control" placeholder="City" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="state" class="form-label">State</label>
                            <input type="text" name="state" id="state" class="form-control" placeholder="State" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="zip_code" class="form-label">ZIP Code</label>
                            <input type="text" name="zip_code" id="zip_code" class="form-control" placeholder="ZIP Code" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="bedrooms" class="form-label">Bedrooms</label>
                            <input type="number" name="bedrooms" id="bedrooms" class="form-control" placeholder="Number of bedrooms" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="bathrooms" class="form-label">Bathrooms</label>
                            <input type="number" name="bathrooms" id="bathrooms" class="form-control" placeholder="Number of bathrooms" min="0" step="0.5" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="square_footage" class="form-label">Square Footage</label>
                            <input type="number" name="square_footage" id="square_footage" class="form-control" placeholder="Square footage" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="monthly_rent" class="form-label">Monthly Rent ($)</label>
                            <input type="number" name="monthly_rent" id="monthly_rent" class="form-control" placeholder="Monthly rent" min="0" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*">
                            <small class="form-text">Max size: 2MB (JPG, PNG, GIF)</small>
                            <div id="imagePreview" style="margin-top: 10px; display: none;">
                                <img id="previewImage" src="#" alt="Preview" style="max-width: 200px; max-height: 150px;">
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Property description"></textarea>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddPropertyForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="create" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Property
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Properties Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-list"></i> Property Listings
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Address</th>
                                <th>Bed/Bath</th>
                                <th>Rent</th>
                                <th>Status</th>
                                <th>Tenants</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($properties)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No properties found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <td><?php echo $property['id']; ?></td>
                                        <td><?php echo htmlspecialchars($property['title']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($property['address']); ?><br>
                                            <small><?php echo htmlspecialchars($property['city'] . ', ' . $property['state'] . ' ' . $property['zip_code']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo $property['bedrooms']; ?> BD / <?php echo $property['bathrooms']; ?> BA
                                        </td>
                                        <td>$<?php echo number_format($property['monthly_rent'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $property['status']; ?>">
                                                <?php echo ucfirst($property['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-lease">
                                                <?php echo $property['active_tenants']; ?> Active
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($property['featured_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($property['featured_image']); ?>" alt="Property Image" style="max-width: 60px; max-height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $property['id']; ?>" onclick="return confirm('Are you sure you want to delete this property?')" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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

    // Toggle Add Property Form
    function toggleAddPropertyForm() {
        const form = document.getElementById('addPropertyForm');
        if (form.style.display === 'none') {
            form.style.display = 'block';
            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        } else {
            form.style.display = 'none';
        }
    }

    // Image preview functionality
    document.getElementById('featured_image').addEventListener('change', function(e) {
        const preview = document.getElementById('previewImage');
        const previewContainer = document.getElementById('imagePreview');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            
            reader.readAsDataURL(this.files[0]);
        } else {
            previewContainer.style.display = 'none';
        }
    });

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                let valid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = 'var(--danger)';
                        valid = false;
                    } else {
                        field.style.borderColor = '';
                    }
                });

                // Validate file upload if present
                const fileInput = document.getElementById('featured_image');
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    
                    if (!validTypes.includes(file.type)) {
                        alert('Only JPG, PNG, and GIF images are allowed');
                        valid = false;
                    }
                    
                    if (file.size > maxSize) {
                        alert('Image size must be less than 2MB');
                        valid = false;
                    }
                }

                if (!valid) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
</body>
</html>