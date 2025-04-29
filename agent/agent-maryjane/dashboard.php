<?php
require_once '../../tenant/config.php'; // adjust path if needed

$landlord_id = 6;

// Count Properties
$property_sql = "SELECT COUNT(*) as total_properties FROM properties WHERE landlord_id = ?";
$property_stmt = $mysqli->prepare($property_sql);
$property_stmt->bind_param("i", $landlord_id);
$property_stmt->execute();
$property_result = $property_stmt->get_result();
$total_properties = $property_result->fetch_assoc()['total_properties'];

// Count Vacant Properties
$vacant_sql = "SELECT COUNT(*) as vacant_properties FROM properties WHERE landlord_id = ? AND status = 'available'";
$vacant_stmt = $mysqli->prepare($vacant_sql);
$vacant_stmt->bind_param("i", $landlord_id);
$vacant_stmt->execute();
$vacant_result = $vacant_stmt->get_result();
$vacant_properties = $vacant_result->fetch_assoc()['vacant_properties'];

// Count Occupied Properties
$occupied_properties = $total_properties - $vacant_properties;

// Total Payments
$payment_sql = "SELECT SUM(payments.amount) as total_payments 
                FROM payments
                INNER JOIN tenant_properties ON payments.tenant_property_id = tenant_properties.id
                INNER JOIN properties ON tenant_properties.property_id = properties.id
                WHERE properties.landlord_id = ?";
$payment_stmt = $mysqli->prepare($payment_sql);
$payment_stmt->bind_param("i", $landlord_id);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();
$total_payments = $payment_result->fetch_assoc()['total_payments'] ?: 0;

// Recent Payments
$recent_payments_sql = "SELECT payments.amount, payments.payment_date, payments.status, properties.title
                        FROM payments
                        INNER JOIN tenant_properties ON payments.tenant_property_id = tenant_properties.id
                        INNER JOIN properties ON tenant_properties.property_id = properties.id
                        WHERE properties.landlord_id = ?
                        ORDER BY payments.payment_date DESC
                        LIMIT 5";
$recent_payments_stmt = $mysqli->prepare($recent_payments_sql);
$recent_payments_stmt->bind_param("i", $landlord_id);
$recent_payments_stmt->execute();
$recent_payments_result = $recent_payments_stmt->get_result();

// Upcoming Lease Endings
$lease_sql = "SELECT properties.title, tenant_properties.lease_end, properties.id
              FROM tenant_properties
              INNER JOIN properties ON tenant_properties.property_id = properties.id
              WHERE properties.landlord_id = ? 
              AND lease_end BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 60 DAY)
              ORDER BY lease_end ASC";
$lease_stmt = $mysqli->prepare($lease_sql);
if (!$lease_stmt) {
    die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
}
$lease_stmt->bind_param("i", $landlord_id);
$lease_stmt->execute();
$lease_result = $lease_stmt->get_result();

// Maintenance Requests
$maintenance_sql = "SELECT maintenance_requests.title, maintenance_requests.status, properties.title as property_title
                    FROM maintenance_requests
                    INNER JOIN properties ON maintenance_requests.property_id = properties.id
                    WHERE properties.landlord_id = ?
                    ORDER BY 
                        CASE 
                            WHEN maintenance_requests.status = 'urgent' THEN 1
                            WHEN maintenance_requests.status = 'pending' THEN 2
                            WHEN maintenance_requests.status = 'in_progress' THEN 3
                            ELSE 4
                        END,
                        maintenance_requests.created_at DESC
                    LIMIT 5";
$maintenance_stmt = $mysqli->prepare($maintenance_sql);
if ($maintenance_stmt) {
    $maintenance_stmt->bind_param("i", $landlord_id);
    $maintenance_stmt->execute();
    $maintenance_result = $maintenance_stmt->get_result();
} else {
    $maintenance_result = null;
}

// Get current month's revenue
$current_month = date('Y-m-01');
$next_month = date('Y-m-01', strtotime('+1 month'));

$monthly_revenue_sql = "SELECT SUM(payments.amount) as monthly_revenue
                        FROM payments
                        INNER JOIN tenant_properties ON payments.tenant_property_id = tenant_properties.id
                        INNER JOIN properties ON tenant_properties.property_id = properties.id
                        WHERE properties.landlord_id = ?
                        AND payments.payment_date BETWEEN ? AND ?";
$monthly_revenue_stmt = $mysqli->prepare($monthly_revenue_sql);
$monthly_revenue_stmt->bind_param("iss", $landlord_id, $current_month, $next_month);
$monthly_revenue_stmt->execute();
$monthly_revenue_result = $monthly_revenue_stmt->get_result();
$monthly_revenue = $monthly_revenue_result->fetch_assoc()['monthly_revenue'] ?: 0;

// Get occupancy rate
$occupancy_rate = $total_properties > 0 ? ($occupied_properties / $total_properties) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Dashboard | REMS</title>
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

        .welcome-message {
            font-size: 1.125rem;
            color: var(--gray-600);
            margin-bottom: 2rem;
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

        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .dashboard-card.col-span-4 {
            grid-column: span 4;
        }

        .dashboard-card.col-span-6 {
            grid-column: span 6;
        }

        .dashboard-card.col-span-8 {
            grid-column: span 8;
        }

        .dashboard-card.col-span-12 {
            grid-column: span 12;
        }

        @media (max-width: 1200px) {
            .dashboard-card.col-span-4,
            .dashboard-card.col-span-6,
            .dashboard-card.col-span-8 {
                grid-column: span 12;
            }
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* List Styles */
        .list-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-item-content {
            flex: 1;
        }

        .list-item-title {
            font-weight: 500;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .list-item-subtitle {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .list-item-action {
            margin-left: 1rem;
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

        .status-badge.failed {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .status-badge.urgent {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .status-badge.in_progress {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            height: 0.5rem;
            background-color: var(--gray-200);
            border-radius: 9999px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-bar {
            height: 100%;
            border-radius: 9999px;
        }

        .progress-bar.primary {
            background-color: var(--agent-primary);
        }

        .progress-bar.success {
            background-color: var(--success);
        }

        .progress-bar.warning {
            background-color: var(--warning);
        }

        .progress-bar.danger {
            background-color: var(--danger);
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        /* Empty State */
        .empty-state {
            padding: 2rem;
            text-align: center;
        }

        .empty-icon {
            font-size: 2.5rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .empty-description {
            color: var(--gray-600);
            max-width: 20rem;
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
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .quick-action {
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
            padding: 1.25rem;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
        }

        .quick-action:hover {
            background-color: var(--agent-primary-light);
            transform: translateY(-3px);
        }

        .quick-action-icon {
            font-size: 1.5rem;
            color: var(--agent-primary);
            margin-bottom: 0.75rem;
        }

        .quick-action-label {
            font-weight: 500;
            color: var(--gray-800);
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
                <div>
                    <h1 class="page-title">Landlord Dashboard</h1>
                    <p class="welcome-message">Welcome back! Here's an overview of your properties and finances.</p>
                </div>
                <div class="header-actions">
                    <a href="add_property.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Property
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo htmlspecialchars($total_properties); ?></div>
                        <div class="stat-label">Total Properties</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">$<?php echo number_format($total_payments, 2); ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($occupancy_rate, 1); ?>%</div>
                        <div class="stat-label">Occupancy Rate</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">$<?php echo number_format($monthly_revenue, 2); ?></div>
                        <div class="stat-label">This Month's Revenue</div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Property Status -->
                <div class="dashboard-card col-span-4">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-pie"></i> Property Status
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="progress-container">
                            <div class="progress-bar success" style="width: <?php echo $occupancy_rate; ?>%"></div>
                        </div>
                        <div class="progress-label">
                            <span>Occupied: <?php echo $occupied_properties; ?></span>
                            <span>Vacant: <?php echo $vacant_properties; ?></span>
                        </div>
                        
                        <div class="quick-actions">
                            <a href="properties.php?status=available" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <div class="quick-action-label">Vacant Properties</div>
                            </a>
                            <a href="properties.php?status=occupied" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fas fa-door-closed"></i>
                                </div>
                                <div class="quick-action-label">Occupied Properties</div>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Payments -->
                <div class="dashboard-card col-span-8">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-money-check-alt"></i> Recent Payments
                        </h2>
                        <a href="payment_report.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div>
                        <?php if ($recent_payments_result->num_rows > 0): ?>
                            <?php while ($payment = $recent_payments_result->fetch_assoc()): ?>
                                <div class="list-item">
                                    <div class="list-item-content">
                                        <div class="list-item-title">
                                            <?php echo htmlspecialchars($payment['title']); ?>
                                        </div>
                                        <div class="list-item-subtitle">
                                            $<?php echo number_format($payment['amount'], 2); ?> • 
                                            <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>
                                        </div>
                                    </div>
                                    <div class="list-item-action">
                                        <?php
                                            $status = $payment['status'];
                                            $status_class = '';
                                            
                                            if ($status == 'completed') {
                                                $status_class = 'completed';
                                            } elseif ($status == 'pending') {
                                                $status_class = 'pending';
                                            } elseif ($status == 'failed') {
                                                $status_class = 'failed';
                                            }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst(htmlspecialchars($status)); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <h3 class="empty-title">No recent payments</h3>
                                <p class="empty-description">
                                    There are no recent payment records to display.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Upcoming Lease Endings -->
                <div class="dashboard-card col-span-6">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-calendar-alt"></i> Leases Ending Soon
                        </h2>
                        <a href="leases.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div>
                        <?php if ($lease_result->num_rows > 0): ?>
                            <?php while ($lease = $lease_result->fetch_assoc()): ?>
                                <div class="list-item">
                                    <div class="list-item-content">
                                        <div class="list-item-title">
                                            <?php echo htmlspecialchars($lease['title']); ?>
                                        </div>
                                        <div class="list-item-subtitle">
                                            Ends: <?php echo date('M d, Y', strtotime($lease['lease_end'])); ?>
                                            (<?php echo ceil((strtotime($lease['lease_end']) - time()) / (60 * 60 * 24)); ?> days left)
                                        </div>
                                    </div>
                                    <div class="list-item-action">
                                        <a href="edit_property.php?id=<?php echo $lease['id']; ?>" class="btn btn-outline btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <h3 class="empty-title">No leases ending soon</h3>
                                <p class="empty-description">
                                    There are no leases ending in the next 60 days.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Maintenance Requests -->
                <div class="dashboard-card col-span-6">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-tools"></i> Maintenance Requests
                        </h2>
                        <a href="maintenance.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div>
                        <?php if ($maintenance_result && $maintenance_result->num_rows > 0): ?>
                            <?php while ($request = $maintenance_result->fetch_assoc()): ?>
                                <div class="list-item">
                                    <div class="list-item-content">
                                        <div class="list-item-title">
                                            <?php echo htmlspecialchars($request['title']); ?>
                                        </div>
                                        <div class="list-item-subtitle">
                                            <?php echo htmlspecialchars($request['property_title']); ?>
                                        </div>
                                    </div>
                                    <div class="list-item-action">
                                        <?php
                                            $status = $request['status'];
                                            $status_class = '';
                                            
                                            if ($status == 'completed') {
                                                $status_class = 'completed';
                                            } elseif ($status == 'pending') {
                                                $status_class = 'pending';
                                            } elseif ($status == 'urgent') {
                                                $status_class = 'urgent';
                                            } elseif ($status == 'in_progress') {
                                                $status_class = 'in_progress';
                                            }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($status))); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <h3 class="empty-title">No maintenance requests</h3>
                                <p class="empty-description">
                                    There are no maintenance requests to display.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="dashboard-card col-span-12">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="add_property.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div class="quick-action-label">Add Property</div>
                            </a>
                            <a href="payment_report.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="quick-action-label">Payment Report</div>
                            </a>
                            <a href="maintenance.php?status=urgent" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="quick-action-label">Urgent Maintenance</div>
                            </a>
                            <a href="tenants.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="quick-action-label">Manage Tenants</div>
                            </a>
                            <a href="reports.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="quick-action-label">Generate Reports</div>
                            </a>
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
    </script>
</body>
</html>