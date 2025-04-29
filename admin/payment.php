<?php
require_once '../../rems/tenant/config.php';

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
    $stmt->execute();
    $stmt->close();
    
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $mysqli->query("DELETE FROM payments WHERE id = $id");
    
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
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
    $stmt->execute();
    $stmt->close();
    
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Fetch all payment records
$result = $mysqli->query("SELECT * FROM payments ORDER BY payment_date DESC");

// Get tenant names for dropdown (assuming you have a tenants table)
$tenants_result = $mysqli->query("SELECT tp.id, t.name FROM tenant_properties tp 
                                 JOIN tenants t ON tp.tenant_id = t.id 
                                 ORDER BY t.name");
$tenant_options = [];
if ($tenants_result) {
    while ($tenant = $tenants_result->fetch_assoc()) {
        $tenant_options[$tenant['id']] = $tenant['name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f94144;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border: #dee2e6;
            --shadow: rgba(0, 0, 0, 0.1);
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: #f0f4f8;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* Layout */
        .layout {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: #2c3e50;
            color: white;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            height: var(--header-height);
        }
        
        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
            list-style: none;
        }
        
        .sidebar-item {
            margin-bottom: 5px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid var(--primary);
        }
        
        .sidebar-icon {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-collapsed .sidebar-text {
            display: none;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }
        
        .main-content-expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: white;
            box-shadow: 0 2px 10px var(--shadow);
            padding: 0 20px;
            margin-bottom: 30px;
            height: var(--header-height);
            display: flex;
            align-items: center;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        
        h1 {
            color: var(--primary);
            font-weight: 600;
            margin: 0;
            font-size: 1.8rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px var(--shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px;
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-col {
            flex: 1;
            padding: 0 10px;
            min-width: 200px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 10px 20px;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 4px;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .btn-primary {
            color: white;
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
        }
        
        .btn-warning {
            color: white;
            background-color: var(--warning);
            border-color: var(--warning);
        }
        
        .btn-warning:hover {
            background-color: #e67e00;
        }
        
        .btn-danger {
            color: white;
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-danger:hover {
            background-color: #e71d23;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
        }
        
        .btn-icon {
            margin-right: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 12px 15px;
            font-weight: 600;
            color: var(--gray);
            border-bottom: 2px solid var(--border);
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        
        tr:hover {
            background-color: rgba(67, 97, 238, 0.03);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50px;
        }
        
        .badge-pending {
            background-color: #ffd166;
            color: #805b10;
        }
        
        .badge-completed {
            background-color: #06d6a0;
            color: #014d3b;
        }
        
        .badge-failed {
            background-color: #ef476f;
            color: #7a1330;
        }
        
        .badge-refunded {
            background-color: #118ab2;
            color: #073a4a;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .edit-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        
        .edit-form input,
        .edit-form select {
            padding: 6px 10px;
            font-size: 0.9rem;
        }
        
        .edit-form .form-group {
            margin-bottom: 0;
            flex: 1;
            min-width: 120px;
        }
        
        .payment-method-icon {
            margin-right: 5px;
        }
        
        .amount-display {
            font-weight: 600;
            color: var(--primary);
        }
        
        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }
            
            .sidebar-text {
                display: none;
            }
            
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }
            
            .sidebar-expanded {
                width: var(--sidebar-width);
                z-index: 1050;
            }
            
            .sidebar-expanded .sidebar-text {
                display: inline;
            }
            
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1040;
            }
            
            .overlay-visible {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .form-col {
                flex: 100%;
            }
            
            .edit-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .edit-form .form-group {
                width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .header-content h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="#" class="sidebar-logo">
                    <i class="fas fa-building"></i>
                    <span class="sidebar-text">REMS Admin</span>
                </a>
                <button class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="../dashboard/index.php" class="sidebar-link">
                        <i class="fas fa-tachometer-alt sidebar-icon"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="../tenant/index.php" class="sidebar-link">
                        <i class="fas fa-users sidebar-icon"></i>
                        <span class="sidebar-text">Tenant Management</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="../agent/index.php" class="sidebar-link">
                        <i class="fas fa-user-tie sidebar-icon"></i>
                        <span class="sidebar-text">Agent Management</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="index.php" class="sidebar-link active">
                        <i class="fas fa-money-bill-wave sidebar-icon"></i>
                        <span class="sidebar-text">Payment</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="../properties/index.php" class="sidebar-link">
                        <i class="fas fa-home sidebar-icon"></i>
                        <span class="sidebar-text">Properties</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="../logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt sidebar-icon"></i>
                    <span class="sidebar-text">Log Out</span>
                </a>
            </div>
        </aside>
        
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>
        
        <!-- Main Content -->
        <main class="main-content" id="main-content">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-money-bill-wave"></i> Payment Management</h1>
                    <div class="header-actions">
                        <span class="badge" style="background-color: #e9ecef; color: var(--dark);">
                            <i class="fas fa-calendar"></i> <?= date('F j, Y') ?>
                        </span>
                    </div>
                </div>
            </header>

            <div class="container">
                <!-- Add New Payment -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-plus-circle"></i> Add New Payment</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="tenant_property_id">Tenant Property</label>
                                        <select name="tenant_property_id" id="tenant_property_id" required>
                                            <?php if (!empty($tenant_options)): ?>
                                                <?php foreach ($tenant_options as $id => $name): ?>
                                                    <option value="<?= $id ?>"><?= htmlspecialchars($name) ?> (ID: <?= $id ?>)</option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="">Select Tenant Property</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="amount">Amount</label>
                                        <div style="position: relative;">
                                            <input type="number" step="0.01" name="amount" id="amount" placeholder="0.00" required>
                                            <span style="position: absolute; left: 12px; top: 10px;">$</span>
                                            <style>
                                                #amount { padding-left: 25px; }
                                            </style>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="payment_date">Payment Date</label>
                                        <input type="date" name="payment_date" id="payment_date" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="payment_method">Payment Method</label>
                                        <select name="payment_method" id="payment_method" required>
                                            <option value="cash">Cash</option>
                                            <option value="check">Check</option>
                                            <option value="credit_card">Credit Card</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" required>
                                            <option value="pending">Pending</option>
                                            <option value="completed">Completed</option>
                                            <option value="failed">Failed</option>
                                            <option value="refunded">Refunded</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="transaction_reference">Transaction Reference</label>
                                        <input type="text" name="transaction_reference" id="transaction_reference" placeholder="e.g., Receipt #12345">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Notes (optional)</label>
                                <textarea name="notes" id="notes" placeholder="Add any additional information about this payment"></textarea>
                            </div>
                            
                            <button type="submit" name="add_payment" class="btn btn-primary">
                                <i class="fas fa-save btn-icon"></i> Add Payment
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Payment Table -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-list"></i> Payment Records</h2>
                        <span class="badge" style="background-color: #e9ecef; color: var(--dark);">
                            <?= $result->num_rows ?> Records
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tenant</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <form method="POST" class="edit-form">
                                                    <td>
                                                        <?= $row['id'] ?>
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <select name="tenant_property_id">
                                                                <?php if (!empty($tenant_options)): ?>
                                                                    <?php foreach ($tenant_options as $id => $name): ?>
                                                                        <option value="<?= $id ?>" <?= $row['tenant_property_id'] == $id ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($name) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <option value="<?= $row['tenant_property_id'] ?>"><?= $row['tenant_property_id'] ?></option>
                                                                <?php endif; ?>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="number" step="0.01" name="amount" value="<?= $row['amount'] ?>">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="date" name="payment_date" value="<?= $row['payment_date'] ?>">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <select name="payment_method">
                                                                <?php 
                                                                $methods = [
                                                                    'cash' => '<i class="fas fa-money-bill-wave"></i> Cash',
                                                                    'check' => '<i class="fas fa-money-check"></i> Check',
                                                                    'credit_card' => '<i class="fas fa-credit-card"></i> Credit Card',
                                                                    'bank_transfer' => '<i class="fas fa-university"></i> Bank Transfer'
                                                                ];
                                                                foreach ($methods as $value => $label): 
                                                                ?>
                                                                    <option value="<?= $value ?>" <?= $row['payment_method'] == $value ? 'selected' : '' ?>>
                                                                        <?= $value ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <select name="status">
                                                                <?php 
                                                                $statuses = [
                                                                    'pending' => 'Pending',
                                                                    'completed' => 'Completed',
                                                                    'failed' => 'Failed',
                                                                    'refunded' => 'Refunded'
                                                                ];
                                                                foreach ($statuses as $value => $label): 
                                                                ?>
                                                                    <option value="<?= $value ?>" <?= $row['status'] == $value ? 'selected' : '' ?>>
                                                                        <?= $label ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="text" name="transaction_reference" value="<?= htmlspecialchars($row['transaction_reference']) ?>">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <textarea name="notes"><?= htmlspecialchars($row['notes']) ?></textarea>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button type="submit" name="update_payment" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-save"></i> Update
                                                            </button>
                                                            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this payment record?')" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        </div>
                                                    </td>
                                                </form>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" style="text-align: center; padding: 30px;">
                                                <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--gray); margin-bottom: 10px;"></i>
                                                <p>No payment records found. Add your first payment using the form above.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const overlay = document.getElementById('overlay');
            
            // Toggle sidebar on button click
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-expanded');
                overlay.classList.toggle('overlay-visible');
            });
            
            // Close sidebar when clicking overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('sidebar-expanded');
                overlay.classList.remove('overlay-visible');
            });
            
            // Format currency display
            const amountInputs = document.querySelectorAll('input[name="amount"]');
            amountInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.paddingLeft = '25px';
                });
            });
            
            // Highlight row on hover
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseover', function() {
                    this.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
                });
                row.addEventListener('mouseout', function() {
                    this.style.backgroundColor = '';
                });
            });
            
            // Responsive sidebar handling
            function handleResize() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('sidebar-expanded');
                    mainContent.classList.remove('main-content-expanded');
                    overlay.classList.remove('overlay-visible');
                }
            }
            
            // Initial check and event listener for window resize
            handleResize();
            window.addEventListener('resize', handleResize);
        });
    </script>
</body>
</html>