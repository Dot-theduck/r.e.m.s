<?php
require_once './tenant/config.php'; // Connects to DB using $mysqli

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $square_footage = $_POST['square_footage'];
    $monthly_rent = $_POST['monthly_rent'];
    $status = $_POST['status'];
    $landlord_id = 2; // Example static ID — replace with session if needed

    // Handle image upload
    $image_path = "";
    if (!empty($_FILES['featured_image']['name'])) {
        $target_dir = "uploads/";
        $filename = time() . '_' . basename($_FILES["featured_image"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["featured_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    $stmt = $mysqli->prepare("INSERT INTO properties 
        (landlord_id, title, description, address, city, state, zip_code, bedrooms, bathrooms, square_footage, monthly_rent, status, featured_image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("issssssiiidss", 
        $landlord_id, $title, $description, $address, $city, $state, $zip_code,
        $bedrooms, $bathrooms, $square_footage, $monthly_rent, $status, $image_path
    );

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Property successfully added!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}
?>

<!-- HTML Form -->
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Title" required><br>
    <textarea name="description" placeholder="Description"></textarea><br>
    <input type="text" name="address" placeholder="Address" required><br>
    <input type="text" name="city" placeholder="City" required><br>
    <input type="text" name="state" placeholder="State" required><br>
    <input type="text" name="zip_code" placeholder="Zip Code" required><br>
    <input type="number" name="bedrooms" placeholder="Bedrooms"><br>
    <input type="number" step="0.1" name="bathrooms" placeholder="Bathrooms"><br>
    <input type="number" name="square_footage" placeholder="Sq Ft"><br>
    <input type="number" step="0.01" name="monthly_rent" placeholder="Monthly Rent"><br>
    <select name="status">
        <option value="available">Available</option>
        <option value="occupied">Occupied</option>
        <option value="maintenance">Maintenance</option>
    </select><br>
    <input type="file" name="featured_image"><br><br>
    <button type="submit">Submit Property</button>
</form>
