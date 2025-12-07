<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "UPDATE products SET is_active = 0 WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        header('Location: manage.php?msg=deleted');
        exit();
    }
}

// Fetch all products
$query = "SELECT * FROM products ORDER BY created_at DESC";
$stmt = $db->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f6fa; }
        .navbar {
            background: #2c3e50; color: white; padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar h1 { font-size: 1.5rem; }
        .navbar nav a {
            color: white; text-decoration: none; margin-left: 1.5rem;
            padding: 0.5rem 1rem; border-radius: 5px; transition: background 0.3s;
        }
        .navbar nav a:hover { background: rgba(255,255,255,0.1); }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 2rem;
        }
        .header h2 { color: #2c3e50; }
        .btn {
            display: inline-block; padding: 0.8rem 1.5rem;
            background: #3498db; color: white; text-decoration: none;
            border-radius: 5px; font-weight: bold; transition: background 0.3s;
        }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #2ecc71; }
        .btn-success:hover { background: #27ae60; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-small { padding: 0.4rem 0.8rem; font-size: 0.9rem; }
        .message {
            padding: 1rem; border-radius: 5px; margin-bottom: 1rem;
            background: #d4edda; color: #155724; border: 1px solid #c3e6cb;
        }
        table {
            width: 100%; background: white; border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-collapse: collapse;
        }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #34495e; color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        img { max-width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
        .actions { display: flex; gap: 0.5rem; }
        .status-active { color: #27ae60; font-weight: bold; }
        .status-inactive { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🎂 Product Management</h1>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="create.php">Add Product</a>
            <a href="manage.php">Products</a>
            <a href="../orders/manage.php">Orders</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <div class="header">
            <h2>All Products</h2>
            <a href="create.php" class="btn btn-success">+ Add New Product</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="message">
                <?php
                    if ($_GET['msg'] == 'created') echo '✓ Product created successfully!';
                    if ($_GET['msg'] == 'updated') echo '✓ Product updated successfully!';
                    if ($_GET['msg'] == 'deleted') echo '✓ Product deleted successfully!';
                ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><img src="../../assets/images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" onerror="this.src='https://via.placeholder.com/60'"></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td>₹<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo $row['stock_quantity']; ?></td>
                    <td class="<?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo $row['is_active'] ? '✓ Active' : '✗ Inactive'; ?>
                    </td>
                    <td class="actions">
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-small">Edit</a>
                        <a href="manage.php?delete=<?php echo $row['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>