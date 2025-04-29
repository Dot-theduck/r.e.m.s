<?php
// Include the database config
require_once 'config.php';

// Set tenant ID (normally from login/session, here hardcoded)
$tenant_id = 3;

// Fetch tenant information (in a real app, this would come from session or database)
$tenant = [
    'initials' => 'MC',
    'name' => 'Michael Chen',
    'role' => 'Tenant'
];

// Fetch lease information
$lease_sql = "SELECT * FROM tenant_properties WHERE tenant_id = ?";
$lease_stmt = $mysqli->prepare($lease_sql);
if (!$lease_stmt) {
    die("Lease prepare failed: " . $mysqli->error);
}
$lease_stmt->bind_param("i", $tenant_id);
$lease_stmt->execute();
$lease_result = $lease_stmt->get_result();

// Fetch recent maintenance requests
$request_sql = "SELECT * FROM maintenance_requests WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 5";
$request_stmt = $mysqli->prepare($request_sql);
$request_stmt->bind_param("i", $tenant_id);
$request_stmt->execute();
$request_result = $request_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard | REMS</title>
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

        /* Dashboard Header */
        .dashboard-header {
            margin-bottom: 2rem;
        }

        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: var(--gray-600);
            font-size: 1.1rem;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Dashboard Cards */
        .dashboard-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(to right, rgba(139, 92, 246, 0.05), rgba(139, 92, 246, 0.1));
        }

        .card-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: var(--tenant-primary-light);
            color: var(--tenant-primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .card-content {
            padding: 1.5rem;
        }

        /* Lease Info Styles */
        .lease-info {
            display: grid;
            gap: 1rem;
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px dashed var(--gray-200);
        }

        .info-group:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-weight: 500;
            color: var(--gray-800);
            font-size: 1.1rem;
        }

        .lease-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-expired {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .card-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            background-color: var(--gray-50);
            display: flex;
            justify-content: flex-start;
        }

        /* Maintenance Request Table */
        .request-table {
            width: 100%;
            border-collapse: collapse;
        }

        .request-table th {
            background-color: var(--gray-50);
            color: var(--gray-700);
            font-weight: 600;
            text-align: left;
            padding: 1rem;
            border-bottom: 2px solid var(--gray-200);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .request-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-700);
        }

        .request-table tr:last-child td {
            border-bottom: none;
        }

        .request-table tr:hover td {
            background-color: var(--gray-50);
        }

        .urgency-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .urgency-low {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .urgency-medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .urgency-high {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .urgency-emergency {
            background-color: rgba(127, 29, 29, 0.1);
            color: #7f1d1d;
            border: 1px solid rgba(127, 29, 29, 0.2);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-new {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .status-in-progress {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-completed {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
            color: var(--gray-500);
        }

        .empty-state-icon {
            font-size: 2.5rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .empty-state-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .empty-state-description {
            color: var(--gray-500);
            max-width: 400px;
            margin-bottom: 1.5rem;
        }

        /* Buttons */
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
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-dark));
            color: white;
            box-shadow: 0 2px 4px rgba(139, 92, 246, 0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--tenant-primary-dark), var(--tenant-primary-dark));
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(139, 92, 246, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--gray-300);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            background-color: var(--gray-100);
            border-color: var(--gray-400);
        }

        .btn-link {
            color: var(--tenant-primary);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0;
        }

        .btn-link:hover {
            text-decoration: underline;
            color: var(--tenant-primary-dark);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .action-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background-color: var(--tenant-primary-light);
            color: var(--tenant-primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .action-title {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .action-description {
            color: var(--gray-600);
            font-size: 0.875rem;
            margin-bottom: 1rem;
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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .request-table th,
            .request-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }

            .request-table th:nth-child(4),
            .request-table td:nth-child(4) {
                display: none;
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
                    <a href="dashboard.php" class="sidebar-nav-link active">
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
                    <a href="contact-agent.php" class="sidebar-nav-link">
                        <i class="fas fa-envelope sidebar-nav-icon"></i>
                        Contact Agent
                    </a>
                </div>
            </nav>
            <!-- Profile Section -->
            <div class="sidebar-profile">
                <div class="profile-info">
                    <div class="profile-avatar"><?php echo $tenant['initials']; ?></div>
                    <div class="profile-details">
                        <div class="profile-name"><?php echo $tenant['name']; ?></div>
                        <div class="profile-role"><?php echo $tenant['role']; ?></div>
                    </div>
                </div>
    
                <form action="../index.php" method="post" style="margin-top: 0;">
                    <button type="submit" 
                        style="
                            width: 100%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 0.5rem;
                            background: linear-gradient(90deg, var(--tenant-primary), var(--tenant-primary-dark));
                            color: #fff;
                            border: none;
                            border-radius: 0.375rem;
                            padding: 0.75rem 0;
                            font-size: 1rem;
                            font-weight: 600;
                            cursor: pointer;
                            box-shadow: var(--shadow-sm);
                            transition: background 0.2s, box-shadow 0.2s;
                        "
                        onmouseover="this.style.background='linear-gradient(90deg, #7c3aed, #8b5cf6)'; this.style.boxShadow='var(--shadow-md)';"
                        onmouseout="this.style.background='linear-gradient(90deg, var(--tenant-primary), var(--tenant-primary-dark))'; this.style.boxShadow='var(--shadow-sm)';"
                    >
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="welcome-title">Welcome, <?php echo $tenant['name']; ?>!</h1>
                <p class="welcome-subtitle">Here's an overview of your rental information and recent requests.</p>
            </div>

            <div class="dashboard-grid">
                <!-- Current Lease Information -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h2 class="card-title">Current Lease Information</h2>
                    </div>
                    <div class="card-content">
                        <?php if ($lease_result->num_rows > 0): ?>
                            <?php $lease = $lease_result->fetch_assoc(); ?>
                            <div class="lease-info">
                                <div class="info-group">
                                    <span class="info-label">Property ID</span>
                                    <span class="info-value"><?php echo htmlspecialchars($lease['property_id']); ?></span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Lease Period</span>
                                    <span class="info-value">
                                        <?php echo htmlspecialchars($lease['lease_start']); ?> to <?php echo htmlspecialchars($lease['lease_end']); ?>
                                    </span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Monthly Rent</span>
                                    <span class="info-value">$<?php echo htmlspecialchars(number_format($lease['monthly_rent'], 2)); ?></span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Status</span>
                                    <span class="lease-status status-<?php echo strtolower($lease['status']); ?>">
                                        <?php echo htmlspecialchars($lease['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-file-contract"></i>
                                </div>
                                <h3 class="empty-state-title">No Lease Information</h3>
                                <p class="empty-state-description">
                                    We couldn't find any lease information for your account.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($lease_result->num_rows > 0): ?>
                        <div class="card-footer">
                            <a href="uploads/leases/<?php echo htmlspecialchars($lease['lease_document']); ?>" target="_blank" class="btn-link">
                                <i class="fas fa-file-pdf"></i> View Lease Document
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Maintenance Requests -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h2 class="card-title">Recent Maintenance Requests</h2>
                    </div>
                    <div class="card-content">
                        <?php if ($request_result->num_rows > 0): ?>
                            <div class="request-table-wrapper">
                                <table class="request-table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Urgency</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($request = $request_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($request['title']); ?></td>
                                                <td>
                                                    <span class="urgency-badge urgency-<?php echo strtolower($request['urgency']); ?>">
                                                        <?php echo ucfirst(htmlspecialchars($request['urgency'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $request['status'])); ?>">
                                                        <?php echo ucfirst(htmlspecialchars($request['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($request['created_at']))); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <h3 class="empty-state-title">No Maintenance Requests</h3>
                                <p class="empty-state-description">
                                    You haven't submitted any maintenance requests yet.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="contact-agent.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Submit New Request
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <h2 style="margin-bottom: 1rem; color: var(--gray-800);">Quick Actions</h2>
            <div class="quick-actions">
                <a href="my-leases.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h3 class="action-title">View All Leases</h3>
                    <p class="action-description">
                        Access all your current and past lease agreements
                    </p>
                </a>
                <a href="view-properties.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="action-title">Browse Properties</h3>
                    <p class="action-description">
                        Explore available properties for rent
                    </p>
                </a>
                <a href="contact-agent.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="action-title">Contact Agent</h3>
                    <p class="action-description">
                        Get in touch with your property manager
                    </p>
                </a>
                <a href="contact-agent.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="action-title">Request Maintenance</h3>
                    <p class="action-description">
                        Submit a new maintenance request
                    </p>
                </a>
            </div>
        </main>
    </div>

    <?php
    // Close statements and connection
    $lease_stmt->close();
    $request_stmt->close();
    $mysqli->close();
    ?>
</body>
</html>