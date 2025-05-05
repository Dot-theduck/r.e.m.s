<?php
require_once '../../tenant/config.php';

$property_id = $_GET['id'] ?? null;
$success_message = '';
$error_message = '';

// Fetch existing property
if ($property_id) {
    $result = $mysqli->query("SELECT * FROM properties WHERE id = $property_id");
    $property = $result->fetch_assoc();
    
    // Fetch existing property images
    $images_result = $mysqli->query("SELECT * FROM property_images WHERE property_id = $property_id ORDER BY sort_order");
    $property_images = [];
    if ($images_result) {
        while ($img = $images_result->fetch_assoc()) {
            $property_images[] = $img;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Handle featured image
    $featured_image = $property['featured_image']; // Keep old image by default
    if (!empty($_FILES['featured_image']['name'])) {
        $target_dir = "uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $filename = time() . '_' . basename($_FILES["featured_image"]["name"]);
        $target_file = $target_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a valid image
        $check = getimagesize($_FILES["featured_image"]["tmp_name"]);
        if ($check === false) {
            $error_message = "File is not a valid image.";
        } 
        // Check file size (limit to 5MB)
        else if ($_FILES["featured_image"]["size"] > 5000000) {
            $error_message = "Sorry, your file is too large. Maximum size is 5MB.";
        }
        // Allow only certain file formats
        else if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
        // If everything is ok, try to upload file
        else if (move_uploaded_file($_FILES["featured_image"]["tmp_name"], $target_file)) {
            $featured_image = $target_file;

            // Delete old image
            if (!empty($property['featured_image']) && file_exists($property['featured_image'])) {
                unlink($property['featured_image']);
            }
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }

    // Only proceed with database update if there are no errors
    if (empty($error_message)) {
        $stmt = $mysqli->prepare("UPDATE properties SET 
            title = ?, description = ?, address = ?, city = ?, state = ?, zip_code = ?, 
            bedrooms = ?, bathrooms = ?, square_footage = ?, monthly_rent = ?, 
            status = ?, featured_image = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?");

        $stmt->bind_param("ssssssiidsssi", 
            $title, $description, $address, $city, $state, $zip_code,
            $bedrooms, $bathrooms, $square_footage, $monthly_rent,
            $status, $featured_image, $property_id
        );

        if ($stmt->execute()) {
            $success_message = "Property details updated successfully!";
            
            // Process additional property images
            if (!empty($_FILES['property_images']['name'][0])) {
                $uploadedImages = 0;
                $failedImages = 0;
                
                // Get the current highest sort order
                $sort_result = $mysqli->query("SELECT MAX(sort_order) as max_order FROM property_images WHERE property_id = $property_id");
                $sort_row = $sort_result->fetch_assoc();
                $next_sort_order = ($sort_row['max_order'] ?? 0) + 1;
                
                // Loop through each uploaded image
                foreach ($_FILES['property_images']['name'] as $key => $name) {
                    if (empty($name)) continue;
                    
                    $tmp_name = $_FILES['property_images']['tmp_name'][$key];
                    $size = $_FILES['property_images']['size'][$key];
                    $type = $_FILES['property_images']['type'][$key];
                    $error = $_FILES['property_images']['error'][$key];
                    
                    // Skip if there was an upload error
                    if ($error !== UPLOAD_ERR_OK) {
                        $failedImages++;
                        continue;
                    }
                    
                    // Check file size (limit to 5MB)
                    if ($size > 5000000) {
                        $failedImages++;
                        continue;
                    }
                    
                    // Get file extension and check if it's allowed
                    $imageFileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $failedImages++;
                        continue;
                    }
                    
                    // Generate unique filename
                    $filename = time() . '_' . $key . '_' . basename($name);
                    $target_file = $target_dir . $filename;
                    
                    // Convert to JPEG if not already
                    if ($imageFileType != 'jpg' && $imageFileType != 'jpeg') {
                        $image = null;
                        
                        if ($imageFileType == 'png') {
                            $image = imagecreatefrompng($tmp_name);
                        } else if ($imageFileType == 'gif') {
                            $image = imagecreatefromgif($tmp_name);
                        }
                        
                        if ($image) {
                            $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
                            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                            imagealphablending($bg, TRUE);
                            imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                            imagedestroy($image);
                            
                            // Change filename to jpg
                            $filename = str_replace('.' . $imageFileType, '.jpg', $filename);
                            $target_file = $target_dir . $filename;
                            
                            if (imagejpeg($bg, $target_file, 90)) {
                                imagedestroy($bg);
                                
                                // Insert into database
                                $img_stmt = $mysqli->prepare("INSERT INTO property_images (property_id, image_path, sort_order, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
                                $img_stmt->bind_param("isi", $property_id, $target_file, $next_sort_order);
                                
                                if ($img_stmt->execute()) {
                                    $uploadedImages++;
                                    $next_sort_order++;
                                } else {
                                    $failedImages++;
                                    if (file_exists($target_file)) {
                                        unlink($target_file);
                                    }
                                }
                            } else {
                                $failedImages++;
                            }
                        } else {
                            $failedImages++;
                        }
                    } else {
                        // Just move the uploaded file for JPG/JPEG
                        if (move_uploaded_file($tmp_name, $target_file)) {
                            // Insert into database
                            $img_stmt = $mysqli->prepare("INSERT INTO property_images (property_id, image_path, sort_order, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
                            $img_stmt->bind_param("isi", $property_id, $target_file, $next_sort_order);
                            
                            if ($img_stmt->execute()) {
                                $uploadedImages++;
                                $next_sort_order++;
                            } else {
                                $failedImages++;
                                if (file_exists($target_file)) {
                                    unlink($target_file);
                                }
                            }
                        } else {
                            $failedImages++;
                        }
                    }
                }
                
                if ($uploadedImages > 0) {
                    $success_message .= " Successfully uploaded $uploadedImages new image" . ($uploadedImages > 1 ? "s" : "") . ".";
                }
                
                if ($failedImages > 0) {
                    $error_message = "Failed to upload $failedImages image" . ($failedImages > 1 ? "s" : "") . ". Please check file types and sizes.";
                }
            }
            
            // Handle image deletions
            if (isset($_POST['delete_image']) && is_array($_POST['delete_image'])) {
                $deleted_count = 0;
                
                foreach ($_POST['delete_image'] as $image_id) {
                    // Get the image path first
                    $img_result = $mysqli->query("SELECT image_path FROM property_images WHERE id = $image_id AND property_id = $property_id");
                    if ($img_result && $img_row = $img_result->fetch_assoc()) {
                        // Delete the file
                        if (file_exists($img_row['image_path'])) {
                            unlink($img_row['image_path']);
                        }
                        
                        // Delete from database
                        $mysqli->query("DELETE FROM property_images WHERE id = $image_id AND property_id = $property_id");
                        $deleted_count++;
                    }
                }
                
                if ($deleted_count > 0) {
                    $success_message .= " Deleted $deleted_count image" . ($deleted_count > 1 ? "s" : "") . ".";
                }
            }
            
            // Refresh property data after update
            $result = $mysqli->query("SELECT * FROM properties WHERE id = $property_id");
            $property = $result->fetch_assoc();
            
            // Refresh property images
            $images_result = $mysqli->query("SELECT * FROM property_images WHERE property_id = $property_id ORDER BY sort_order");
            $property_images = [];
            if ($images_result) {
                while ($img = $images_result->fetch_assoc()) {
                    $property_images[] = $img;
                }
            }
        } else {
            $error_message = "Error updating property: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property | Agent Portal</title>
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

        /* Form Styles */
        .form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .form-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .form-body {
            padding: 1.5rem;
        }

        .form-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        @media (max-width: 768px) {
            .form-group.full-width {
                grid-column: span 1;
            }
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

        /* Property Preview */
        .property-preview {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .preview-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .preview-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .preview-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .preview-image {
            width: 100%;
            max-width: 400px;
            height: 250px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }

        .preview-details {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .preview-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .preview-label {
            font-weight: 500;
            color: var(--gray-600);
        }

        .preview-value {
            color: var(--gray-800);
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
        
        /* Image Gallery Styles */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .image-item {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        
        .image-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }
        
        .image-actions {
            position: absolute;
            top: 0;
            right: 0;
            padding: 0.25rem;
            display: flex;
            gap: 0.25rem;
        }
        
        .image-action {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.8);
            color: var(--gray-800);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }
        
        .image-action:hover {
            background-color: white;
            color: var(--danger);
        }
        
        .image-upload-container {
            border: 2px dashed var(--gray-300);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .image-upload-container:hover {
            border-color: var(--agent-primary);
            background-color: var(--gray-100);
        }
        
        .image-upload-icon {
            font-size: 2rem;
            color: var(--gray-400);
            margin-bottom: 0.5rem;
        }
        
        .image-upload-text {
            color: var(--gray-600);
            font-size: 0.875rem;
        }
        
        .image-preview-container {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .image-preview-item {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        
        .image-preview-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }
        
        .image-preview-remove {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.8);
            color: var(--danger);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }
        
        .image-preview-remove:hover {
            background-color: var(--danger);
            color: white;
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
                    <span>REMS</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-nav-item">
                    <a href="dashboard.php" class="sidebar-nav-link active">
                        <i class="fas fa-home sidebar-nav-icon"></i>
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
                    <a href="payment_report.php" class="sidebar-nav-link">
                        <i class="fas fa-money-bill-wave sidebar-nav-icon"></i>
                        Payments
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="users.php" class="sidebar-nav-link">
                        <i class="fas fa-users sidebar-nav-icon"></i>
                        Tenants
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="maintenance.php" class="sidebar-nav-link">
                        <i class="fas fa-tools sidebar-nav-icon"></i>
                        Maintenance
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="report.php" class="sidebar-nav-link">
                        <i class="fas fa-chart-bar sidebar-nav-icon"></i>
                        Reports
                    </a>
                </div>

                <div class="sidebar-nav-item">
                    <a href="../../index.php" class="sidebar-nav-link">
                        <i class="fas fa-sign-out-alt sidebar-nav-icon"></i>
                        Logout
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Edit Property</h1>
                <div class="header-actions">
                    <a href="properties.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Properties
                    </a>
                </div>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <div class="form-header">
                    <h2 class="form-title">Property Information</h2>
                </div>
                <form method="post" id="propertyForm" enctype="multipart/form-data">
                    <div class="form-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="title" class="form-label">Property Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($property['city']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms" min="0" value="<?php echo htmlspecialchars($property['bedrooms']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0" step="0.5" value="<?php echo htmlspecialchars($property['bathrooms']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="square_footage" class="form-label">Square Footage</label>
                                <input type="number" class="form-control" id="square_footage" name="square_footage" min="0" value="<?php echo htmlspecialchars($property['square_footage']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="monthly_rent" class="form-label">Monthly Rent ($)</label>
                                <input type="number" class="form-control" id="monthly_rent" name="monthly_rent" min="0" step="0.01" value="<?php echo htmlspecialchars($property['monthly_rent']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="available" <?php if($property['status'] == 'available') echo 'selected'; ?>>Available</option>
                                    <option value="occupied" <?php if($property['status'] == 'occupied') echo 'selected'; ?>>Occupied</option>
                                    <option value="maintenance" <?php if($property['status'] == 'maintenance') echo 'selected'; ?>>Maintenance</option>
                                </select>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($property['description']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($property['address']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($property['state']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="zip_code" class="form-label">Zip Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($property['zip_code']); ?>" required>
                            </div>
                        </div>
                        
                        <!-- Featured Image Section -->
                        <div class="form-group full-width">
                            <label class="form-label">Featured Image</label>
                            <?php if (!empty($property['featured_image'])): ?>
                                <div class="mb-3">
                                    <img src="<?php echo htmlspecialchars($property['featured_image']); ?>" alt="Featured Image" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: var(--border-radius);">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/jpeg,image/png,image/gif">
                            <div class="form-text">Upload a new image to replace the current one. Maximum file size: 5MB. Supported formats: JPG, JPEG, PNG, GIF.</div>
                        </div>
                        
                        <!-- Property Images Section -->
                        <div class="form-group full-width">
                            <label class="form-label">Property Images</label>
                            
                            <!-- Existing Images Gallery -->
                            <?php if (!empty($property_images)): ?>
                                <div class="image-gallery">
                                    <?php foreach ($property_images as $img): ?>
                                        <div class="image-item">
                                            <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Property Image">
                                            <div class="image-actions">
                                                <label class="image-action" title="Delete Image">
                                                    <input type="checkbox" name="delete_image[]" value="<?php echo $img['id']; ?>" style="display: none;">
                                                    <i class="fas fa-trash"></i>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-text mb-3">Check the trash icon to mark images for deletion.</div>
                            <?php else: ?>
                                <p class="text-muted mb-3">No additional images for this property.</p>
                            <?php endif; ?>
                            
                            <!-- Upload New Images -->
                            <div class="image-upload-container" id="imageUploadContainer">
                                <div class="image-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="image-upload-text">
                                    <p>Drag & drop images here or click to browse</p>
                                    <p class="form-text">Maximum 10 images. 5MB per image. Supported formats: JPG, JPEG, PNG, GIF</p>
                                </div>
                                <input type="file" id="property_images" name="property_images[]" multiple accept="image/jpeg,image/png,image/gif" style="display: none;">
                            </div>
                            
                            <!-- Image Preview Container -->
                            <div class="image-preview-container" id="imagePreviewContainer"></div>
                        </div>
                    </div>
                    <div class="form-footer">
                        <a href="properties.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <div class="property-preview">
                <div class="preview-header">
                    <h2 class="preview-title">Property Preview</h2>
                </div>
                <div class="preview-body">
                    <img id="previewImage" src="<?php echo htmlspecialchars($property['featured_image'] ?: '/placeholder.svg?height=250&width=400'); ?>" 
                         alt="<?php echo htmlspecialchars($property['title']); ?>" 
                         class="preview-image"
                         onerror="this.src='/placeholder.svg?height=250&width=400'">
                    
                    <div class="preview-details">
                        <div class="preview-item">
                            <i class="fas fa-tag" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Title:</span>
                            <span id="previewTitle" class="preview-value"><?php echo htmlspecialchars($property['title']); ?></span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-map-marker-alt" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">City:</span>
                            <span id="previewCity" class="preview-value"><?php echo htmlspecialchars($property['city']); ?></span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-bed" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Bedrooms:</span>
                            <span id="previewBedrooms" class="preview-value"><?php echo htmlspecialchars($property['bedrooms']); ?></span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-bath" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Bathrooms:</span>
                            <span id="previewBathrooms" class="preview-value"><?php echo htmlspecialchars($property['bathrooms']); ?></span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-ruler-combined" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Area:</span>
                            <span id="previewArea" class="preview-value"><?php echo number_format($property['square_footage']); ?> sqft</span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-dollar-sign" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Rent:</span>
                            <span id="previewRent" class="preview-value">$<?php echo number_format($property['monthly_rent'], 2); ?>/month</span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-info-circle" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Status:</span>
                            <span id="previewStatus" class="preview-value"><?php echo ucfirst($property['status']); ?></span>
                        </div>
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

        // Live preview functionality
        const form = document.getElementById('propertyForm');
        const formElements = form.elements;
        
        for (let i = 0; i < formElements.length; i++) {
            const element = formElements[i];
            if (element.type !== 'submit' && element.type !== 'button') {
                element.addEventListener('input', updatePreview);
            }
        }
        
        function updatePreview() {
            // Update preview elements
            document.getElementById('previewTitle').textContent = document.getElementById('title').value;
            document.getElementById('previewCity').textContent = document.getElementById('city').value;
            document.getElementById('previewBedrooms').textContent = document.getElementById('bedrooms').value;
            document.getElementById('previewBathrooms').textContent = document.getElementById('bathrooms').value;
            document.getElementById('previewArea').textContent = Number(document.getElementById('square_footage').value).toLocaleString() + ' sqft';
            document.getElementById('previewRent').textContent = '$' + Number(document.getElementById('monthly_rent').value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '/month';
            
            const statusSelect = document.getElementById('status');
            document.getElementById('previewStatus').textContent = statusSelect.options[statusSelect.selectedIndex].text;
        }
        
        // Image upload functionality
        const imageUploadContainer = document.getElementById('imageUploadContainer');
        const propertyImagesInput = document.getElementById('property_images');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        
        imageUploadContainer.addEventListener('click', function() {
            propertyImagesInput.click();
        });
        
        imageUploadContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--agent-primary)';
            this.style.backgroundColor = 'var(--gray-100)';
        });
        
        imageUploadContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--gray-300)';
            this.style.backgroundColor = '';
        });
        
        imageUploadContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--gray-300)';
            this.style.backgroundColor = '';
            
            if (e.dataTransfer.files.length > 0) {
                propertyImagesInput.files = e.dataTransfer.files;
                handleImagePreview();
            }
        });
        
        propertyImagesInput.addEventListener('change', handleImagePreview);
        
        function handleImagePreview() {
            imagePreviewContainer.innerHTML = '';
            
            if (propertyImagesInput.files.length > 10) {
                alert('You can only upload up to 10 images at once.');
                propertyImagesInput.value = '';
                return;
            }
            
            for (let i = 0; i < propertyImagesInput.files.length; i++) {
                const file = propertyImagesInput.files[i];
                
                // Validate file size
                if (file.size > 5 * 1024 * 1024) {
                    alert(`File "${file.name}" exceeds the 5MB size limit.`);
                    continue;
                }
                
                // Validate file type
                const fileType = file.type.toLowerCase();
                if (fileType !== 'image/jpeg' && fileType !== 'image/jpg' && fileType !== 'image/png' && fileType !== 'image/gif') {
                    alert(`File "${file.name}" is not a supported image format.`);
                    continue;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'image-preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Image Preview';
                    
                    const removeBtn = document.createElement('div');
                    removeBtn.className = 'image-preview-remove';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.dataset.index = i;
                    
                    removeBtn.addEventListener('click', function() {
                        previewItem.remove();
                        
                        // Create a new FileList without the removed file
                        const dt = new DataTransfer();
                        const files = propertyImagesInput.files;
                        
                        for (let j = 0; j < files.length; j++) {
                            if (j !== parseInt(this.dataset.index)) {
                                dt.items.add(files[j]);
                            }
                        }
                        
                        propertyImagesInput.files = dt.files;
                    });
                    
                    previewItem.appendChild(img);
                    previewItem.appendChild(removeBtn);
                    imagePreviewContainer.appendChild(previewItem);
                };
                
                reader.readAsDataURL(file);
            }
        }
        
        // Handle image deletion checkboxes
        const deleteCheckboxes = document.querySelectorAll('input[name="delete_image[]"]');
        deleteCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const imageItem = this.closest('.image-item');
                if (this.checked) {
                    imageItem.style.opacity = '0.5';
                } else {
                    imageItem.style.opacity = '1';
                }
            });
        });
        
        // Preview featured image when selected
        const featuredImageInput = document.getElementById('featured_image');
        featuredImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>