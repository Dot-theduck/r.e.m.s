<?php
require_once '../tenant/config.php';

// Initialize variables
$property = [];
$errors = [];
$success_message = '';

// Get property ID from URL
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch property data
if ($property_id > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    $stmt->close();
    
    if (!$property) {
        header("Location: properties.php");
        exit();
    }
} else {
    header("Location: properties.php");
    exit();
}

// Get landlords for dropdown
$landlords_result = $mysqli->query("SELECT id, first_name, last_name FROM users WHERE user_type = 'landlord' ORDER BY last_name, first_name");
$landlords = [];
if ($landlords_result) {
    while ($row = $landlords_result->fetch_assoc()) {
        $landlords[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    // Get form data
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
    $current_image = $property['featured_image'];
    $new_image = $current_image;

    // Handle file upload if new image is provided
    if (!empty($_FILES['featured_image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $filename = time() . '_' . basename($_FILES["featured_image"]["name"]);
        $target_file = $target_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Validate image
        $check = getimagesize($_FILES["featured_image"]["tmp_name"]);
        if ($check === false) {
            $errors[] = "File is not an image.";
        } elseif ($_FILES["featured_image"]["size"] > 2000000) {
            $errors[] = "Sorry, your file is too large (max 2MB).";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif (move_uploaded_file($_FILES["featured_image"]["tmp_name"], $target_file)) {
            // Delete old image if it exists
            if (!empty($current_image) && file_exists($current_image)) {
                unlink($current_image);
            }
            $new_image = "uploads/" . $filename;
        } else {
            $errors[] = "Sorry, there was an error uploading your file.";
        }
    }

    // Only proceed if no errors
    if (empty($errors)) {
        $stmt = $mysqli->prepare("UPDATE properties SET 
            landlord_id = ?, 
            title = ?, 
            description = ?, 
            address = ?, 
            city = ?, 
            state = ?, 
            zip_code = ?, 
            bedrooms = ?, 
            bathrooms = ?, 
            square_footage = ?, 
            monthly_rent = ?, 
            status = ?, 
            featured_image = ?
            WHERE id = ?");
        
        $stmt->bind_param("issssssididssi",
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
            $new_image,
            $property_id
        );
        
        if ($stmt->execute()) {
            $success_message = "Property updated successfully!";
            // Refresh property data
            $stmt = $mysqli->prepare("SELECT * FROM properties WHERE id = ?");
            $stmt->bind_param("i", $property_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $property = $result->fetch_assoc();
            $stmt->close();
        } else {
            $errors[] = "Error updating property: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property | REMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reuse the same styles from properties.php */
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

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

        .current-image {
            max-width: 200px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-200);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <h1 class="page-title">Edit Property</h1>
        <div class="header-actions">
            <a href="properties.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Properties
            </a>
        </div>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
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

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-edit"></i> Edit Property Details
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
                                <option value="<?php echo $id; ?>" <?php echo ($id == $property['landlord_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" 
                               value="<?php echo htmlspecialchars($property['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" id="address" class="form-control" 
                               value="<?php echo htmlspecialchars($property['address']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="city" class="form-label">City</label>
                        <input type="text" name="city" id="city" class="form-control" 
                               value="<?php echo htmlspecialchars($property['city']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="state" class="form-label">State</label>
                        <input type="text" name="state" id="state" class="form-control" 
                               value="<?php echo htmlspecialchars($property['state']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="zip_code" class="form-label">ZIP Code</label>
                        <input type="text" name="zip_code" id="zip_code" class="form-control" 
                               value="<?php echo htmlspecialchars($property['zip_code']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bedrooms" class="form-label">Bedrooms</label>
                        <input type="number" name="bedrooms" id="bedrooms" class="form-control" 
                               value="<?php echo htmlspecialchars($property['bedrooms']); ?>" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bathrooms" class="form-label">Bathrooms</label>
                        <input type="number" name="bathrooms" id="bathrooms" class="form-control" 
                               value="<?php echo htmlspecialchars($property['bathrooms']); ?>" min="0" step="0.5" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="square_footage" class="form-label">Square Footage</label>
                        <input type="number" name="square_footage" id="square_footage" class="form-control" 
                               value="<?php echo htmlspecialchars($property['square_footage']); ?>" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="monthly_rent" class="form-label">Monthly Rent ($)</label>
                        <input type="number" name="monthly_rent" id="monthly_rent" class="form-control" 
                               value="<?php echo htmlspecialchars($property['monthly_rent']); ?>" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="available" <?php echo ($property['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                            <option value="occupied" <?php echo ($property['status'] == 'occupied') ? 'selected' : ''; ?>>Occupied</option>
                            <option value="maintenance" <?php echo ($property['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*">
                        <small class="form-text">Max size: 2MB (JPG, PNG, GIF)</small>
                        
                        <?php if (!empty($property['featured_image'])): ?>
                            <div style="margin-top: 10px;">
                                <p class="form-text">Current Image:</p>
                                <img src="<?php echo htmlspecialchars($property['featured_image']); ?>" class="current-image" id="currentImage">
                                <div style="margin-top: 5px;">
                                    <label class="form-text">
                                        <input type="checkbox" name="remove_image" id="remove_image"> Remove current image
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div id="imagePreview" style="margin-top: 10px; display: none;">
                            <p class="form-text">New Image Preview:</p>
                            <img id="previewImage" src="#" alt="Preview" class="current-image">
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="5"><?php echo htmlspecialchars($property['description']); ?></textarea>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                    <a href="properties.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" name="update" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Property
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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

    // Remove image checkbox functionality
    const removeImageCheckbox = document.getElementById('remove_image');
    if (removeImageCheckbox) {
        removeImageCheckbox.addEventListener('change', function() {
            const currentImage = document.getElementById('currentImage');
            if (this.checked) {
                currentImage.style.opacity = '0.5';
                currentImage.style.border = '2px solid var(--danger)';
            } else {
                currentImage.style.opacity = '1';
                currentImage.style.border = '1px solid var(--gray-200)';
            }
        });
    }

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