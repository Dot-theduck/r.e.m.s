<?php
// Include the database configuration
require_once 'config.php';

// Set the tenant ID (in a real app, you'd get this from the login session)
$tenant_id = 3;

// Fetch tenant information (in a real app, this would come from session or database)
$tenant = [
    'initials' => 'MC',
    'name' => 'Michael Chen',
    'role' => 'Tenant'
];

// Fetch lease information
$sql = "SELECT * FROM tenant_properties WHERE tenant_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leases | Tenant Portal</title>
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
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--tenant-primary), var(--tenant-primary-light));
            border-radius: 2px;
        }

        /* Lease Cards */
        .lease-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .lease-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .lease-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .lease-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to right, rgba(139, 92, 246, 0.05), rgba(139, 92, 246, 0.1));
        }

        .lease-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .lease-status {
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

        .lease-content {
            padding: 1.5rem;
        }

        .lease-info {
            display: grid;
            gap: 1rem;
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
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
        }

        .lease-actions {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 1rem;
            background-color: var(--gray-50);
        }

        .btn {
            padding: 0.5rem 1rem;
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
        
        /* Lease Table Styles */
        .lease-table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .lease-table-header {
            background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-dark));
            color: white;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .lease-table-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .lease-table-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .lease-table-wrapper {
            overflow-x: auto;
            padding: 1rem;
        }
        
        .lease-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .lease-table th {
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
        
        .lease-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-700);
        }
        
        .lease-table tr:last-child td {
            border-bottom: none;
        }
        
        .lease-table tr:hover td {
            background-color: var(--gray-50);
        }
        
        .lease-table a {
            color: var(--tenant-primary);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .lease-table a:hover {
            text-decoration: underline;
            color: var(--tenant-primary-dark);
        }
        
        .table-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-status-active {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .table-status-expired {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .table-status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            text-align: center;
            color: var(--gray-500);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }
        
        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }
        
        .empty-state-description {
            color: var(--gray-500);
            max-width: 400px;
            margin-bottom: 1.5rem;
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
            
            .lease-table th, 
            .lease-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
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
                    <a href="my-leases.php" class="sidebar-nav-link active">
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
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">My Leases</h1>
            </div>

            <div class="lease-table-container">
                <div class="lease-table-header">
                    <div class="lease-table-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h2 class="lease-table-title">Active and Past Lease Agreements</h2>
                </div>
                
                <div class="lease-table-wrapper">
                    <?php if ($result->num_rows > 0): ?>
                        <table class="lease-table">
                            <thead>
                                <tr>
                                    <th>Property ID</th>
                                    <th>Lease Start</th>
                                    <th>Lease End</th>
                                    <th>Monthly Rent</th>
                                    <th>Security Deposit</th>
                                    <th>Document</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['property_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['lease_start']); ?></td>
                                        <td><?php echo htmlspecialchars($row['lease_end']); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format($row['monthly_rent'], 2)); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format($row['security_deposit'], 2)); ?></td>
                                        <td>
                                            <a href="uploads/leases/<?php echo htmlspecialchars($row['lease_document']); ?>" target="_blank">
                                                <i class="fas fa-file-pdf"></i> View Lease
                                            </a>
                                        </td>
                                        <td>
                                            <?php 
                                                $statusClass = '';
                                                switch(strtolower($row['status'])) {
                                                    case 'active':
                                                        $statusClass = 'table-status-active';
                                                        break;
                                                    case 'expired':
                                                        $statusClass = 'table-status-expired';
                                                        break;
                                                    case 'pending':
                                                        $statusClass = 'table-status-pending';
                                                        break;
                                                }
                                            ?>
                                            <span class="table-status <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <h3 class="empty-state-title">No Lease Information Found</h3>
                            <p class="empty-state-description">
                                We couldn't find any lease agreements associated with your account. 
                                If you believe this is an error, please contact your property manager.
                            </p>
                            <a href="contact-agent.php" class="btn btn-primary">
                                <i class="fas fa-envelope"></i> Contact Agent
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <?php
    $stmt->close();
    $mysqli->close();
    ?>
</body>
</html>