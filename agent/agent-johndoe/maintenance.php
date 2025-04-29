<?php
require_once '../../tenant/config.php'; // adjust path if needed

$landlord_id = 2;

// Initialize filter variables
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_property = isset($_GET['property']) ? $_GET['property'] : '';
$filter_priority = isset($_GET['priority']) ? $_GET['priority'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Base SQL query
$sql = "SELECT 
            maintenance_requests.id,
            maintenance_requests.title,
            maintenance_requests.description,
            maintenance_requests.status,
            maintenance_requests.priority,
            maintenance_requests.created_at,
            maintenance_requests.updated_at,
            properties.id AS property_id,
            properties.title AS property_title,
            properties.address,
            properties.city,
            properties.state,
            properties.zip_code
        FROM maintenance_requests
        INNER JOIN properties ON maintenance_requests.property_id = properties.id
        WHERE properties.landlord_id = ?";

// Add filters to the query if they exist
$params = array($landlord_id);
$types = "i";

if (!empty($filter_status)) {
    $sql .= " AND maintenance_requests.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_property)) {
    $sql .= " AND properties.id = ?";
    $params[] = $filter_property;
    $types .= "i";
}

if (!empty($filter_priority)) {
    $sql .= " AND maintenance_requests.priority = ?";
    $params[] = $filter_priority;
    $types .= "s";
}

if (!empty($filter_date_from)) {
    $sql .= " AND maintenance_requests.created_at >= ?";
    $params[] = $filter_date_from . ' 00:00:00';
    $types .= "s";
}

if (!empty($filter_date_to)) {
    $sql .= " AND maintenance_requests.created_at <= ?";
    $params[] = $filter_date_to . ' 23:59:59';
    $types .= "s";
}

// Add order by clause
$sql .= " ORDER BY 
            CASE 
                WHEN maintenance_requests.priority = 'high' THEN 1
                WHEN maintenance_requests.priority = 'medium' THEN 2
                WHEN maintenance_requests.priority = 'low' THEN 3
            END,
            CASE 
                WHEN maintenance_requests.status = 'pending' THEN 1
                WHEN maintenance_requests.status = 'in_progress' THEN 2
                WHEN maintenance_requests.status = 'completed' THEN 3
            END,
            maintenance_requests.created_at DESC";

// Get properties for filter dropdown
$properties_sql = "SELECT id, title FROM properties WHERE landlord_id = ? ORDER BY title";
$properties = array();

if ($properties_stmt = $mysqli->prepare($properties_sql)) {
    $properties_stmt->bind_param("i", $landlord_id);
    $properties_stmt->execute();
    $properties_result = $properties_stmt->get_result();
    while ($property = $properties_result->fetch_assoc()) {
        $properties[$property['id']] = $property['title'];
    }
    $properties_stmt->close();
}

// Get maintenance request counts by status
$status_counts = array(
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'total' => 0
);

$count_sql = "SELECT status, COUNT(*) as count 
              FROM maintenance_requests 
              INNER JOIN properties ON maintenance_requests.property_id = properties.id 
              WHERE properties.landlord_id = ? 
              GROUP BY status";

if ($count_stmt = $mysqli->prepare($count_sql)) {
    $count_stmt->bind_param("i", $landlord_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    
    while ($row = $count_result->fetch_assoc()) {
        $status_counts[$row['status']] = $row['count'];
        $status_counts['total'] += $row['count'];
    }
    
    $count_stmt->close();
}

// Execute the main query
$maintenance_requests = array();

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $maintenance_requests[] = $row;
        }
        
    } else {
        $error_message = "Error executing query: " . $stmt->error;
    }

    $stmt->close();
} else {
    $error_message = "Prepare failed: " . $mysqli->error;
}

// Handle status update if form is submitted
$update_message = '';
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['new_status'];
    
    $update_sql = "UPDATE maintenance_requests SET status = ?, updated_at = NOW() WHERE id = ?";
    
    if ($update_stmt = $mysqli->prepare($update_sql)) {
        $update_stmt->bind_param("si", $new_status, $request_id);
        
        if ($update_stmt->execute()) {
            $update_message = "Maintenance request status updated successfully.";
            
            // Refresh the page to show updated data
            header("Location: maintenance.php" . (empty($_SERVER['QUERY_STRING']) ? "" : "?" . $_SERVER['QUERY_STRING']));
            exit;
        } else {
            $update_error = "Error updating status: " . $update_stmt->error;
        }
        
        $update_stmt->close();
    } else {
        $update_error = "Prepare failed: " . $mysqli->error;
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Requests | REMS</title>
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

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: var(--success);
            opacity: 0.9;
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: var(--warning);
            opacity: 0.9;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: var(--danger);
            opacity: 0.9;
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.primary {
            background-color: var(--agent-primary-light);
            color: var(--agent-primary-dark);
        }

        .stat-icon.success {
            background-color: var(--success-light);
            color: var(--success);
        }

        .stat-icon.warning {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .stat-icon.danger {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        /* Filter Card */
        .filter-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .filter-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .filter-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-body {
            padding: 1.5rem;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.25rem;
        }

        .filter-group {
            margin-bottom: 0.5rem;
        }

        .filter-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .filter-control {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            color: var(--gray-800);
            background-color: white;
            background-clip: padding-box;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .filter-control:focus {
            border-color: var(--agent-primary);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        /* Table Styles */
        .table-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table th {
            background-color: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: var(--gray-50);
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-badge.completed {
            background-color: var(--success-light);
            color: var(--success);
        }

        .status-badge.pending {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .status-badge.in_progress {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        /* Priority Badge */
        .priority-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .priority-badge.high {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .priority-badge.medium {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .priority-badge.low {
            background-color: var(--success-light);
            color: var(--success);
        }

        /* Empty State */
        .empty-state {
            padding: 3rem;
            text-align: center;
        }

        .empty-icon {
            font-size: 3rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .empty-description {
            color: var(--gray-600);
            max-width: 30rem;
            margin: 0 auto 1.5rem;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                grid-template-columns: 1fr;
            }
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }

        .pagination-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--border-radius);
            background-color: white;
            color: var(--gray-700);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid var(--gray-200);
        }

        .pagination-item:hover {
            background-color: var(--gray-100);
        }

        .pagination-item.active {
            background-color: var(--agent-primary);
            color: white;
            border-color: var(--agent-primary);
        }

        .pagination-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
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

        /* Modal Styles */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-backdrop.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }

        .modal-backdrop.active .modal {
            transform: translateY(0);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--gray-500);
            font-size: 1.25rem;
            cursor: pointer;
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: var(--gray-800);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
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

        /* Description Truncation */
        .truncate-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
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
                <h1 class="page-title">Maintenance Requests</h1>
                <div class="header-actions">
                    <a href="#" class="btn btn-primary" onclick="openNewRequestModal()">
                        <i class="fas fa-plus"></i> New Request
                    </a>
                </div>
            </div>

            <?php if (!empty($update_message)): ?>
                <div class="alert alert-success">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div><?php echo $update_message; ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($update_error)): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div><?php echo $update_error; ?></div>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $status_counts['total']; ?></div>
                        <div class="stat-label">Total Requests</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $status_counts['pending']; ?></div>
                        <div class="stat-label">Pending Requests</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $status_counts['in_progress']; ?></div>
                        <div class="stat-label">In Progress</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $status_counts['completed']; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="filter-card">
                <div class="filter-header">
                    <h2 class="filter-title">
                        <i class="fas fa-filter"></i> Filter Requests
                    </h2>
                </div>
                <div class="filter-body">
                    <form action="" method="get" class="filter-form">
                        <div class="filter-group">
                            <label for="status" class="filter-label">Status</label>
                            <select name="status" id="status" class="filter-control">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php if ($filter_status === 'pending') echo 'selected'; ?>>Pending</option>
                                <option value="in_progress" <?php if ($filter_status === 'in_progress') echo 'selected'; ?>>In Progress</option>
                                <option value="completed" <?php if ($filter_status === 'completed') echo 'selected'; ?>>Completed</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="property" class="filter-label">Property</label>
                            <select name="property" id="property" class="filter-control">
                                <option value="">All Properties</option>
                                <?php foreach ($properties as $id => $title): ?>
                                    <option value="<?php echo $id; ?>" <?php if ($filter_property == $id) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="priority" class="filter-label">Priority</label>
                            <select name="priority" id="priority" class="filter-control">
                                <option value="">All Priorities</option>
                                <option value="high" <?php if ($filter_priority === 'high') echo 'selected'; ?>>High</option>
                                <option value="medium" <?php if ($filter_priority === 'medium') echo 'selected'; ?>>Medium</option>
                                <option value="low" <?php if ($filter_priority === 'low') echo 'selected'; ?>>Low</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="date_from" class="filter-label">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="filter-control" value="<?php echo htmlspecialchars($filter_date_from); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="date_to" class="filter-label">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="filter-control" value="<?php echo htmlspecialchars($filter_date_to); ?>">
                        </div>
                        
                        <div class="filter-actions">
                            <a href="maintenance.php" class="btn btn-outline">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <div class="table-header">
                    <h2 class="table-title">
                        <i class="fas fa-list"></i> Maintenance Requests
                    </h2>
                </div>
                
                <?php if (count($maintenance_requests) > 0): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Property</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($maintenance_requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['id']); ?></td>
                                        <td><?php echo htmlspecialchars($request['property_title']); ?></td>
                                        <td><?php echo htmlspecialchars($request['title']); ?></td>
                                        <td>
                                            <div class="truncate-text">
                                                <?php echo htmlspecialchars($request['description']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                                $priority = $request['priority'];
                                                $priority_class = '';
                                                
                                                if ($priority == 'high') {
                                                    $priority_class = 'high';
                                                } elseif ($priority == 'medium') {
                                                    $priority_class = 'medium';
                                                } elseif ($priority == 'low') {
                                                    $priority_class = 'low';
                                                }
                                            ?>
                                            <span class="priority-badge <?php echo $priority_class; ?>">
                                                <?php echo ucfirst(htmlspecialchars($priority)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                                $status = $request['status'];
                                                $status_class = '';
                                                
                                                if ($status == 'completed') {
                                                    $status_class = 'completed';
                                                } elseif ($status == 'pending') {
                                                    $status_class = 'pending';
                                                } elseif ($status == 'in_progress') {
                                                    $status_class = 'in_progress';
                                                }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($status))); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($request['updated_at'])); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <button class="btn btn-outline btn-sm" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <?php if ($request['status'] !== 'completed'): ?>
                                                    <button class="btn btn-primary btn-sm" onclick="updateStatus(<?php echo $request['id']; ?>, '<?php echo $request['status']; ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination">
                        <a href="#" class="pagination-item disabled">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="#" class="pagination-item active">1</a>
                        <a href="#" class="pagination-item">2</a>
                        <a href="#" class="pagination-item">3</a>
                        <a href="#" class="pagination-item">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3 class="empty-title">No maintenance requests found</h3>
                        <p class="empty-description">
                            There are no maintenance requests matching your filter criteria. Try adjusting your filters or add a new request.
                        </p>
                        <button class="btn btn-primary" onclick="openNewRequestModal()">
                            <i class="fas fa-plus"></i> Add New Request
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Mobile menu toggle -->
    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Update Status Modal -->
    <div class="modal-backdrop" id="updateStatusModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Update Request Status</h3>
                <button class="modal-close" onclick="closeModal('updateStatusModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="update_request_id">
                    
                    <div class="form-group">
                        <label for="new_status" class="form-label">Status</label>
                        <select name="new_status" id="new_status" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('updateStatusModal')">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Request Modal -->
    <div class="modal-backdrop" id="viewRequestModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Request Details</h3>
                <button class="modal-close" onclick="closeModal('viewRequestModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="requestDetails">
                    <!-- Request details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('viewRequestModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- New Request Modal -->
    <div class="modal-backdrop" id="newRequestModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">New Maintenance Request</h3>
                <button class="modal-close" onclick="closeModal('newRequestModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="post" action="add_maintenance_request.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="property_id" class="form-label">Property</label>
                        <select name="property_id" id="property_id" class="form-select" required>
                            <option value="">Select Property</option>
                            <?php foreach ($properties as $id => $title): ?>
                                <option value="<?php echo $id; ?>">
                                    <?php echo htmlspecialchars($title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="new_priority" class="form-select" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('newRequestModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
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

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
        }

        function updateStatus(requestId, currentStatus) {
            document.getElementById('update_request_id').value = requestId;
            
            const statusSelect = document.getElementById('new_status');
            for (let i = 0; i < statusSelect.options.length; i++) {
                if (statusSelect.options[i].value === currentStatus) {
                    statusSelect.selectedIndex = i;
                    break;
                }
            }
            
            openModal('updateStatusModal');
        }

        function viewRequest(requestId) {
            // In a real application, you would fetch the request details via AJAX
            // For this example, we'll just show a placeholder
            const requestDetails = document.getElementById('requestDetails');
            
            // Find the request in the PHP array
            <?php
            echo "const requests = " . json_encode($maintenance_requests) . ";";
            ?>
            
            const request = requests.find(r => r.id == requestId);
            
            if (request) {
                let statusClass = '';
                if (request.status === 'completed') {
                    statusClass = 'completed';
                } else if (request.status === 'pending') {
                    statusClass = 'pending';
                } else if (request.status === 'in_progress') {
                    statusClass = 'in_progress';
                }
                
                let priorityClass = '';
                if (request.priority === 'high') {
                    priorityClass = 'high';
                } else if (request.priority === 'medium') {
                    priorityClass = 'medium';
                } else if (request.priority === 'low') {
                    priorityClass = 'low';
                }
                
                requestDetails.innerHTML = `
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">${request.title}</h4>
                        <p style="color: var(--gray-600); margin-bottom: 1rem;">
                            <strong>Property:</strong> ${request.property_title}
                        </p>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <span class="status-badge ${statusClass}">
                                ${request.status.replace('_', ' ').charAt(0).toUpperCase() + request.status.replace('_', ' ').slice(1)}
                            </span>
                            <span class="priority-badge ${priorityClass}">
                                ${request.priority.charAt(0).toUpperCase() + request.priority.slice(1)} Priority
                            </span>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h5 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">Description</h5>
                        <p style="white-space: pre-line;">${request.description}</p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <h5 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Created</h5>
                            <p>${new Date(request.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
                        </div>
                        <div>
                            <h5 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Last Updated</h5>
                            <p>${new Date(request.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem;">
                        <h5 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">Property Address</h5>
                        <p>${request.address}, ${request.city}, ${request.state} ${request.zip_code}</p>
                    </div>
                `;
            } else {
                requestDetails.innerHTML = `<p>Request details not found.</p>`;
            }
            
            openModal('viewRequestModal');
        }

        function openNewRequestModal() {
            openModal('newRequestModal');
        }
    </script>
</body>
</html>


<Actions>
  <Action name="Add maintenance scheduling" description="Implement a calendar for scheduling maintenance visits" />
  <Action name="Create maintenance reports" description="Add reporting functionality for maintenance costs and trends" />
  <Action name="Add photo uploads" description="Allow photo attachments to maintenance requests" />
  <Action name="Implement vendor management" description="Add functionality to assign requests to maintenance vendors" />
</Actions>
