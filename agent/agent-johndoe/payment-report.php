<?php
require_once '../../tenant/config.php'; // Adjust path if needed

$landlord_id = 2;

// Fetch payments related to landlord's properties
$sql = "
SELECT 
    payments.id AS payment_id,
    tenant_properties.tenant_id,
    payments.property_id,
    payments.amount,
    payments.payment_date,
    payments.payment_method,
    payments.status,
    properties.title AS property_title,
    properties.address AS property_address
FROM payments
INNER JOIN properties ON payments.property_id = properties.id
LEFT JOIN tenant_properties ON payments.property_id = tenant_properties.property_id
WHERE properties.landlord_id = ?
ORDER BY payments.payment_date DESC
";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize total amount
$total_amount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Payment Report for Your Properties</h1>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Payment ID</th>
                    <th>Tenant ID</th>
                    <th>Property</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php 
                if (strtolower($row['status']) == 'completed') {
                    $total_amount += $row['amount'];
                }
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['payment_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['tenant_id'] ?? 'N/A'); ?></td>
                    <td>
                        <?php echo htmlspecialchars($row['property_title']); ?><br>
                        <small><?php echo htmlspecialchars($row['property_address']); ?></small>
                    </td>
                    <td>$<?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <div class="alert alert-success mt-4">
            <h4>Total Amount Received (Completed Payments Only): <strong>$<?php echo number_format($total_amount, 2); ?></strong></h4>
        </div>

    <?php else: ?>
        <div class="alert alert-info">No payment records found for your properties yet.</div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
