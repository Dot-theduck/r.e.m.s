<?php
require_once '../tenant/config.php';



// 1. Total Properties
$propertyCount = 0;
$result = $mysqli->query("SELECT COUNT(*) AS total_properties FROM properties");
if ($result) {
    $propertyCount = $result->fetch_assoc()['total_properties'];
}

// 2. Total Payments
$totalPayments = 0;
$result = $mysqli->query("SELECT SUM(amount) AS total_payments FROM payment");
if ($result) {
    $totalPayments = $result->fetch_assoc()['total_payments'] ?? 0;
}

// 3. Active Leases
$activeLeases = 0;
$result = $mysqli->query("SELECT COUNT(*) AS active_leases FROM tenant_properties WHERE status = 'active'");
if ($result) {
    $activeLeases = $result->fetch_assoc()['active_leases'];
}

// 4. Total Tenants
$totalTenants = 0;
$result = $mysqli->query("SELECT COUNT(DISTINCT tenant_id) AS total_tenants FROM tenant_properties");
if ($result) {
    $totalTenants = $result->fetch_assoc()['total_tenants'];
}

// 5. Recent Payments
$recentPayments = [];
$result = $mysqli->query("
    SELECT p.id, p.amount, p.payment_date, p.payment_method, t.first_name, t.last_name, prop.title as property_name
    FROM payment p
    JOIN users t ON p.tenant_id = t.id
    JOIN properties prop ON p.property_id = prop.id
    WHERE t.user_type = 'tenant'
    ORDER BY p.payment_date DESC
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentPayments[] = $row;
    }
}

// 6. Properties with Vacancies
$vacantProperties = [];
$result = $mysqli->query("
    SELECT id, title as property_name, address, 
           (SELECT COUNT(*) FROM tenant_properties WHERE property_id = properties.id AND status = 'active') AS occupied_units,
           bedrooms + bathrooms AS total_units
    FROM properties
    HAVING total_units > occupied_units
    ORDER BY (total_units - occupied_units) DESC
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['vacant_units'] = $row['total_units'] - $row['occupied_units'];
        $vacantProperties[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | REMS</title>
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.primary {
            border-left-color: var(--agent-primary);
        }

        .stat-card.success {
            border-left-color: var(--success);
        }

        .stat-card.info {
            border-left-color: var(--primary);
        }

        .stat-card.warning {
            border-left-color: var(--warning);
        }

        .stat-title {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            font-weight: 600;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            opacity: 0.2;
        }

        .stat-card.primary .stat-icon {
            color: var(--agent-primary);
        }

        .stat-card.success .stat-icon {
            color: var(--success);
        }

        .stat-card.info .stat-icon {
            color: var(--primary);
        }

        .stat-card.warning .stat-icon {
            color: var(--warning);
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
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

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: var(--success-light);
            color: var(--success);
        }

        .badge-warning {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .badge-danger {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .badge-primary {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        /* Payment Method Styles */
        .payment-method {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .payment-method.cash {
            background-color: var(--success-light);
            color: var(--success);
        }

        .payment-method.card {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        .payment-method.bank {
            background-color: var(--agent-primary-light);
            color: var(--agent-primary-dark);
        }

        .payment-method i {
            margin-right: 0.25rem;
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

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--agent-primary-light);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.875rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--gray-500);
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

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .dashboard-row {
                flex-direction: column;
            }
            
            .dashboard-col {
                width: 100% !important;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        .dashboard-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }

        .dashboard-col {
            padding: 0 0.75rem;
            width: 50%;
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
            <h1 class="page-title">Dashboard</h1>
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=6366f1&color=fff" alt="Admin User" class="user-avatar">
                <div class="user-info">
                    <div class="user-name">Admin User</div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-title">Total Properties</div>
                <div class="stat-value"><?= $propertyCount ?></div>
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-title">Total Payments</div>
                <div class="stat-value">$<?= number_format($totalPayments, 2) ?></div>
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-title">Active Leases</div>
                <div class="stat-value"><?= $activeLeases ?></div>
                <div class="stat-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-title">Total Tenants</div>
                <div class="stat-value"><?= $totalTenants ?></div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <!-- Dashboard Tables -->
        <div class="dashboard-row">
            <!-- Recent Payments Table -->
            <div class="dashboard-col">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-money-bill-wave"></i> Recent Payments
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tenant</th>
                                        <th>Property</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recentPayments) > 0): ?>
                                        <?php foreach ($recentPayments as $payment): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></td>
                                                <td><?= htmlspecialchars($payment['property_name']) ?></td>
                                                <td>$<?= number_format($payment['amount'], 2) ?></td>
                                                <td>
                                                    <?php
                                                    $methodClass = 'bank';
                                                    $methodIcon = 'university';
                                                    
                                                    if ($payment['payment_method'] === 'cash') {
                                                        $methodClass = 'cash';
                                                        $methodIcon = 'money-bill-wave';
                                                    } elseif ($payment['payment_method'] === 'card') {
                                                        $methodClass = 'card';
                                                        $methodIcon = 'credit-card';
                                                    }
                                                    ?>
                                                    <span class="payment-method <?= $methodClass ?>">
                                                        <i class="fas fa-<?= $methodIcon ?>"></i>
                                                        <?= ucfirst($payment['payment_method']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No recent payments found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: right; margin-top: 1rem;">
                            <a href="payment_report.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View All Payments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Vacant Properties Table -->
            <div class="dashboard-col">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-door-open"></i> Properties with Vacancies
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($vacantProperties) > 0): ?>
                                        <?php foreach ($vacantProperties as $property): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($property['property_name']) ?></td>
                                                <td><?= htmlspecialchars($property['address']) ?></td>
                                                
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No vacant properties found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: right; margin-top: 1rem;">
                            <a href="properties.php?filter=vacant" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View All Vacancies
                            </a>
                        </div>
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