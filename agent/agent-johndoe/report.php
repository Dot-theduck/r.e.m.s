<?php
require_once '../../tenant/config.php'; // adjust path if needed

$landlord_id = 2;

// Initialize filter variables
$filter_report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'financial';
$filter_property = isset($_GET['property']) ? $_GET['property'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01'); // First day of current month
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d'); // Today
$filter_group_by = isset($_GET['group_by']) ? $_GET['group_by'] : 'month';

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

// Financial Report Data
$financial_data = array();
$financial_summary = array(
    'total_revenue' => 0,
    'total_expenses' => 0,
    'net_income' => 0,
    'occupancy_rate' => 0,
    'avg_rent' => 0
);

if ($filter_report_type === 'financial') {
    // Base SQL query for revenue (payments)
    $revenue_sql = "SELECT 
                    DATE_FORMAT(payments.payment_date, ?) as period,
                    SUM(payments.amount) as revenue,
                    COUNT(DISTINCT payments.id) as payment_count
                FROM payments
                INNER JOIN tenant_properties ON payments.tenant_property_id = tenant_properties.id
                INNER JOIN properties ON tenant_properties.property_id = properties.id
                WHERE properties.landlord_id = ?
                AND payments.payment_date BETWEEN ? AND ?";

    // Add property filter if selected
    if (!empty($filter_property)) {
        $revenue_sql .= " AND properties.id = ?";
    }

    // Group by period
    $format_string = '%Y-%m';
    if ($filter_group_by === 'day') {
        $format_string = '%Y-%m-%d';
    } elseif ($filter_group_by === 'week') {
        $format_string = '%x-%v'; // Year-Week
    } elseif ($filter_group_by === 'month') {
        $format_string = '%Y-%m';
    } elseif ($filter_group_by === 'quarter') {
        $format_string = '%Y-Q%q';
    } elseif ($filter_group_by === 'year') {
        $format_string = '%Y';
    }

    $revenue_sql .= " GROUP BY period ORDER BY MIN(payments.payment_date)";

    // Execute revenue query
    if ($revenue_stmt = $mysqli->prepare($revenue_sql)) {
        if (!empty($filter_property)) {
            $revenue_stmt->bind_param("sissi", $format_string, $landlord_id, $filter_date_from, $filter_date_to, $filter_property);
        } else {
            $revenue_stmt->bind_param("siss", $format_string, $landlord_id, $filter_date_from, $filter_date_to);
        }
        
        $revenue_stmt->execute();
        $revenue_result = $revenue_stmt->get_result();
        
        while ($row = $revenue_result->fetch_assoc()) {
            if (!isset($financial_data[$row['period']])) {
                $financial_data[$row['period']] = array(
                    'period' => $row['period'],
                    'revenue' => 0,
                    'expenses' => 0,
                    'net_income' => 0
                );
            }
            
            $financial_data[$row['period']]['revenue'] = $row['revenue'];
            $financial_summary['total_revenue'] += $row['revenue'];
        }
        
        $revenue_stmt->close();
    }

    // Base SQL query for expenses (maintenance costs, etc.)
    $expenses_sql = "SELECT 
                    DATE_FORMAT(expenses.expense_date, ?) as period,
                    SUM(expenses.amount) as expenses,
                    COUNT(DISTINCT expenses.id) as expense_count
                FROM expenses
                INNER JOIN properties ON expenses.property_id = properties.id
                WHERE properties.landlord_id = ?
                AND expenses.expense_date BETWEEN ? AND ?";

    // Add property filter if selected
    if (!empty($filter_property)) {
        $expenses_sql .= " AND properties.id = ?";
    }

    // Group by period
    $expenses_sql .= " GROUP BY period ORDER BY MIN(expenses.expense_date)";

    // Execute expenses query
    if ($expenses_stmt = $mysqli->prepare($expenses_sql)) {
        if (!empty($filter_property)) {
            $expenses_stmt->bind_param("siss", $format_string, $landlord_id, $filter_date_from, $filter_date_to, $filter_property);
        } else {
            $expenses_stmt->bind_param("sis", $format_string, $landlord_id, $filter_date_from, $filter_date_to);
        }
        
        $expenses_stmt->execute();
        $expenses_result = $expenses_stmt->get_result();
        
        while ($row = $expenses_result->fetch_assoc()) {
            if (!isset($financial_data[$row['period']])) {
                $financial_data[$row['period']] = array(
                    'period' => $row['period'],
                    'revenue' => 0,
                    'expenses' => 0,
                    'net_income' => 0
                );
            }
            
            $financial_data[$row['period']]['expenses'] = $row['expenses'];
            $financial_summary['total_expenses'] += $row['expenses'];
        }
        
        $expenses_stmt->close();
    }

    // Calculate net income for each period
    foreach ($financial_data as $period => $data) {
        $financial_data[$period]['net_income'] = $data['revenue'] - $data['expenses'];
    }

    // Calculate overall financial summary
    $financial_summary['net_income'] = $financial_summary['total_revenue'] - $financial_summary['total_expenses'];

    // Get occupancy rate and average rent
    $occupancy_sql = "SELECT 
                        COUNT(*) as total_properties,
                        SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_properties,
                        AVG(CASE WHEN status = 'occupied' THEN rent_amount ELSE NULL END) as avg_rent
                    FROM properties
                    WHERE landlord_id = ?";

    if (!empty($filter_property)) {
        $occupancy_sql .= " AND id = ?";
    }

    if ($occupancy_stmt = $mysqli->prepare($occupancy_sql)) {
        if (!empty($filter_property)) {
            $occupancy_stmt->bind_param("ii", $landlord_id, $filter_property);
        } else {
            $occupancy_stmt->bind_param("i", $landlord_id);
        }
        
        $occupancy_stmt->execute();
        $occupancy_result = $occupancy_stmt->get_result();
        
        if ($row = $occupancy_result->fetch_assoc()) {
            $total_properties = $row['total_properties'];
            $occupied_properties = $row['occupied_properties'];
            
            if ($total_properties > 0) {
                $financial_summary['occupancy_rate'] = ($occupied_properties / $total_properties) * 100;
            }
            
            $financial_summary['avg_rent'] = $row['avg_rent'] ?: 0;
        }
        
        $occupancy_stmt->close();
    }
}

// Property Report Data
$property_data = array();
$property_summary = array(
    'total_properties' => 0,
    'occupied_properties' => 0,
    'vacant_properties' => 0,
    'maintenance_requests' => 0,
    'avg_property_value' => 0
);

if ($filter_report_type === 'property') {
    // Base SQL query for properties
    $property_sql = "SELECT 
                    properties.id,
                    properties.title,
                    properties.address,
                    properties.city,
                    properties.state,
                    properties.zip_code,
                    properties.status,
                    properties.property_value,
                    properties.bedrooms,
                    properties.bathrooms,
                    properties.square_feet,
                    COUNT(DISTINCT maintenance_requests.id) as maintenance_count,
                    SUM(CASE WHEN maintenance_requests.status = 'pending' THEN 1 ELSE 0 END) as pending_maintenance,
                    tenant_properties.rent_amount,
                    tenant_properties.lease_start,
                    tenant_properties.lease_end
                FROM properties
                LEFT JOIN maintenance_requests ON properties.id = maintenance_requests.property_id
                LEFT JOIN tenant_properties ON properties.id = tenant_properties.property_id AND tenant_properties.status = 'active'
                WHERE properties.landlord_id = ?";

    // Add property filter if selected
    if (!empty($filter_property)) {
        $property_sql .= " AND properties.id = ?";
    }

    // Group by property
    $property_sql .= " GROUP BY properties.id ORDER BY properties.title";

    // Execute property query
    if ($property_stmt = $mysqli->prepare($property_sql)) {
        if (!empty($filter_property)) {
            $property_stmt->bind_param("ii", $landlord_id, $filter_property);
        } else {
            $property_stmt->bind_param("i", $landlord_id);
        }
        
        $property_stmt->execute();
        $property_result = $property_stmt->get_result();
        
        while ($row = $property_result->fetch_assoc()) {
            $property_data[] = $row;
            
            $property_summary['total_properties']++;
            if ($row['status'] === 'occupied') {
                $property_summary['occupied_properties']++;
            } else {
                $property_summary['vacant_properties']++;
            }
            
            $property_summary['maintenance_requests'] += $row['maintenance_count'];
            $property_summary['avg_property_value'] += $row['property_value'];
        }
        
        if ($property_summary['total_properties'] > 0) {
            $property_summary['avg_property_value'] /= $property_summary['total_properties'];
        }
        
        $property_stmt->close();
    }
}

// Tenant Report Data
$tenant_data = array();
$tenant_summary = array(
    'total_tenants' => 0,
    'active_tenants' => 0,
    'pending_tenants' => 0,
    'expiring_leases' => 0,
    'total_monthly_revenue' => 0
);

if ($filter_report_type === 'tenant') {
    // Base SQL query for tenants
    $tenant_sql = "SELECT 
                    tenants.id,
                    tenants.first_name,
                    tenants.last_name,
                    tenants.email,
                    tenants.phone,
                    tenant_properties.status,
                    tenant_properties.lease_start,
                    tenant_properties.lease_end,
                    tenant_properties.rent_amount,
                    properties.id as property_id,
                    properties.title as property_title,
                    (SELECT COUNT(*) FROM payments WHERE payments.tenant_property_id = tenant_properties.id) as payment_count,
                    (SELECT SUM(amount) FROM payments WHERE payments.tenant_property_id = tenant_properties.id) as total_paid
                FROM tenants
                INNER JOIN tenant_properties ON tenants.id = tenant_properties.tenant_id
                INNER JOIN properties ON tenant_properties.property_id = properties.id
                WHERE properties.landlord_id = ?";

    // Add property filter if selected
    if (!empty($filter_property)) {
        $tenant_sql .= " AND properties.id = ?";
    }

    // Add date filter for lease period
    $tenant_sql .= " AND (tenant_properties.lease_start <= ? AND tenant_properties.lease_end >= ?)";

    // Order by name
    $tenant_sql .= " ORDER BY tenants.last_name, tenants.first_name";

    // Execute tenant query
    if ($tenant_stmt = $mysqli->prepare($tenant_sql)) {
        if (!empty($filter_property)) {
            $tenant_stmt->bind_param("iiss", $landlord_id, $filter_property, $filter_date_to, $filter_date_from);
        } else {
            $tenant_stmt->bind_param("iss", $landlord_id, $filter_date_to, $filter_date_from);
        }
        
        $tenant_stmt->execute();
        $tenant_result = $tenant_stmt->get_result();
        
        while ($row = $tenant_result->fetch_assoc()) {
            $tenant_data[] = $row;
            
            $tenant_summary['total_tenants']++;
            if ($row['status'] === 'active') {
                $tenant_summary['active_tenants']++;
                $tenant_summary['total_monthly_revenue'] += $row['rent_amount'];
            } elseif ($row['status'] === 'pending') {
                $tenant_summary['pending_tenants']++;
            }
            
            // Check if lease is expiring within 60 days
            $today = new DateTime();
            $lease_end = new DateTime($row['lease_end']);
            $days_until_expiry = $today->diff($lease_end)->days;
            
            if ($lease_end > $today && $days_until_expiry <= 60) {
                $tenant_summary['expiring_leases']++;
            }
        }
        
        $tenant_stmt->close();
    }
}

// Maintenance Report Data
$maintenance_data = array();
$maintenance_summary = array(
    'total_requests' => 0,
    'pending_requests' => 0,
    'in_progress_requests' => 0,
    'completed_requests' => 0,
    'avg_completion_time' => 0,
    'high_priority_requests' => 0
);

if ($filter_report_type === 'maintenance') {
    // Base SQL query for maintenance requests
    $maintenance_sql = "SELECT 
                        maintenance_requests.id,
                        maintenance_requests.title,
                        maintenance_requests.description,
                        maintenance_requests.status,
                        maintenance_requests.priority,
                        maintenance_requests.created_at,
                        maintenance_requests.updated_at,
                        DATEDIFF(maintenance_requests.updated_at, maintenance_requests.created_at) as days_to_complete,
                        properties.id as property_id,
                        properties.title as property_title
                    FROM maintenance_requests
                    INNER JOIN properties ON maintenance_requests.property_id = properties.id
                    WHERE properties.landlord_id = ?
                    AND maintenance_requests.created_at BETWEEN ? AND ?";

    // Add property filter if selected
    if (!empty($filter_property)) {
        $maintenance_sql .= " AND properties.id = ?";
    }

    // Order by date
    $maintenance_sql .= " ORDER BY maintenance_requests.created_at DESC";

    // Execute maintenance query
    if ($maintenance_stmt = $mysqli->prepare($maintenance_sql)) {
        if (!empty($filter_property)) {
            $maintenance_stmt->bind_param("issi", $landlord_id, $filter_date_from, $filter_date_to, $filter_property);
        } else {
            $maintenance_stmt->bind_param("iss", $landlord_id, $filter_date_from, $filter_date_to);
        }
        
        $maintenance_stmt->execute();
        $maintenance_result = $maintenance_stmt->get_result();
        
        $total_days = 0;
        $completed_count = 0;
        
        while ($row = $maintenance_result->fetch_assoc()) {
            $maintenance_data[] = $row;
            
            $maintenance_summary['total_requests']++;
            
            if ($row['status'] === 'pending') {
                $maintenance_summary['pending_requests']++;
            } elseif ($row['status'] === 'in_progress') {
                $maintenance_summary['in_progress_requests']++;
            } elseif ($row['status'] === 'completed') {
                $maintenance_summary['completed_requests']++;
                $total_days += $row['days_to_complete'];
                $completed_count++;
            }
            
            if ($row['priority'] === 'high') {
                $maintenance_summary['high_priority_requests']++;
            }
        }
        
        if ($completed_count > 0) {
            $maintenance_summary['avg_completion_time'] = $total_days / $completed_count;
        }
        
        $maintenance_stmt->close();
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | REMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Report Card */
        .report-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .report-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .report-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .report-body {
            padding: 1.5rem;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 2rem;
        }

        /* Table Styles */
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

        .status-badge.active, .status-badge.completed {
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

        .status-badge.expired, .status-badge.high {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .status-badge.medium {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .status-badge.low {
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

        /* Report Type Tabs */
        .report-tabs {
            display: flex;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 2rem;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .report-tabs::-webkit-scrollbar {
            display: none;
        }

        .report-tab {
            padding: 1rem 1.5rem;
            font-weight: 500;
            color: var(--gray-600);
            border-bottom: 2px solid transparent;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .report-tab:hover {
            color: var(--gray-800);
        }

        .report-tab.active {
            color: var(--agent-primary);
            border-bottom-color: var(--agent-primary);
        }

        /* Export Button */
        .export-dropdown {
            position: relative;
            display: inline-block;
        }

        .export-menu {
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 10;
            min-width: 10rem;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            display: none;
        }

        .export-menu.active {
            display: block;
        }

        .export-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s;
        }

        .export-item:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .export-icon {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }

        /* Grid Layout */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 1024px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        /* Summary Card */
        .summary-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .summary-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .summary-item {
            margin-bottom: 0.75rem;
        }

        .summary-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .summary-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .summary-subvalue {
            font-size: 0.875rem;
            color: var(--gray-600);
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
                <h1 class="page-title">Reports</h1>
                <div class="header-actions">
                    <div class="export-dropdown">
                        <button class="btn btn-primary" onclick="toggleExportMenu()">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                        <div class="export-menu" id="exportMenu">
                            <a href="#" class="export-item" onclick="exportReport('pdf')">
                                <i class="fas fa-file-pdf export-icon"></i> Export as PDF
                            </a>
                            <a href="#" class="export-item" onclick="exportReport('excel')">
                                <i class="fas fa-file-excel export-icon"></i> Export as Excel
                            </a>
                            <a href="#" class="export-item" onclick="exportReport('csv')">
                                <i class="fas fa-file-csv export-icon"></i> Export as CSV
                            </a>
                            <a href="#" class="export-item" onclick="printReport()">
                                <i class="fas fa-print export-icon"></i> Print Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Type Tabs -->
            <div class="report-tabs">
                <a href="?report_type=financial<?php echo !empty($filter_property) ? '&property=' . $filter_property : ''; ?>" class="report-tab <?php echo $filter_report_type === 'financial' ? 'active' : ''; ?>">
                    <i class="fas fa-dollar-sign"></i> Financial Reports
                </a>
                <a href="?report_type=property<?php echo !empty($filter_property) ? '&property=' . $filter_property : ''; ?>" class="report-tab <?php echo $filter_report_type === 'property' ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i> Property Reports
                </a>
                <a href="?report_type=tenant<?php echo !empty($filter_property) ? '&property=' . $filter_property : ''; ?>" class="report-tab <?php echo $filter_report_type === 'tenant' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Tenant Reports
                </a>
                <a href="?report_type=maintenance<?php echo !empty($filter_property) ? '&property=' . $filter_property : ''; ?>" class="report-tab <?php echo $filter_report_type === 'maintenance' ? 'active' : ''; ?>">
                    <i class="fas fa-tools"></i> Maintenance Reports
                </a>
            </div>

            <!-- Filter Card -->
            <div class="filter-card">
                <div class="filter-header">
                    <h2 class="filter-title">
                        <i class="fas fa-filter"></i> Filter Report
                    </h2>
                </div>
                <div class="filter-body">
                    <form action="" method="get" class="filter-form">
                        <input type="hidden" name="report_type" value="<?php echo $filter_report_type; ?>">
                        
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
                            <label for="date_from" class="filter-label">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="filter-control" value="<?php echo htmlspecialchars($filter_date_from); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="date_to" class="filter-label">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="filter-control" value="<?php echo htmlspecialchars($filter_date_to); ?>">
                        </div>
                        
                        <?php if ($filter_report_type === 'financial'): ?>
                            <div class="filter-group">
                                <label for="group_by" class="filter-label">Group By</label>
                                <select name="group_by" id="group_by" class="filter-control">
                                    <option value="day" <?php if ($filter_group_by === 'day') echo 'selected'; ?>>Day</option>
                                    <option value="week" <?php if ($filter_group_by === 'week') echo 'selected'; ?>>Week</option>
                                    <option value="month" <?php if ($filter_group_by === 'month') echo 'selected'; ?>>Month</option>
                                    <option value="quarter" <?php if ($filter_group_by === 'quarter') echo 'selected'; ?>>Quarter</option>
                                    <option value="year" <?php if ($filter_group_by === 'year') echo 'selected'; ?>>Year</option>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <div class="filter-actions">
                            <a href="?report_type=<?php echo $filter_report_type; ?>" class="btn btn-outline">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($filter_report_type === 'financial'): ?>
                <!-- Financial Report -->
                <div class="summary-card">
                    <h3 class="summary-title">Financial Summary</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-label">Total Revenue</div>
                            <div class="summary-value">$<?php echo number_format($financial_summary['total_revenue'], 2); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Total Expenses</div>
                            <div class="summary-value">$<?php echo number_format($financial_summary['total_expenses'], 2); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Net Income</div>
                            <div class="summary-value">$<?php echo number_format($financial_summary['net_income'], 2); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Occupancy Rate</div>
                            <div class="summary-value"><?php echo number_format($financial_summary['occupancy_rate'], 1); ?>%</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Average Rent</div>
                            <div class="summary-value">$<?php echo number_format($financial_summary['avg_rent'], 2); ?></div>
                            <div class="summary-subvalue">per month</div>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <!-- Revenue vs Expenses Chart -->
                    <div class="report-card">
                        <div class="report-header">
                            <h2 class="report-title">
                                <i class="fas fa-chart-line"></i> Revenue vs Expenses
                            </h2>
                        </div>
                        <div class="report-body">
                            <div class="chart-container">
                                <canvas id="revenueExpensesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Net Income Chart -->
                    <div class="report-card">
                        <div class="report-header">
                            <h2 class="report-title">
                                <i class="fas fa-chart-bar"></i> Net Income
                            </h2>
                        </div>
                        <div class="report-body">
                            <div class="chart-container">
                                <canvas id="netIncomeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Data Table -->
                <div class="report-card">
                    <div class="report-header">
                        <h2 class="report-title">
                            <i class="fas fa-table"></i> Financial Data
                        </h2>
                    </div>
                    <div class="report-body">
                        <?php if (count($financial_data) > 0): ?>
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Revenue</th>
                                            <th>Expenses</th>
                                            <th>Net Income</th>
                                            <th>Profit Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($financial_data as $period => $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($period); ?></td>
                                                <td>$<?php echo number_format($data['revenue'], 2); ?></td>
                                                <td>$<?php echo number_format($data['expenses'], 2); ?></td>
                                                <td>$<?php echo number_format($data['net_income'], 2); ?></td>
                                                <td>
                                                    <?php 
                                                        $profit_margin = $data['revenue'] > 0 ? ($data['net_income'] / $data['revenue']) * 100 : 0;
                                                        echo number_format($profit_margin, 1) . '%';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3 class="empty-title">No financial data available</h3>
                                <p class="empty-description">
                                    There is no financial data for the selected period. Try adjusting your filters or adding financial records.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($filter_report_type === 'property'): ?>
                <!-- Property Report -->
                <div class="summary-card">
                    <h3 class="summary-title">Property Summary</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-label">Total Properties</div>
                            <div class="summary-value"><?php echo $property_summary['total_properties']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Occupied Properties</div>
                            <div class="summary-value"><?php echo $property_summary['occupied_properties']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Vacant Properties</div>
                            <div class="summary-value"><?php echo $property_summary['vacant_properties']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Maintenance Requests</div>
                            <div class="summary-value"><?php echo $property_summary['maintenance_requests']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Average Property Value</div>
                            <div class="summary-value">$<?php echo number_format($property_summary['avg_property_value'], 2); ?></div>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <!-- Occupancy Rate Chart -->
                    <div class="report-card">
                        <div class="report-header">
                            <h2 class="report-title">
                                <i class="fas fa-chart-pie"></i> Occupancy Rate
                            </h2>
                        </div>
                        <div class="report-body">
                            <div class="chart-container">
                                <canvas id="occupancyChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Property Value Distribution Chart -->
                    <div class="report-card">
                        <div class="report-header">
                            <h2 class="report-title">
                                <i class="fas fa-chart-bar"></i> Property Value Distribution
                            </h2>
                        </div>
                        <div class="report-body">
                            <div class="chart-container">
                                <canvas id="propertyValueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Data Table -->
                <div class="report-card">
                    <div class="report-header">
                        <h2 class="report-title">
                            <i class="fas fa-table"></i> Property Data
                        </h2>
                    </div>
                    <div class="report-body">
                        <?php if (count($property_data) > 0): ?>
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>Address</th>
                                            <th>Status</th>
                                            <th>Value</th>
                                            <th>Size</th>
                                            <th>Rent</th>
                                            <th>Maintenance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($property_data as $property): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($property['title']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['state'] . ' ' . $property['zip_code']); ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo $property['status']; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($property['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>$<?php echo number_format($property['property_value'], 2); ?></td>
                                                <td>
                                                    <?php echo $property['square_feet']; ?> sq ft<br>
                                                    <span style="font-size: 0.75rem; color: var(--gray-500);">
                                                        <?php echo $property['bedrooms']; ?> bed, <?php echo $property['bathrooms']; ?> bath
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($property['rent_amount']): ?>
                                                        $<?php echo number_format($property['rent_amount'], 2); ?>/month
                                                    <?php else: ?>
                                                        N/A
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo $property['maintenance_count']; ?> requests<br>
                                                    <?php if ($property['pending_maintenance'] > 0): ?>
                                                        <span style="font-size: 0.75rem; color: var(--warning);">
                                                            <?php echo $property['pending_maintenance']; ?> pending
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <h3 class="empty-title">No property data available</h3>
                                <p class="empty-description">
                                    There are no properties matching your filter criteria. Try adjusting your filters or adding properties.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($filter_report_type === 'tenant'): ?>
                <!-- Tenant Report -->
                <div class="summary-card">
                    <h3 class="summary-title">Tenant Summary</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-label">Total Tenants</div>
                            <div class="summary-value"><?php echo $tenant_summary['total_tenants']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Active Tenants</div>
                            <div class="summary-value"><?php echo $tenant_summary['active_tenants']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Pending Tenants</div>
                            <div class="summary-value"><?php echo $tenant_summary['pending_tenants']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Expiring Leases</div>
                            <div class="summary-value"><?php echo $tenant_summary['expiring_leases']; ?></div>
                            <div class="summary-subvalue">within 60 days</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Monthly Revenue</div>
                            <div class="summary-value">$<?php echo number_format($tenant_summary['total_monthly_revenue'], 2); ?></div>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <!-- Tenant Status Chart -->
                    <div class="report-card">
                        <div class="report-header">
                            <h2 class="report-title">
                                <i class="fas fa-chart-pie"></i> Tenant Status
                            </h2>
                        </div>
                        <div class="report-body">
                            <div class="chart-container">
                                <canvas id="tenantStatusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Lease Expiry Chart -->
                    <div class="report-card">
                        <div class="report-header">
                            <h2 class="report-title">
                                <i class="fas fa-chart-bar"></i> Lease Expiry Timeline
                            </h2>
                        </div>
                        <div class="report-body">
                            <div class="chart-container">
                                <canvas id="leaseExpiryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tenant Data Table -->
                <div class="report-card">
                    <div class="report-header">
                        <h2 class="report-title">
                            <i class="fas fa-table"></i> Tenant Data
                        </h2>
                    </div>
                    <div class="report-body">
                        <?php if (count($tenant_data) > 0): ?>
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Tenant</th>
                                            <th>Property</th>
                                            <th>Status</th>
                                            <th>Lease Period</th>
                                            <th>Rent</th>
                                            <th>Payments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tenant_data as $tenant): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($tenant['first_name'] . ' ' . $tenant['last_name']); ?><br>
                                                    <span style="font-size: 0.75rem; color: var(--gray-500);">
                                                        <?php echo htmlspecialchars($tenant['email']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($tenant['property_title']); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $tenant['status']; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($tenant['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                        echo date('M d, Y', strtotime($tenant['lease_start'])) . ' to ' . date('M d, Y', strtotime($tenant['lease_end']));
                                                        
                                                        // Calculate days left
                                                        $today = new DateTime();
                                                        $lease_end = new DateTime($tenant['lease_end']);
                                                        $days_left = $today->diff($lease_end)->days;
                                                        $is_expired = $today > $lease_end;
                                                        
                                                        if ($is_expired) {
                                                            echo '<br><span style="font-size: 0.75rem; color: var(--danger);">Expired</span>';
                                                        } elseif ($days_left <= 30) {
                                                            echo '<br><span style="font-size: 0.75rem; color: var(--danger);">' . $days_left . ' days left</span>';
                                                        } elseif ($days_left <= 60) {
                                                            echo '<br><span style="font-size: 0.75rem; color: var(--warning);">' . $days_left . ' days left</span>';
                                                        }
                                                    ?>
                                                </td>
                                                <td>$<?php echo number_format($tenant['rent_amount'], 2); ?>/month</td>
                                                <td>
                                                    <?php echo $tenant['payment_count']; ?> payments<br>
                                                    <span style="font-size: 0.75rem; color: var(--gray-500);">
                                                        $<?php echo number_format($tenant['total_paid'] ?: 0, 2); ?> total
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3 class="empty-title">No tenant data available</h3>
                                <p class="empty-description">
                                    There are no tenants matching your filter criteria. Try adjusting your filters or adding tenants.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($filter_report_type === 'maintenance'): ?>
                <!-- Maintenance Report -->
                <div class="summary-card">
                    <h3 class="summary-title">Maintenance Summary</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-label">Total Requests</div>
                            <div class="summary-value"><?php echo $maintenance_summary['total_requests']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Pending Requests</div>
                            <div class="summary-value"><?php echo $maintenance_summary['pending_requests']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">In Progress</div>
                            <div class="summary-value"><?php echo $maintenance_summary['in_progress_requests']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Completed</div>
                            <div class="summary-value"><?php echo $maintenance_summary['completed_requests']; ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Avg. Completion Time</div>
                            <div class="summary-value"><?php echo number_format($maintenance_summary['avg_completion_time'], 1); ?></div>
                            <div class="summary-subvalue">days</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">High Priority</div>
                            <div class="summary-value"><?php echo $maintenance_summary['high_priority_requests']; ?></div>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <!-- Maintenance Status Chart -->
                    <div class="report-card">
                        <div class="report-header">
                            <h2 class="report-title">
                                <i class="fas fa-chart-pie"></i> Maintenance Status
                            </h2>
                        </div>
                        <div class="report-body">
                            <div class="chart-container">
                                <canvas id="maintenanceStatusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance Priority Chart -->
                    <div class="report-card">
                        <div class="report-header">
                            <h2 class="report-title">
                                <i class="fas fa-chart-bar"></i> Maintenance by Priority
                            </h2>
                        </div>
                        <div class="report-body">
                            <div class="chart-container">
                                <canvas id="maintenancePriorityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Data Table -->
                <div class="report-card">
                    <div class="report-header">
                        <h2 class="report-title">
                            <i class="fas fa-table"></i> Maintenance Data
                        </h2>
                    </div>
                    <div class="report-body">
                        <?php if (count($maintenance_data) > 0): ?>
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Request</th>
                                            <th>Property</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Created</th>
                                            <th>Updated</th>
                                            <th>Time to Complete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($maintenance_data as $request): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($request['title']); ?></td>
                                                <td><?php echo htmlspecialchars($request['property_title']); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $request['status']; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($request['status']))); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo $request['priority']; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($request['priority'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($request['updated_at'])); ?></td>
                                                <td>
                                                    <?php 
                                                        if ($request['status'] === 'completed') {
                                                            echo $request['days_to_complete'] . ' days';
                                                        } else {
                                                            echo 'In progress';
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <h3 class="empty-title">No maintenance data available</h3>
                                <p class="empty-description">
                                    There are no maintenance requests matching your filter criteria. Try adjusting your filters or adding maintenance requests.
                                </p>
                            </div>
                        <?php endif; ?>
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

        // Export dropdown functionality
        function toggleExportMenu() {
            document.getElementById('exportMenu').classList.toggle('active');
        }

        // Close export menu when clicking outside
        document.addEventListener('click', function(event) {
            const exportMenu = document.getElementById('exportMenu');
            const exportButton = document.querySelector('.export-dropdown button');
            
            if (exportMenu && exportButton) {
                if (!exportButton.contains(event.target) && !exportMenu.contains(event.target)) {
                    exportMenu.classList.remove('active');
                }
            }
        });

        // Export report function
        function exportReport(format) {
            // In a real application, this would trigger a download
            alert('Exporting report as ' + format.toUpperCase());
            document.getElementById('exportMenu').classList.remove('active');
        }

        // Print report function
        function printReport() {
            window.print();
            document.getElementById('exportMenu').classList.remove('active');
        }

        // Chart.js initialization
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($filter_report_type === 'financial'): ?>
                // Financial Charts
                const revenueExpensesData = {
                    labels: [
                        <?php 
                            foreach ($financial_data as $period => $data) {
                                echo "'" . $period . "', ";
                            }
                        ?>
                    ],
                    datasets: [
                        {
                            label: 'Revenue',
                            data: [
                                <?php 
                                    foreach ($financial_data as $period => $data) {
                                        echo $data['revenue'] . ", ";
                                    }
                                ?>
                            ],
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Expenses',
                            data: [
                                <?php 
                                    foreach ($financial_data as $period => $data) {
                                        echo $data['expenses'] . ", ";
                                    }
                                ?>
                            ],
                            backgroundColor: 'rgba(239, 68, 68, 0.2)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 2
                        }
                    ]
                };

                const netIncomeData = {
                    labels: [
                        <?php 
                            foreach ($financial_data as $period => $data) {
                                echo "'" . $period . "', ";
                            }
                        ?>
                    ],
                    datasets: [
                        {
                            label: 'Net Income',
                            data: [
                                <?php 
                                    foreach ($financial_data as $period => $data) {
                                        echo $data['net_income'] . ", ";
                                    }
                                ?>
                            ],
                            backgroundColor: function(context) {
                                const value = context.dataset.data[context.dataIndex];
                                return value >= 0 ? 'rgba(99, 102, 241, 0.2)' : 'rgba(239, 68, 68, 0.2)';
                            },
                            borderColor: function(context) {
                                const value = context.dataset.data[context.dataIndex];
                                return value >= 0 ? 'rgba(99, 102, 241, 1)' : 'rgba(239, 68, 68, 1)';
                            },
                            borderWidth: 2
                        }
                    ]
                };

                new Chart(document.getElementById('revenueExpensesChart'), {
                    type: 'line',
                    data: revenueExpensesData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': $' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });

                new Chart(document.getElementById('netIncomeChart'), {
                    type: 'bar',
                    data: netIncomeData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': $' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });

            <?php elseif ($filter_report_type === 'property'): ?>
                // Property Charts
                const occupancyData = {
                    labels: ['Occupied', 'Vacant'],
                    datasets: [{
                        data: [
                            <?php echo $property_summary['occupied_properties']; ?>,
                            <?php echo $property_summary['vacant_properties']; ?>
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(239, 68, 68, 0.7)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 1
                    }]
                };

                // Create property value ranges
                const propertyValues = [
                    <?php 
                        foreach ($property_data as $property) {
                            echo $property['property_value'] . ", ";
                        }
                    ?>
                ];

                // Group property values into ranges
                const valueRanges = {
                    '0-100k': 0,
                    '100k-250k': 0,
                    '250k-500k': 0,
                    '500k-750k': 0,
                    '750k-1M': 0,
                    '1M+': 0
                };

                propertyValues.forEach(value => {
                    if (value < 100000) valueRanges['0-100k']++;
                    else if (value < 250000) valueRanges['100k-250k']++;
                    else if (value < 500000) valueRanges['250k-500k']++;
                    else if (value < 750000) valueRanges['500k-750k']++;
                    else if (value < 1000000) valueRanges['750k-1M']++;
                    else valueRanges['1M+']++;
                });

                const propertyValueData = {
                    labels: Object.keys(valueRanges),
                    datasets: [{
                        label: 'Number of Properties',
                        data: Object.values(valueRanges),
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 1
                    }]
                };

                new Chart(document.getElementById('occupancyChart'), {
                    type: 'pie',
                    data: occupancyData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                new Chart(document.getElementById('propertyValueChart'), {
                    type: 'bar',
                    data: propertyValueData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

            <?php elseif ($filter_report_type === 'tenant'): ?>
                // Tenant Charts
                const tenantStatusData = {
                    labels: ['Active', 'Pending', 'Expired'],
                    datasets: [{
                        data: [
                            <?php echo $tenant_summary['active_tenants']; ?>,
                            <?php echo $tenant_summary['pending_tenants']; ?>,
                            <?php echo $tenant_summary['expiring_leases']; ?>
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(239, 68, 68, 0.7)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 1
                    }]
                };

                // Group lease expiry dates by month
                const leaseExpiryMonths = {};
                
                <?php
                    // Get the next 12 months
                    for ($i = 0; $i < 12; $i++) {
                        $month = date('M Y', strtotime("+$i months"));
                        echo "leaseExpiryMonths['$month'] = 0;";
                    }
                    
                    // Count leases expiring in each month
                    foreach ($tenant_data as $tenant) {
                        $expiry_month = date('M Y', strtotime($tenant['lease_end']));
                        echo "if (leaseExpiryMonths['$expiry_month'] !== undefined) leaseExpiryMonths['$expiry_month']++;";
                    }
                ?>

                const leaseExpiryData = {
                    labels: Object.keys(leaseExpiryMonths),
                    datasets: [{
                        label: 'Expiring Leases',
                        data: Object.values(leaseExpiryMonths),
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 1
                    }]
                };

                new Chart(document.getElementById('tenantStatusChart'), {
                    type: 'pie',
                    data: tenantStatusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                new Chart(document.getElementById('leaseExpiryChart'), {
                    type: 'bar',
                    data: leaseExpiryData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

            <?php elseif ($filter_report_type === 'maintenance'): ?>
                // Maintenance Charts
                const maintenanceStatusData = {
                    labels: ['Pending', 'In Progress', 'Completed'],
                    datasets: [{
                        data: [
                            <?php echo $maintenance_summary['pending_requests']; ?>,
                            <?php echo $maintenance_summary['in_progress_requests']; ?>,
                            <?php echo $maintenance_summary['completed_requests']; ?>
                        ],
                        backgroundColor: [
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(16, 185, 129, 0.7)'
                        ],
                        borderColor: [
                            'rgba(245, 158, 11, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(16, 185, 129, 1)'
                        ],
                        borderWidth: 1
                    }]
                };

                // Count maintenance requests by priority
                const priorityCounts = {
                    'high': 0,
                    'medium': 0,
                    'low': 0
                };

                <?php
                    foreach ($maintenance_data as $request) {
                        echo "priorityCounts['" . $request['priority'] . "']++;";
                    }
                ?>

                const maintenancePriorityData = {
                    labels: ['High', 'Medium', 'Low'],
                    datasets: [{
                        label: 'Number of Requests',
                        data: [
                            priorityCounts['high'],
                            priorityCounts['medium'],
                            priorityCounts['low']
                        ],
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(16, 185, 129, 0.7)'
                        ],
                        borderColor: [
                            'rgba(239, 68, 68, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(16, 185, 129, 1)'
                        ],
                        borderWidth: 1
                    }]
                };

                new Chart(document.getElementById('maintenanceStatusChart'), {
                    type: 'pie',
                    data: maintenanceStatusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                new Chart(document.getElementById('maintenancePriorityChart'), {
                    type: 'bar',
                    data: maintenancePriorityData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>