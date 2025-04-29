<?php
require_once '../../rems/tenant/config.php';

// CREATE
if (isset($_POST['create'])) {
    $stmt = $mysqli->prepare("INSERT INTO properties (landlord_id, title, description, address, city, state, zip_code, bedrooms, bathrooms, square_footage, monthly_rent, status, featured_image)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssiidsss",
        $_POST['landlord_id'],
        $_POST['title'],
        $_POST['description'],
        $_POST['address'],
        $_POST['city'],
        $_POST['state'],
        $_POST['zip_code'],
        $_POST['bedrooms'],
        $_POST['bathrooms'],
        $_POST['square_footage'],
        $_POST['monthly_rent'],
        $_POST['status'],
        $_POST['featured_image']
    );
    $stmt->execute();
    $stmt->close();
    header("Location: properties.php");
    exit();
}

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $mysqli->query("DELETE FROM properties WHERE id = $id");
    header("Location: properties.php");
    exit();
}

// FETCH
$result = $mysqli->query("SELECT * FROM properties ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2>Property Management (Admin)</h2>

    <form method="POST" class="row g-3 mt-3 mb-5">
        <h4>Add New Property</h4>
        <div class="col-md-2"><input name="landlord_id" class="form-control" placeholder="Landlord ID" required></div>
        <div class="col-md-2"><input name="title" class="form-control" placeholder="Title" required></div>
        <div class="col-md-4"><input name="description" class="form-control" placeholder="Description"></div>
        <div class="col-md-4"><input name="address" class="form-control" placeholder="Address" required></div>
        <div class="col-md-2"><input name="city" class="form-control" placeholder="City" required></div>
        <div class="col-md-2"><input name="state" class="form-control" placeholder="State" required></div>
        <div class="col-md-2"><input name="zip_code" class="form-control" placeholder="ZIP" required></div>
        <div class="col-md-1"><input type="number" name="bedrooms" class="form-control" placeholder="Beds"></div>
        <div class="col-md-1"><input type="text" name="bathrooms" class="form-control" placeholder="Baths"></div>
        <div class="col-md-2"><input type="number" name="square_footage" class="form-control" placeholder="Sq Ft"></div>
        <div class="col-md-2"><input type="text" name="monthly_rent" class="form-control" placeholder="Rent"></div>
        <div class="col-md-2">
            <select name="status" class="form-control">
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>
        <div class="col-md-4"><input name="featured_image" class="form-control" placeholder="Image URL (optional)"></div>
        <div class="col-md-12"><button type="submit" name="create" class="btn btn-primary">Add Property</button></div>
    </form>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>ID</th><th>Title</th><th>Landlord</th><th>Address</th><th>City</th><th>Rent</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $row['landlord_id'] ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= $row['city'] ?></td>
                <td>$<?= $row['monthly_rent'] ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <a href="edit_property.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this property?')" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
