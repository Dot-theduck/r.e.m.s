<?php
require_once '../tenant/config.php';

// Handle CREATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_payment'])) {
    $tenant_property_id = $_POST['tenant_property_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];
    $transaction_reference = $_POST['transaction_reference'];
    $notes = $_POST['notes'];

    $stmt = $mysqli->prepare("INSERT INTO payments (tenant_property_id, amount, payment_date, payment_method, status, transaction_reference, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idsssss", $tenant_property_id, $amount, $payment_date, $payment_method, $status, $transaction_reference, $notes);
    
    if ($stmt->execute()) {
        $success_message = "Payment added successfully!";
    } else {
        $error_message = "Error adding payment: " . $stmt->error;
    }
    $stmt->close();
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($mysqli->query("DELETE FROM payments WHERE id = $id")) {
        $success_message = "Payment deleted successfully!";
    } else {
        $error_message = "Error deleting payment: " . $mysqli->error;
    }
}

// Handle UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_payment'])) {
    $id = $_POST['id'];
    $tenant_property_id = $_POST['tenant_property_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];
    $transaction_reference = $_POST['transaction_reference'];
    $notes = $_POST['notes'];

    $stmt = $mysqli->prepare("UPDATE payments SET tenant_property_id=?, amount=?, payment_date=?, payment_method=?, status=?, transaction_reference=?, notes=? WHERE id=?");
    $stmt->bind_param("idsssssi", $tenant_property_id, $amount, $payment_date, $payment_method, $status, $transaction_reference, $notes, $id);
    
    if ($stmt->execute()) {
        $success_message = "Payment updated successfully!";
    } else {
        $error_message = "Error updating payment: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all payment records
$result = $mysqli->query("
    SELECT p.*, 
           CONCAT(u.first_name, ' ', u.last_name) AS tenant_name,
           pr.title AS property_title
    FROM payments p
    LEFT JOIN tenant_properties tp ON p.tenant_property_id = tp.id
    LEFT JOIN users u ON tp.tenant_id = u.id
    LEFT JOIN properties pr ON tp.property_id = pr.id
    WHERE u.user_type = 'tenant'
    ORDER BY p.payment_date DESC
");

$payments = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}

// Get tenant properties for dropdown
$tenant_properties_result = $mysqli->query("
    SELECT tp.id, 
           CONCAT(u.first_name, ' ', u.last_name) AS tenant_name,
           pr.title AS property_title
    FROM tenant_properties tp
    JOIN users u ON tp.tenant_id = u.id
    JOIN properties pr ON tp.property_id = pr.id
    WHERE u.user_type = 'tenant'
    ORDER BY u.last_name, u.first_name
");

$tenant_properties = [];
if ($tenant_properties_result) {
    while ($row = $tenant_properties_result->fetch_assoc()) {
        $tenant_properties[$row['id']] = $row['tenant_name'] . ' - ' . $row['property_title'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management | REMS</title>
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

        .btn-secondary {
            background-color: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background-color: var(--gray-300);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e69009;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
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

        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            background-color: var(--agent-primary-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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

        .form-textarea {
            min-height: 100px;
            resize: vertical;
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
            text-transform: capitalize;
        }

        .badge-pending {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .badge-completed {
            background-color: var(--success-light);
            color: var(--success);
        }

        .badge-failed {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .badge-refunded {
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

        .payment-method.check {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .payment-method.credit_card {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        .payment-method.bank_transfer {
            background-color: var(--agent-primary-light);
            color: var(--agent-primary-dark);
        }

        .payment-method i {
            margin-right: 0.25rem;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            border-left: 4px solid transparent;
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
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
            }
        }

        /* Toggle Form */
        #addPaymentForm {
            display: none;
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
            <h1 class="page-title">Payment Management</h1>
            <div class="header-actions">
                <button type="button" class="btn btn-primary" onclick="toggleAddPaymentForm()">
                    <i class="fas fa-plus"></i> Add New Payment
                </button>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Add Payment Form Card -->
        <div class="card" id="addPaymentForm">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-plus-circle"></i> Add New Payment
                </h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tenant_property_id" class="form-label">Tenant & Property</label>
                            <select name="tenant_property_id" id="tenant_property_id" class="form-select" required>
                                <option value="">Select Tenant & Property</option>
                                <?php foreach ($tenant_properties as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount" class="form-label">Amount ($)</label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control" placeholder="0.00" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="transaction_reference" class="form-label">Transaction Reference</label>
                            <input type="text" name="transaction_reference" id="transaction_reference" class="form-control" placeholder="e.g., Receipt #12345">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="notes" class="form-label">Notes (optional)</label>
                            <textarea name="notes" id="notes" class="form-control form-textarea" placeholder="Add any additional information about this payment"></textarea>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddPaymentForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="add_payment" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Records Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-list"></i> Payment Records
                </h2>
                <span class="badge" style="background-color: var(--gray-200); color: var(--gray-700);">
                    <?php echo count($payments); ?> Records
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tenant</th>
                                <th>Property</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Reference</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($payments) > 0): ?>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo $payment['id']; ?></td>
                                        <td><?php echo htmlspecialchars($payment['tenant_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['property_title'] ?? 'N/A'); ?></td>
                                        <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                        <td>
                                            <?php
                                            $methodClass = $payment['payment_method'];
                                            $methodIcon = 'money-bill-wave';
                                            
                                            if ($payment['payment_method'] === 'check') {
                                                $methodIcon = 'money-check';
                                            } elseif ($payment['payment_method'] === 'credit_card') {
                                                $methodIcon = 'credit-card';
                                            } elseif ($payment['payment_method'] === 'bank_transfer') {
                                                $methodIcon = 'university';
                                            }
                                            ?>
                                            <span class="payment-method <?php echo $methodClass; ?>">
                                                <i class="fas fa-<?php echo $methodIcon; ?>"></i>
                                                <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $payment['status']; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($payment['transaction_reference'] ?: 'N/A'); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <button type="button" class="btn btn-sm btn-warning" onclick="openEditModal(<?php echo $payment['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?delete=<?php echo $payment['id']; ?>" onclick="return confirm('Are you sure you want to delete this payment record?')" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div style="padding: 2rem 1rem; text-align: center;">
                                            <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                                            <p>No payment records found. Add your first payment using the form above.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Payment Modal (Hidden by default) -->
        <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
            <div style="background-color: white; margin: 2rem auto; max-width: 800px; border-radius: var(--border-radius); box-shadow: var(--shadow-lg);">
                <div style="padding: 1.25rem; border-bottom: 1px solid var(--gray-200); background-color: var(--agent-primary-light); display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--agent-primary-dark);">
                        <i class="fas fa-edit"></i> Edit Payment
                    </h2>
                    <button type="button" onclick="closeEditModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--gray-600);">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div style="padding: 1.5rem;">
                    <form method="POST" id="editForm">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="edit_tenant_property_id" class="form-label">Tenant & Property</label>
                                <select name="tenant_property_id" id="edit_tenant_property_id" class="form-select" required>
                                    <?php foreach ($tenant_properties as $id => $name): ?>
                                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_amount" class="form-label">Amount ($)</label>
                                <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_payment_date" class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" id="edit_payment_date" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_payment_method" class="form-label">Payment Method</label>
                                <select name="payment_method" id="edit_payment_method" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_status" class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_transaction_reference" class="form-label">Transaction Reference</label>
                                <input type="text" name="transaction_reference" id="edit_transaction_reference" class="form-control">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="edit_notes" class="form-label">Notes</label>
                                <textarea name="notes" id="edit_notes" class="form-control form-textarea"></textarea>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                            <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" name="update_payment" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Payment
                            </button>
                        </div>
                    </form>
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

    // Toggle Add Payment Form
    function toggleAddPaymentForm() {
        const form = document.getElementById('addPaymentForm');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        } else {
            form.style.display = 'none';
        }
    }

    // Edit Payment Modal Functions
    function openEditModal(paymentId) {
        // In a real application, you would fetch the payment data via AJAX
        // For this example, we'll use the data already in the table
        const payments = <?php echo json_encode($payments); ?>;
        const payment = payments.find(p => p.id == paymentId);
        
        if (payment) {
            document.getElementById('edit_id').value = payment.id;
            document.getElementById('edit_tenant_property_id').value = payment.tenant_property_id;
            document.getElementById('edit_amount').value = payment.amount;
            document.getElementById('edit_payment_date').value = payment.payment_date;
            document.getElementById('edit_payment_method').value = payment.payment_method;
            document.getElementById('edit_status').value = payment.status;
            document.getElementById('edit_transaction_reference').value = payment.transaction_reference;
            document.getElementById('edit_notes').value = payment.notes;
            
            document.getElementById('editModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
        }
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.body.style.overflow = 'auto'; // Re-enable scrolling
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('editModal');
        if (event.target === modal) {
            closeEditModal();
        }
    });

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let valid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = 'var(--danger)';
                        valid = false;
                    } else {
                        field.style.borderColor = '';
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
        });
    });
</script>
</body>
</html>

