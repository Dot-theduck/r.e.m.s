<?php
// Ensure this is at the top and only included ONCE
require_once '../../tenant/config.php';

// Now $mysqli is defined here
$landlordId = 2;

$sql = "SELECT 
            p.id AS payment_id,
            pr.title AS property_title,
            p.amount,
            p.payment_date,
            p.payment_method,
            p.transaction_reference,
            p.status,
            p.notes
        FROM 
            payments p
        JOIN 
            properties pr ON p.tenant_property_id = pr.id
        WHERE 
            pr.landlord_id = ?
        ORDER BY 
            p.payment_date DESC";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error); // Add this to help debug if needed
}
$stmt->bind_param("i", $landlordId);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total payments
$totalPayments = 0;
$successfulPayments = 0;
$pendingPayments = 0;

if ($result && $result->num_rows > 0) {
    // Store the results in an array so we can reuse them
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
        $totalPayments += $row['amount'];
        if ($row['status'] === 'Successful') {
            $successfulPayments += $row['amount'];
        } elseif ($row['status'] === 'Pending') {
            $pendingPayments += $row['amount'];
        }
    }
    // Reset the result pointer
    $result->data_seek(0);
} else {
    $payments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report | Agent Portal</title>
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

        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.5rem;
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
        }

        .stat-title {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .stat-icon {
            margin-bottom: 1rem;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.blue {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        .stat-icon.green {
            background-color: var(--success-light);
            color: var(--success);
        }

        .stat-icon.yellow {
            background-color: var(--warning-light);
            color: var(--warning);
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

        /* Print styles */
        @media print {
            .sidebar, .mobile-menu-toggle, .header-actions {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            
            body {
                background-color: white !important;
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
                <a href="dashboard.php" class="sidebar-nav-link">
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
                <a href="payment_report.php" class="sidebar-nav-link active">
                    <i class="fas fa-money-bill-wave sidebar-nav-icon"></i>
                    Payments
                </a>
            </div>
            <div class="sidebar-nav-item">
                <a href="users.php" class="sidebar-nav-link">
                    <i class="fas fa-users sidebar-nav-icon"></i>
                    Users
                </a>
            </div>
            <div class="sidebar-nav-item">
                <a href="reports.php" class="sidebar-nav-link">
                    <i class="fas fa-chart-bar sidebar-nav-icon"></i>
                    Reports
                </a>
            </div>
            <div class="sidebar-nav-item">
                <a href="settings.php" class="sidebar-nav-link">
                    <i class="fas fa-cog sidebar-nav-icon"></i>
                    Settings
                </a>
            </div>
            <div class="sidebar-nav-item">
                <a href="logout.php" class="sidebar-nav-link">
                    <i class="fas fa-sign-out-alt sidebar-nav-icon"></i>
                    Logout
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Payment Report</h1>
            <div class="header-actions">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <a href="export_payments.php" class="btn btn-primary">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-title">Total Payments</div>
                <div class="stat-value">$<?= number_format($totalPayments, 2) ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-title">Successful Payments</div>
                <div class="stat-value">$<?= number_format($successfulPayments, 2) ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-title">Pending Payments</div>
                <div class="stat-value">$<?= number_format($pendingPayments, 2) ?></div>
            </div>
        </div>

        <!-- Payment Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-list"></i> Payment Records
                </h2>
            </div>
            <div class="card-body">
                <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Property</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Method</th>
                                    <th>Transaction Ref</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $count = 1; foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= htmlspecialchars($payment['property_title']) ?></td>
                                    <td>$<?= number_format($payment['amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                                    <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                                    <td><?= htmlspecialchars($payment['transaction_reference']) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'badge-success';
                                        if ($payment['status'] === 'Pending') {
                                            $statusClass = 'badge-warning';
                                        } elseif ($payment['status'] === 'Failed') {
                                            $statusClass = 'badge-danger';
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($payment['status']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($payment['notes']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No payment records found for this landlord.
                    </div>
                <?php endif; ?>
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

<?php
$stmt->close();
$mysqli->close();
?>