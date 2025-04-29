<?php
require_once '../../rems/tenant/config.php';

// Check if user is logged in (you can implement your authentication logic here)
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
//     header('Location: login.php');
//     exit();
// }

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
    SELECT p.id, p.amount, p.payment_date, p.payment_method, t.first_name, t.last_name, prop.property_name
    FROM payment p
    JOIN tenants t ON p.tenant_id = t.id
    JOIN properties prop ON p.property_id = prop.id
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
    SELECT id, property_name, address, units, 
           (units - (SELECT COUNT(*) FROM tenant_properties WHERE property_id = properties.id AND status = 'active')) AS vacant_units
    FROM properties
    HAVING vacant_units > 0
    ORDER BY vacant_units DESC
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vacantProperties[] = $row;
    }
}

// Get current page for sidebar active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REMS - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
            --sidebar-width: 250px;
            --sidebar-width-collapsed: 70px;
            --topbar-height: 70px;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            color: rgba(255, 255, 255, 0.8);
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow-y: auto;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }
        
        .sidebar-brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: white;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            background: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand-icon {
            margin-right: 0.5rem;
        }
        
        .sidebar.collapsed .sidebar-brand-text {
            display: none;
        }
        
        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 0 1rem 1rem;
        }
        
        .sidebar-heading {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.13rem;
            padding: 0 1rem;
            margin-bottom: 0.5rem;
        }
        
        .sidebar.collapsed .sidebar-heading {
            display: none;
        }
        
        .nav-item {
            position: relative;
            margin-bottom: 0.25rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        
        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            font-weight: 700;
        }
        
        .nav-link i {
            font-size: 1rem;
            margin-right: 0.75rem;
            color: rgba(255, 255, 255, 0.3);
            transition: all 0.2s;
            width: 1.5rem;
            text-align: center;
        }
        
        .nav-link:hover i, .nav-link.active i {
            color: white;
        }
        
        .sidebar.collapsed .nav-link-text {
            display: none;
        }
        
        .sidebar-toggle {
            position: absolute;
            right: -1rem;
            top: calc(var(--topbar-height) / 2);
            height: 2rem;
            width: 2rem;
            background-color: #4e73df;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 1001;
            transition: all 0.3s;
        }
        
        .sidebar-toggle:hover {
            background-color: #224abe;
        }
        
        /* Content Wrapper */
        .content-wrapper {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            transition: all 0.3s;
            min-height: 100vh;
        }
        
        .content-wrapper.expanded {
            margin-left: var(--sidebar-width-collapsed);
        }
        
        /* Topbar */
        .topbar {
            height: var(--topbar-height);
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 0.35rem;
        }
        
        .topbar-divider {
            width: 0;
            border-right: 1px solid #e3e6f0;
            height: calc(var(--topbar-height) - 2rem);
            margin: auto 1rem;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.5rem;
            border: 2px solid #4e73df;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 700;
            font-size: 0.85rem;
            color: #5a5c69;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: #858796;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.25);
        }
        
        .card-stat {
            position: relative;
            padding: 1.5rem;
            overflow: hidden;
            border-left: 0.25rem solid;
        }
        
        .card-stat.primary {
            border-left-color: var(--primary);
        }
        
        .card-stat.success {
            border-left-color: var(--success);
        }
        
        .card-stat.info {
            border-left-color: var(--info);
        }
        
        .card-stat.warning {
            border-left-color: var(--warning);
        }
        
        .card-stat-title {
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 0.25rem;
            letter-spacing: 0.1rem;
        }
        
        .card-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0;
        }
        
        .card-stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            opacity: 0.3;
        }
        
        .card-stat.primary .card-stat-icon {
            color: var(--primary);
        }
        
        .card-stat.success .card-stat-icon {
            color: var(--success);
        }
        
        .card-stat.info .card-stat-icon {
            color: var(--info);
        }
        
        .card-stat.warning .card-stat-icon {
            color: var(--warning);
        }
        
        /* Tables */
        .table-card {
            margin-bottom: 1.5rem;
        }
        
        .table-card .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 700;
            color: var(--dark);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #f8f9fc;
            font-weight: 700;
            color: var(--dark);
            border-top: none;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge-vacant {
            background-color: var(--danger);
            color: white;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 10rem;
        }
        
        .payment-method {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .payment-method.cash {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success);
        }
        
        .payment-method.card {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary);
        }
        
        .payment-method.bank {
            background-color: rgba(54, 185, 204, 0.1);
            color: var(--info);
        }
        
        .payment-method i {
            margin-right: 0.25rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                box-shadow: none;
            }
            
            .sidebar.mobile-show {
                width: var(--sidebar-width);
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            }
            
            .content-wrapper {
                margin-left: 0;
            }
            
            .content-wrapper.expanded {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                right: auto;
                left: 1rem;
                top: 1rem;
            }
            
            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }
            
            .mobile-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="sidebar-brand-text">REMS Admin</div>
        </div>
        
        <hr class="sidebar-divider">
        
        <div class="sidebar-heading">Core</div>
        
        <div class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span class="nav-link-text">Dashboard</span>
            </a>
        </div>
        
        <hr class="sidebar-divider">
        
        <div class="sidebar-heading">Management</div>
        
        <div class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'landlord_management.php' ? 'active' : ''; ?>" href="landlord_management.php">
                <i class="fas fa-fw fa-user-tie"></i>
                <span class="nav-link-text">Landlord Management</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'agent_management.php' ? 'active' : ''; ?>" href="agent_management.php">
                <i class="fas fa-fw fa-user-tag"></i>
                <span class="nav-link-text">Agent Management</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'tenant_management.php' ? 'active' : ''; ?>" href="tenant_management.php">
                <i class="fas fa-fw fa-users"></i>
                <span class="nav-link-text">Tenant Management</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'properties.php' ? 'active' : ''; ?>" href="properties.php">
                <i class="fas fa-fw fa-home"></i>
                <span class="nav-link-text">Properties</span>
            </a>
        </div>
        
        <hr class="sidebar-divider">
        
        <div class="sidebar-heading">Finance</div>
        
        <div class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'payments.php' ? 'active' : ''; ?>" href="payments.php">
                <i class="fas fa-fw fa-money-bill-wave"></i>
                <span class="nav-link-text">Payments</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                <i class="fas fa-fw fa-chart-bar"></i>
                <span class="nav-link-text">Reports</span>
            </a>
        </div>
        
        <hr class="sidebar-divider">
        
        <div class="sidebar-heading">Account</div>
        
        <div class="nav-item">
            <a class="nav-link" href="logout.php">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span class="nav-link-text">Logout</span>
            </a>
        </div>
        
        <div class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-left"></i>
        </div>
    </div>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper" id="contentWrapper">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <button class="btn btn-link d-md-none" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="h3 mb-0 text-gray-800 d-none d-sm-inline-block">Dashboard</h1>
            </div>
            
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=4e73df&color=fff" alt="Admin User">
                <div class="user-info">
                    <div class="user-name">Admin User</div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Content -->
        <div class="dashboard-cards">
            <div class="card card-stat primary">
                <div class="card-stat-title">Total Properties</div>
                <div class="card-stat-value"><?= $propertyCount ?></div>
                <div class="card-stat-icon">
                    <i class="fas fa-home"></i>
                </div>
            </div>
            
            <div class="card card-stat success">
                <div class="card-stat-title">Total Payments</div>
                <div class="card-stat-value">$<?= number_format($totalPayments, 2) ?></div>
                <div class="card-stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            
            <div class="card card-stat info">
                <div class="card-stat-title">Active Leases</div>
                <div class="card-stat-value"><?= $activeLeases ?></div>
                <div class="card-stat-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
            </div>
            
            <div class="card card-stat warning">
                <div class="card-stat-title">Total Tenants</div>
                <div class="card-stat-value"><?= $totalTenants ?></div>
                <div class="card-stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Payments Table -->
            <div class="col-lg-6">
                <div class="card table-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-money-bill-wave me-1"></i>
                        Recent Payments
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tenant</th>
                                        <th>Property</th>
                                        <th>Amount</th>
                                        <th>Date</th>
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
                                                <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
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
                                            <td colspan="5" class="text-center">No recent payments found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="payments.php" class="btn btn-sm btn-primary">View All Payments</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Vacant Properties Table -->
            <div class="col-lg-6">
                <div class="card table-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-door-open me-1"></i>
                        Properties with Vacancies
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Address</th>
                                        <th>Total Units</th>
                                        <th>Vacant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($vacantProperties) > 0): ?>
                                        <?php foreach ($vacantProperties as $property): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($property['property_name']) ?></td>
                                                <td><?= htmlspecialchars($property['address']) ?></td>
                                                <td><?= $property['units'] ?></td>
                                                <td>
                                                    <span class="badge-vacant">
                                                        <?= $property['vacant_units'] ?> vacant
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No vacant properties found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="properties.php?filter=vacant" class="btn btn-sm btn-primary">View All Vacancies</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const contentWrapper = document.getElementById('contentWrapper');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileToggle = document.getElementById('mobileToggle');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const toggleIcon = sidebarToggle.querySelector('i');
            
            // Check for saved state
            const sidebarState = localStorage.getItem('sidebarState');
            if (sidebarState === 'collapsed') {
                sidebar.classList.add('collapsed');
                contentWrapper.classList.add('expanded');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            }
            
            // Desktop sidebar toggle
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                contentWrapper.classList.toggle('expanded');
                
                if (sidebar.classList.contains('collapsed')) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                    localStorage.setItem('sidebarState', 'collapsed');
                } else {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                    localStorage.setItem('sidebarState', 'expanded');
                }
            });
            
            // Mobile sidebar toggle
            if (mobileToggle) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('mobile-show');
                    mobileOverlay.classList.toggle('show');
                });
            }
            
            // Close sidebar when clicking overlay
            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-show');
                mobileOverlay.classList.remove('show');
            });
            
            // Close sidebar on mobile when window resizes to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('mobile-show');
                    mobileOverlay.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>