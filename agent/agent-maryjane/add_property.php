<?php
require_once '../../tenant/config.php'; // database connection

// Define landlord_id manually here or get from session later
$landlord_id = 2;

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $city = $_POST['city'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $square_footage = $_POST['square_footage'];
    $monthly_rent = $_POST['monthly_rent'];
    $status = $_POST['status'];
    $featured_image = $_POST['featured_image']; // Just the URL path for now

    $sql = "INSERT INTO properties (landlord_id, title, city, bedrooms, bathrooms, square_footage, monthly_rent, status, featured_image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("issiiidss", $landlord_id, $title, $city, $bedrooms, $bathrooms, $square_footage, $monthly_rent, $status, $featured_image);

    if ($stmt->execute()) {
        // If you want to show a success message instead of redirecting immediately
        $success_message = "Property added successfully!";
        // Uncomment the next line if you want to redirect immediately
        // header("Location: properties.php"); exit();
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property | Agent Portal</title>
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
            background-color: var(--agent-primary-light);
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--agent-primary-dark);
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
            background-color: var(--gray-50);
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
            display: flex;
            align-items: center;
            gap: 0.75rem;
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

        .alert-icon {
            font-size: 1.25rem;
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
            background-color: var(--gray-50);
        }

        .preview-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            border: 1px solid var(--gray-200);
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

        /* Steps indicator */
        .steps {
            display: flex;
            margin-bottom: 2rem;
            overflow-x: auto;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 1rem;
            position: relative;
            min-width: 120px;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 1.5rem;
            right: -1rem;
            height: 2px;
            width: 2rem;
            background-color: var(--gray-300);
        }

        .step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: var(--gray-200);
            color: var(--gray-600);
            margin: 0 auto 0.5rem;
            font-weight: 600;
        }

        .step.active .step-number {
            background-color: var(--agent-primary);
            color: white;
        }

        .step-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-600);
        }

        .step.active .step-title {
            color: var(--agent-primary);
            font-weight: 600;
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
                <h1 class="page-title">Add New Property</h1>
                <div class="header-actions">
                    <a href="properties.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Properties
                    </a>
                </div>
            </div>

            <!-- Steps indicator -->
            <div class="steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-title">Property Details</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-title">Images & Media</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-title">Amenities</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-title">Publish</div>
                </div>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <strong>Success!</strong> <?php echo $success_message; ?>
                        <div>
                            <a href="properties.php" class="btn btn-primary" style="margin-top: 0.5rem;">
                                <i class="fas fa-list"></i> View All Properties
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div>
                        <strong>Error!</strong> <?php echo $error_message; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <div class="form-header">
                    <h2 class="form-title"><i class="fas fa-plus-circle"></i> Add New Property</h2>
                </div>
                <form method="post" id="propertyForm">
                    <div class="form-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="title" class="form-label">Property Title</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Modern Apartment in Downtown" required>
                                <div class="form-text">Enter a descriptive title for your property</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" placeholder="e.g. New York" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms" min="0" placeholder="e.g. 2" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0" step="0.5" placeholder="e.g. 1.5" required>
                                <div class="form-text">Use 0.5 for half bathrooms</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="square_footage" class="form-label">Square Footage</label>
                                <input type="number" class="form-control" id="square_footage" name="square_footage" min="0" placeholder="e.g. 1200" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="monthly_rent" class="form-label">Monthly Rent ($)</label>
                                <input type="number" class="form-control" id="monthly_rent" name="monthly_rent" min="0" step="0.01" placeholder="e.g. 1500.00" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="" disabled selected>Select status</option>
                                    <option value="available">Available</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="featured_image" class="form-label">Featured Image URL</label>
                                <input type="url" class="form-control" id="featured_image" name="featured_image" placeholder="https://example.com/image.jpg">
                                <div class="form-text">Enter a valid URL for the property image. Leave empty to use a placeholder.</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-footer">
                        <a href="properties.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Property
                        </button>
                    </div>
                </form>
            </div>

            <div class="property-preview">
                <div class="preview-header">
                    <h2 class="preview-title"><i class="fas fa-eye"></i> Property Preview</h2>
                </div>
                <div class="preview-body">
                    <img id="previewImage" src="/placeholder.svg?height=250&width=400" 
                         alt="Property Preview" 
                         class="preview-image">
                    
                    <div class="preview-details">
                        <div class="preview-item">
                            <i class="fas fa-tag" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Title:</span>
                            <span id="previewTitle" class="preview-value">Property Title</span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-map-marker-alt" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">City:</span>
                            <span id="previewCity" class="preview-value">City</span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-bed" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Bedrooms:</span>
                            <span id="previewBedrooms" class="preview-value">0</span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-bath" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Bathrooms:</span>
                            <span id="previewBathrooms" class="preview-value">0</span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-ruler-combined" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Area:</span>
                            <span id="previewArea" class="preview-value">0 sqft</span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-dollar-sign" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Rent:</span>
                            <span id="previewRent" class="preview-value">$0.00/month</span>
                        </div>
                        
                        <div class="preview-item">
                            <i class="fas fa-info-circle" style="color: var(--agent-primary);"></i>
                            <span class="preview-label">Status:</span>
                            <span id="previewStatus" class="preview-value">Not set</span>
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
            const title = document.getElementById('title').value || 'Property Title';
            const city = document.getElementById('city').value || 'City';
            const bedrooms = document.getElementById('bedrooms').value || '0';
            const bathrooms = document.getElementById('bathrooms').value || '0';
            const squareFootage = document.getElementById('square_footage').value || '0';
            const monthlyRent = document.getElementById('monthly_rent').value || '0';
            
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewCity').textContent = city;
            document.getElementById('previewBedrooms').textContent = bedrooms;
            document.getElementById('previewBathrooms').textContent = bathrooms;
            document.getElementById('previewArea').textContent = Number(squareFootage).toLocaleString() + ' sqft';
            document.getElementById('previewRent').textContent = '$' + Number(monthlyRent).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '/month';
            
            const statusSelect = document.getElementById('status');
            if (statusSelect.selectedIndex > 0) {
                document.getElementById('previewStatus').textContent = statusSelect.options[statusSelect.selectedIndex].text;
            } else {
                document.getElementById('previewStatus').textContent = 'Not set';
            }
            
            const imageUrl = document.getElementById('featured_image').value;
            if (imageUrl) {
                document.getElementById('previewImage').src = imageUrl;
            } else {
                document.getElementById('previewImage').src = '/placeholder.svg?height=250&width=400';
            }
        }
    </script>
</body>
</html>
