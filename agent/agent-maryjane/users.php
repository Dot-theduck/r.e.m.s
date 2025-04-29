<?php
require_once '../../tenant/config.php'; // adjust path if needed

$landlord_id = 6;

// Initialize filter variables
$filter_property = isset($_GET['property']) ? $_GET['property'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_lease_expiry = isset($_GET['lease_expiry']) ? $_GET['lease_expiry'] : '';

// Base SQL query
$sql = "SELECT 
            tenant_properties.id AS tenant_property_id,
            tenant_properties.lease_start,
            tenant_properties.lease_end,
            tenant_properties.rent_amount,
            tenant_properties.status,
            tenant_properties.payment_day,
            tenants.id AS tenant_id,
            tenants.first_name,
            tenants.last_name,
            tenants.email,
            tenants.phone,
            properties.id AS property_id,
            properties.title AS property_title,
            properties.address,
            properties.city,
            properties.state,
            properties.zip_code,
            properties.type
        FROM tenant_properties
        INNER JOIN tenants ON tenant_properties.tenant_id = tenants.id
        INNER JOIN properties ON tenant_properties.property_id = properties.id
        WHERE properties.landlord_id = ?";

// Add filters to the query if they exist
$params = array($landlord_id);
$types = "i";

if (!empty($filter_property)) {
    $sql .= " AND properties.id = ?";
    $params[] = $filter_property;
    $types .= "i";
}

if (!empty($filter_status)) {
    $sql .= " AND tenant_properties.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_search)) {
    $sql .= " AND (tenants.first_name LIKE ? OR tenants.last_name LIKE ? OR tenants.email LIKE ? OR properties.title LIKE ?)";
    $search_param = "%" . $filter_search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if (!empty($filter_lease_expiry)) {
    if ($filter_lease_expiry === 'soon') {
        $sql .= " AND tenant_properties.lease_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)";
    } elseif ($filter_lease_expiry === 'expired') {
        $sql .= " AND tenant_properties.lease_end < CURDATE()";
    }
}

// Add order by clause
$sql .= " ORDER BY tenant_properties.lease_end ASC";

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

// Get tenant counts
$counts = array(
    'total' => 0,
    'active' => 0,
    'pending' => 0,
    'expired' => 0
);

$count_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN tenant_properties.status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN tenant_properties.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN tenant_properties.lease_end < CURDATE() THEN 1 ELSE 0 END) as expired
            FROM tenant_properties
            INNER JOIN properties ON tenant_properties.property_id = properties.id
            WHERE properties.landlord_id = ?";

if ($count_stmt = $mysqli->prepare($count_sql)) {
    $count_stmt->bind_param("i", $landlord_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    
    if ($row = $count_result->fetch_assoc()) {
        $counts['total'] = $row['total'];
        $counts['active'] = $row['active'];
        $counts['pending'] = $row['pending'];
        $counts['expired'] = $row['expired'];
    }
    
    $count_stmt->close();
}

// Execute the main query
$tenants = array();

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $tenants[] = $row;
        }
        
    } else {
        $error_message = "Error executing query: " . $stmt->error;
    }

    $stmt->close();
} else {
    $error_message = "Prepare failed: " . $mysqli->error;
}

// Calculate total monthly revenue
$total_monthly_revenue = 0;
foreach ($tenants as $tenant) {
    if ($tenant['status'] === 'active') {
        $total_monthly_revenue += $tenant['rent_amount'];
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants | REMS</title>
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

        .status-badge.active {
            background-color: var(--success-light);
            color: var(--success);
        }

        .status-badge.pending {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .status-badge.expired {
            background-color: var(--danger-light);
            color: var(--danger);
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
            max-width: 600px;
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

        /* Tenant Profile */
        .tenant-profile {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 1.5rem;
        }

        .tenant-avatar {
            width: 100%;
            aspect-ratio: 1;
            border-radius: var(--border-radius);
            background-color: var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--gray-500);
            overflow: hidden;
        }

        .tenant-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .tenant-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .tenant-contact {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .tenant-contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-700);
        }

        .tenant-contact-icon {
            width: 1.25rem;
            color: var(--gray-500);
        }

        .tenant-details {
            margin-top: 1.5rem;
        }

        .tenant-details-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .tenant-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .tenant-detail-item {
            margin-bottom: 1rem;
        }

        .tenant-detail-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .tenant-detail-value {
            font-size: 0.875rem;
            color: var(--gray-800);
        }

        /* Search Box */
        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-300);
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: var(--agent-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            pointer-events: none;
        }

        /* Lease Info */
        .lease-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .lease-dates {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .lease-amount {
            font-weight: 600;
            color: var(--gray-800);
        }

        .lease-status {
            margin-top: 0.5rem;
        }

        /* Days Left Indicator */
        .days-left {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .days-left.warning {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .days-left.danger {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .days-left.success {
            background-color: var(--success-light);
            color: var(--success);
        }

        @media (max-width: 640px) {
            .tenant-profile {
                grid-template-columns: 1fr;
            }
            
            .tenant-avatar {
                max-width: 150px;
                margin: 0 auto;
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
                <h1 class="page-title">Tenants</h1>
                <div class="header-actions">
                    <a href="#" class="btn btn-primary" onclick="openNewTenantModal()">
                        <i class="fas fa-user-plus"></i> Add New Tenant
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $counts['total']; ?></div>
                        <div class="stat-label">Total Tenants</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $counts['active']; ?></div>
                        <div class="stat-label">Active Tenants</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $counts['pending']; ?></div>
                        <div class="stat-label">Pending Tenants</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">$<?php echo number_format($total_monthly_revenue, 2); ?></div>
                        <div class="stat-label">Monthly Revenue</div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="filter-card">
                <div class="filter-header">
                    <h2 class="filter-title">
                        <i class="fas fa-filter"></i> Filter Tenants
                    </h2>
                </div>
                <div class="filter-body">
                    <form action="" method="get" class="filter-form">
                        <div class="filter-group">
                            <label for="search" class="filter-label">Search</label>
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="search" id="search" class="search-input" placeholder="Search by name, email or property" value="<?php echo htmlspecialchars($filter_search); ?>">
                            </div>
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
                            <label for="status" class="filter-label">Status</label>
                            <select name="status" id="status" class="filter-control">
                                <option value="">All Statuses</option>
                                <option value="active" <?php if ($filter_status === 'active') echo 'selected'; ?>>Active</option>
                                <option value="pending" <?php if ($filter_status === 'pending') echo 'selected'; ?>>Pending</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="lease_expiry" class="filter-label">Lease Expiry</label>
                            <select name="lease_expiry" id="lease_expiry" class="filter-control">
                                <option value="">All</option>
                                <option value="soon" <?php if ($filter_lease_expiry === 'soon') echo 'selected'; ?>>Expiring Soon (60 days)</option>
                                <option value="expired" <?php if ($filter_lease_expiry === 'expired') echo 'selected'; ?>>Expired</option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <a href="tenants.php" class="btn btn-outline">
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
                        <i class="fas fa-list"></i> Tenant List
                    </h2>
                </div>
                
                <?php if (count($tenants) > 0): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Property</th>
                                    <th>Contact</th>
                                    <th>Lease Period</th>
                                    <th>Rent</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tenants as $tenant): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 500;"><?php echo htmlspecialchars($tenant['first_name'] . ' ' . $tenant['last_name']); ?></div>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($tenant['property_title']); ?></div>
                                            <div style="font-size: 0.75rem; color: var(--gray-500);">
                                                <?php echo htmlspecialchars($tenant['city'] . ', ' . $tenant['state']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($tenant['email']); ?></div>
                                            <div><?php echo htmlspecialchars($tenant['phone']); ?></div>
                                        </td>
                                        <td>
                                            <div class="lease-info">
                                                <div class="lease-dates">
                                                    <span><?php echo date('M d, Y', strtotime($tenant['lease_start'])); ?></span>
                                                    <span>to</span>
                                                    <span><?php echo date('M d, Y', strtotime($tenant['lease_end'])); ?></span>
                                                </div>
                                                <?php
                                                    $today = new DateTime();
                                                    $lease_end = new DateTime($tenant['lease_end']);
                                                    $days_left = $today->diff($lease_end)->days;
                                                    $is_expired = $today > $lease_end;
                                                    
                                                    if ($is_expired) {
                                                        echo '<div class="days-left danger">Expired</div>';
                                                    } elseif ($days_left <= 30) {
                                                        echo '<div class="days-left danger">' . $days_left . ' days left</div>';
                                                    } elseif ($days_left <= 60) {
                                                        echo '<div class="days-left warning">' . $days_left . ' days left</div>';
                                                    } else {
                                                        echo '<div class="days-left success">' . $days_left . ' days left</div>';
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="lease-amount">$<?php echo number_format($tenant['rent_amount'], 2); ?>/month</div>
                                            <div style="font-size: 0.75rem; color: var(--gray-500);">
                                                Due on day <?php echo $tenant['payment_day']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                                $status = $tenant['status'];
                                                $status_class = '';
                                                
                                                if ($status == 'active') {
                                                    $status_class = 'active';
                                                } elseif ($status == 'pending') {
                                                    $status_class = 'pending';
                                                } elseif ($status == 'expired') {
                                                    $status_class = 'expired';
                                                }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo ucfirst(htmlspecialchars($status)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <button class="btn btn-outline btn-sm" onclick="viewTenant(<?php echo $tenant['tenant_id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-primary btn-sm" onclick="editTenant(<?php echo $tenant['tenant_id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="tenant_payments.php?id=<?php echo $tenant['tenant_id']; ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-money-bill"></i>
                                                </a>
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
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="empty-title">No tenants found</h3>
                        <p class="empty-description">
                            There are no tenants matching your filter criteria. Try adjusting your filters or add a new tenant.
                        </p>
                        <button class="btn btn-primary" onclick="openNewTenantModal()">
                            <i class="fas fa-user-plus"></i> Add New Tenant
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

    <!-- View Tenant Modal -->
    <div class="modal-backdrop" id="viewTenantModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Tenant Details</h3>
                <button class="modal-close" onclick="closeModal('viewTenantModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="tenantDetails">
                    <!-- Tenant details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('viewTenantModal')">Close</button>
                <button type="button" class="btn btn-primary" id="editTenantBtn">Edit Tenant</button>
            </div>
        </div>
    </div>

    <!-- New/Edit Tenant Modal -->
    <div class="modal-backdrop" id="tenantFormModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="tenantFormTitle">Add New Tenant</h3>
                <button class="modal-close" onclick="closeModal('tenantFormModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="post" action="save_tenant.php" id="tenantForm">
                <div class="modal-body">
                    <input type="hidden" name="tenant_id" id="tenant_id" value="">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" name="phone" id="phone" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="property_id" class="form-label">Property</label>
                        <select name="property_id" id="property_id" class="form-control" required>
                            <option value="">Select Property</option>
                            <?php foreach ($properties as $id => $title): ?>
                                <option value="<?php echo $id; ?>">
                                    <?php echo htmlspecialchars($title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="lease_start" class="form-label">Lease Start</label>
                            <input type="date" name="lease_start" id="lease_start" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="lease_end" class="form-label">Lease End</label>
                            <input type="date" name="lease_end" id="lease_end" class="form-control" required>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="rent_amount" class="form-label">Rent Amount</label>
                            <input type="number" name="rent_amount" id="rent_amount" class="form-control" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_day" class="form-label">Payment Day</label>
                            <input type="number" name="payment_day" id="payment_day" class="form-control" min="1" max="31" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('tenantFormModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Tenant</button>
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

        function viewTenant(tenantId) {
            // In a real application, you would fetch the tenant details via AJAX
            // For this example, we'll just show a placeholder
            const tenantDetails = document.getElementById('tenantDetails');
            
            // Find the tenant in the PHP array
            <?php
            echo "const tenants = " . json_encode($tenants) . ";";
            ?>
            
            const tenant = tenants.find(t => t.tenant_id == tenantId);
            
            if (tenant) {
                let statusClass = '';
                if (tenant.status === 'active') {
                    statusClass = 'active';
                } else if (tenant.status === 'pending') {
                    statusClass = 'pending';
                }
                
                const today = new Date();
                const leaseEnd = new Date(tenant.lease_end);
                const daysLeft = Math.ceil((leaseEnd - today) / (1000 * 60 * 60 * 24));
                const isExpired = today > leaseEnd;
                
                let daysLeftClass = 'success';
                if (isExpired) {
                    daysLeftClass = 'danger';
                } else if (daysLeft <= 30) {
                    daysLeftClass = 'danger';
                } else if (daysLeft <= 60) {
                    daysLeftClass = 'warning';
                }
                
                tenantDetails.innerHTML = `
                    <div class="tenant-profile">
                        <div class="tenant-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="tenant-info">
                            <div class="tenant-name">${tenant.first_name} ${tenant.last_name}</div>
                            <div class="tenant-contact">
                                <div class="tenant-contact-item">
                                    <i class="fas fa-envelope tenant-contact-icon"></i>
                                    ${tenant.email}
                                </div>
                                <div class="tenant-contact-item">
                                    <i class="fas fa-phone tenant-contact-icon"></i>
                                    ${tenant.phone}
                                </div>
                            </div>
                            <div class="lease-status">
                                <span class="status-badge ${statusClass}">
                                    ${tenant.status.charAt(0).toUpperCase() + tenant.status.slice(1)}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tenant-details">
                        <h4 class="tenant-details-title">Property Information</h4>
                        <div class="tenant-details-grid">
                            <div class="tenant-detail-item">
                                <div class="tenant-detail-label">Property</div>
                                <div class="tenant-detail-value">${tenant.property_title}</div>
                            </div>
                            <div class="tenant-detail-item">
                                <div class="tenant-detail-label">Type</div>
                                <div class="tenant-detail-value">${tenant.type || 'Residential'}</div>
                            </div>
                            <div class="tenant-detail-item">
                                <div class="tenant-detail-label">Address</div>
                                <div class="tenant-detail-value">${tenant.address}, ${tenant.city}, ${tenant.state} ${tenant.zip_code}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tenant-details">
                        <h4 class="tenant-details-title">Lease Information</h4>
                        <div class="tenant-details-grid">
                            <div class="tenant-detail-item">
                                <div class="tenant-detail-label">Lease Period</div>
                                <div class="tenant-detail-value">
                                    ${new Date(tenant.lease_start).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })} to 
                                    ${new Date(tenant.lease_end).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}
                                </div>
                            </div>
                            <div class="tenant-detail-item">
                                <div class="tenant-detail-label">Lease Status</div>
                                <div class="tenant-detail-value">
                                    <span class="days-left ${daysLeftClass}">
                                        ${isExpired ? 'Expired' : daysLeft + ' days left'}
                                    </span>
                                </div>
                            </div>
                            <div class="tenant-detail-item">
                                <div class="tenant-detail-label">Rent Amount</div>
                                <div class="tenant-detail-value">$${parseFloat(tenant.rent_amount).toFixed(2)}/month</div>
                            </div>
                            <div class="tenant-detail-item">
                                <div class="tenant-detail-label">Payment Day</div>
                                <div class="tenant-detail-value">Day ${tenant.payment_day} of each month</div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Set up the edit button
                document.getElementById('editTenantBtn').onclick = function() {
                    closeModal('viewTenantModal');
                    editTenant(tenantId);
                };
            } else {
                tenantDetails.innerHTML = `<p>Tenant details not found.</p>`;
            }
            
            openModal('viewTenantModal');
        }

        function openNewTenantModal() {
            document.getElementById('tenantFormTitle').textContent = 'Add New Tenant';
            document.getElementById('tenantForm').reset();
            document.getElementById('tenant_id').value = '';
            
            // Set default dates
            const today = new Date();
            const oneYearLater = new Date();
            oneYearLater.setFullYear(today.getFullYear() + 1);
            
            document.getElementById('lease_start').value = today.toISOString().split('T')[0];
            document.getElementById('lease_end').value = oneYearLater.toISOString().split('T')[0];
            document.getElementById('payment_day').value = '1';
            
            openModal('tenantFormModal');
        }

        function editTenant(tenantId) {
            document.getElementById('tenantFormTitle').textContent = 'Edit Tenant';
            
            // Find the tenant in the PHP array
            const tenant = tenants.find(t => t.tenant_id == tenantId);
            
            if (tenant) {
                document.getElementById('tenant_id').value = tenant.tenant_id;
                document.getElementById('first_name').value = tenant.first_name;
                document.getElementById('last_name').value = tenant.last_name;
                document.getElementById('email').value = tenant.email;
                document.getElementById('phone').value = tenant.phone;
                document.getElementById('property_id').value = tenant.property_id;
                document.getElementById('lease_start').value = tenant.lease_start.split(' ')[0];
                document.getElementById('lease_end').value = tenant.lease_end.split(' ')[0];
                document.getElementById('rent_amount').value = tenant.rent_amount;
                document.getElementById('payment_day').value = tenant.payment_day;
                document.getElementById('status').value = tenant.status;
                
                openModal('tenantFormModal');
            }
        }
    </script>
</body>
</html>