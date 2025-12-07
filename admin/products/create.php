<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $price = $_POST['price'];
    $weight = trim($_POST['weight']);
    $stock_quantity = $_POST['stock_quantity'];
    $rating = $_POST['rating'];
    $image = 'placeholder.jpg'; // Default image
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newname = uniqid() . '.' . $ext;
            $upload_path = '../../assets/images/' . $newname;
            
            if (!is_dir('../../assets/images/')) {
                mkdir('../../assets/images/', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $newname;
            }
        }
    }
    
    if (!empty($name) && !empty($category) && !empty($price)) {
        $query = "INSERT INTO products (name, description, category, price, weight, image, stock_quantity, rating) 
                  VALUES (:name, :description, :category, :price, :weight, :image, :stock_quantity, :rating)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        $stmt->bindParam(':rating', $rating);
        
        if ($stmt->execute()) {
            $success = 'Product created successfully!';
        } else {
            $error = 'Failed to create product.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 { font-size: 1.5rem; }
        .navbar nav a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }
        .navbar nav a:hover { background: rgba(255,255,255,0.1); }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .form-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-card h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }
        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🎂 Add New Product</h1>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="manage.php">Manage Products</a>
            <a href="../orders/manage.php">Orders</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <div class="form-card">
            <h2>Add New Product</h2>

            <?php if ($success): ?>
                <div class="message success">
                    <?php echo $success; ?>
                    <a href="manage.php">View Products</a> | <a href="create.php">Add Another</a>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Enter product description..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" required>
                            <option value="">Select Category</option>
                            <option value="Celebration Cakes">Celebration Cakes</option>
                            <option value="Birthday Cakes">Birthday Cakes</option>
                            <option value="Wedding Cakes">Wedding Cakes</option>
                            <option value="Custom Cakes">Custom Cakes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Weight</label>
                        <input type="text" name="weight" value="1 Kg" placeholder="e.g., 1 Kg, 500g">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Price (₹) *</label>
                        <input type="number" name="price" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" value="100" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Rating (0-5)</label>
                        <input type="number" name="rating" step="0.1" min="0" max="5" value="5.0">
                    </div>

                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="manage.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>