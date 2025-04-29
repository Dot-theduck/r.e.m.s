<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'rems';
$username = 'root';
$password = '';

// Initialize variables
$tenant = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'profile_image' => ''
];
$errors = [];
$success = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete'])) {
            // DELETE operation
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'tenant'");
            $stmt->execute([$_POST['id']]);
            $_SESSION['success'] = 'Tenant deleted successfully';
            header('Location: tenant_management.php');
            exit();
        } elseif (isset($_POST['save'])) {
            // Validate input
            $tenant = array_merge($tenant, $_POST);
            
            if (empty($tenant['first_name'])) $errors['first_name'] = 'First name is required';
            if (empty($tenant['last_name'])) $errors['last_name'] = 'Last name is required';
            if (empty($tenant['email'])) $errors['email'] = 'Email is required';
            if (!filter_var($tenant['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
            
            // Check if email exists (for new tenants)
            if (empty($tenant['id'])) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND user_type = 'tenant'");
                $stmt->execute([$tenant['email']]);
                if ($stmt->fetch()) $errors['email'] = 'Email already exists';
            }

            // Handle file upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/tenants/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                    $tenant['profile_image'] = $destination;
                } else {
                    $errors['profile_image'] = 'Failed to upload image';
                }
            }

            if (empty($errors)) {
                if (empty($tenant['id'])) {
                    // CREATE operation
                    $hashedPassword = password_hash($tenant['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (
                            first_name, last_name, email, phone, password, 
                            user_type, profile_image, created_at
                        ) VALUES (?, ?, ?, ?, ?, 'tenant', ?, NOW())
                    ");
                    $stmt->execute([
                        $tenant['first_name'], $tenant['last_name'], $tenant['email'], 
                        $tenant['phone'], $hashedPassword, $tenant['profile_image']
                    ]);
                    $_SESSION['success'] = 'Tenant added successfully';
                } else {
                    // UPDATE operation
                    $sql = "UPDATE users SET 
                            first_name = ?, last_name = ?, email = ?, phone = ?, 
                            profile_image = COALESCE(?, profile_image)
                            WHERE id = ? AND user_type = 'tenant'";
                    $params = [
                        $tenant['first_name'], $tenant['last_name'], $tenant['email'], 
                        $tenant['phone'], $tenant['profile_image'], $tenant['id']
                    ];
                    
                    // Only update password if provided
                    if (!empty($tenant['password'])) {
                        $hashedPassword = password_hash($tenant['password'], PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET 
                                first_name = ?, last_name = ?, email = ?, phone = ?, 
                                password = ?, profile_image = COALESCE(?, profile_image)
                                WHERE id = ? AND user_type = 'tenant'";
                        $params = [
                            $tenant['first_name'], $tenant['last_name'], $tenant['email'], 
                            $tenant['phone'], $hashedPassword, $tenant['profile_image'], $tenant['id']
                        ];
                    }
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $_SESSION['success'] = 'Tenant updated successfully';
                }
                header('Location: tenant_management.php');
                exit();
            }
        }
    } elseif ($action === 'edit' && isset($_GET['id'])) {
        // READ operation for edit form
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'tenant'");
        $stmt->execute([$_GET['id']]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tenant) {
            header('Location: tenant_management.php');
            exit();
        }
        $tenant['password'] = ''; // Never show the hashed password
    } elseif ($action === 'delete' && isset($_GET['id'])) {
        // Show delete confirmation
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE id = ? AND user_type = 'tenant'");
        $stmt->execute([$_GET['id']]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tenant) {
            header('Location: tenant_management.php');
            exit();
        }
    }

    // Fetch all tenants
    $stmt = $pdo->query("
        SELECT u.*, 
               (SELECT COUNT(*) FROM tenant_properties WHERE tenant_id = u.id AND status = 'active') AS active_leases
        FROM users u
        WHERE u.user_type = 'tenant'
        ORDER BY u.last_name, u.first_name
    ");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check for success message from session
    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REMS - Tenant Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tenant-card {
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }
        .tenant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .badge-lease {
            background-color: #4e73df;
        }
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <!-- Tenant Listing View -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Tenant Management</h2>
                </div>
                <div class="col-md-6 text-end">
                    <a href="tenant_management.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Tenant
                    </a>
                </div>
            </div>

            <div class="row">
                <?php foreach ($tenants as $t): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card tenant-card">
                            <div class="card-body text-center">
                                <img src="<?php echo htmlspecialchars($t['profile_image'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($t['first_name'] . '+' . $t['last_name']) . '&size=100'); ?>" 
                                     class="profile-img mb-3" 
                                     alt="<?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?>">
                                <h5 class="card-title"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($t['email']); ?><br>
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($t['phone'] ?: 'N/A'); ?>
                                </p>
                                <span class="badge badge-lease">
                                    <?php echo $t['active_leases']; ?> Active Lease<?php echo $t['active_leases'] != 1 ? 's' : ''; ?>
                                </span>
                                <div class="mt-3">
                                    <a href="tenant_management.php?action=edit&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="tenant_management.php?action=delete&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($action === 'delete' && isset($tenant['id'])): ?>
            <!-- Delete Confirmation -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h3 class="card-title">Confirm Tenant Deletion</h3>
                        </div>
                        <div class="card-body">
                            <p>Are you sure you want to delete tenant "<?php echo htmlspecialchars($tenant['first_name'] . ' ' . $tenant['last_name']); ?>"?</p>
                            <form method="post">
                                <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
                                <div class="d-flex justify-content-between">
                                    <a href="tenant_management.php" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" name="delete" class="btn btn-danger">Delete Tenant</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Add/Edit Tenant Form -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-section">
                        <h2><?php echo empty($tenant['id']) ? 'Add New Tenant' : 'Edit Tenant'; ?></h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name*</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($tenant['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name*</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($tenant['last_name']); ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email*</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($tenant['email']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($tenant['phone']); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password<?php echo empty($tenant['id']) ? '*' : ''; ?></label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           <?php echo empty($tenant['id']) ? 'required' : ''; ?>>
                                    <?php if (!empty($tenant['id'])): ?>
                                        <small class="text-muted">Leave blank to keep current password</small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="profile_image" class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                    <?php if (!empty($tenant['profile_image'])): ?>
                                        <div class="mt-2">
                                            <p>Current Image:</p>
                                            <img src="<?php echo htmlspecialchars($tenant['profile_image']); ?>" class="preview-image">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="tenant_management.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="save" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Tenant
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Preview image before upload
            const imageInput = document.getElementById('profile_image');
            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const preview = document.querySelector('.preview-image');
                    if (preview && this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Form validation
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    let valid = true;
                    const requiredFields = form.querySelectorAll('[required]');
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                            valid = false;
                        } else {
                            field.classList.remove('is-invalid');
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