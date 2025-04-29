<?php
// Include the database configuration
require_once 'config.php';

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and fetch form inputs
    $tenant_id = intval($_POST['tenant_id']);
    $property_id = intval($_POST['property_id']);
    $title = $mysqli->real_escape_string($_POST['title']);
    $description = $mysqli->real_escape_string($_POST['description']);
    $urgency = $mysqli->real_escape_string($_POST['urgency']);

    // Insert into database
    $sql = "INSERT INTO maintenance_requests (tenant_id, property_id, title, description, urgency)
            VALUES ($tenant_id, $property_id, '$title', '$description', '$urgency')";

    if ($mysqli->query($sql) === TRUE) {
        $success_message = "Maintenance request submitted successfully!";
    } else {
        $error_message = "Error: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Agent | Tenant Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
         /* Base Styles */
         :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
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
            --tenant-primary: #8b5cf6;
            --tenant-primary-dark: #7c3aed;
            --tenant-primary-light: #c4b5fd;
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

        .tenant-layout {
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
            display: flex;
            flex-direction: column;
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
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
            flex: 1;
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
        }

        .sidebar-nav-link:hover {
            background-color: var(--gray-100);
            color: var(--gray-800);
        }

        .sidebar-nav-link.active {
            background-color: var(--tenant-primary-light);
            color: var(--tenant-primary-dark);
            font-weight: 500;
        }

        .sidebar-nav-icon {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }

        /* Profile Section */
        .sidebar-profile {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            background-color: var(--gray-50);
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: var(--tenant-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .profile-details {
            flex: 1;
        }

        .profile-name {
            font-weight: 600;
            color: var(--gray-800);
        }

        .profile-role {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        /* Agent Info */
        .agent-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }
        
        .agent-info:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .agent-avatar {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            background-color: var(--tenant-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 600;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2);
        }

        .agent-details {
            flex: 1;
        }

        .agent-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .agent-role {
            color: var(--gray-500);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .agent-contact {
            display: flex;
            gap: 1.5rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            transition: color 0.2s ease;
        }
        
        .contact-item:hover {
            color: var(--tenant-primary);
        }
        
        .contact-item i {
            color: var(--tenant-primary);
        }

        /* Contact Form */
        .contact-form-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .contact-form-header {
            background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-dark));
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .contact-form-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .contact-form-subtitle {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .contact-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 1rem;
            color: var(--gray-800);
            transition: all 0.3s ease;
            background-color: var(--gray-50);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--tenant-primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            background-color: white;
        }

        .form-textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 1rem;
            color: var(--gray-800);
            transition: all 0.3s ease;
            background-color: var(--gray-50);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px;
        }
        
        .form-select:focus {
            outline: none;
            border-color: var(--tenant-primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            background-color: white;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-dark));
            color: white;
            box-shadow: 0 4px 6px rgba(139, 92, 246, 0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--tenant-primary-dark), var(--tenant-primary-dark));
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(139, 92, 246, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        /* Success Message */
        .success-message {
            background-color: #ecfdf5;
            color: #065f46;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: 4px solid var(--success);
        }
        
        /* Error Message */
        .error-message {
            background-color: #fef2f2;
            color: #991b1b;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: 4px solid var(--danger);
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                position: static;
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--gray-200);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
            
            .agent-info {
                flex-direction: column;
                text-align: center;
            }
            
            .agent-contact {
                flex-direction: column;
                align-items: center;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="tenant-layout">
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
                    <a href="dashboard.php" class="sidebar-nav-link">
                        <i class="fas fa-home sidebar-nav-icon"></i>
                        Dashboard
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="my-leases.php" class="sidebar-nav-link">
                        <i class="fas fa-file-contract sidebar-nav-icon"></i>
                        My Leases
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="view-properties.php" class="sidebar-nav-link">
                        <i class="fas fa-building sidebar-nav-icon"></i>
                        View Properties
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="contact-agent.php" class="sidebar-nav-link active">
                        <i class="fas fa-envelope sidebar-nav-icon"></i>
                        Contact Agent
                    </a>
                </div>
            </nav>
            <!-- Profile Section -->
            <div class="sidebar-profile">
                <div class="profile-info">
                    <div class="profile-avatar">MC</div>
                    <div class="profile-details">
                        <div class="profile-name">Michael Chen</div>
                        <div class="profile-role">Tenant</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Contact Agent</h1>
            </div>

            <!-- Agent Info -->
            <div class="agent-info">
                <div class="agent-avatar">MJ</div>
                <div class="agent-details">
                    <h2 class="agent-name">Mary Jane</h2>
                    <div class="agent-role">Property Agent</div>
                    <div class="agent-contact">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>(555) 123-4567</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>mary.jane@rems.com</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-container">
                <div class="contact-form-header">
                    <h2 class="contact-form-title">Maintenance Request</h2>
                    <p class="contact-form-subtitle">Submit your maintenance request and we'll address it promptly</p>
                </div>
                
                <div class="contact-form">
                    <?php if (isset($success_message)): ?>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo $success_message; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo $error_message; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="tenant_id" class="form-label">Tenant ID</label>
                            <input type="number" id="tenant_id" name="tenant_id" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="property_id" class="form-label">Property ID</label>
                            <input type="number" id="property_id" name="property_id" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" id="title" name="title" class="form-control" maxlength="100" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control form-textarea" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="urgency" class="form-label">Urgency</label>
                            <select id="urgency" name="urgency" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>