<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch all users
$query = "SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC";
$stmt = $db->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
            padding: 0.5rem 1rem; border-radius: 5px;
        }
        .navbar nav a:hover { background: rgba(255,255,255,0.1); }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .header { margin-bottom: 2rem; }
        .header h2 { color: #2c3e50; }
        table {
            width: 100%; background: white; border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-collapse: collapse;
        }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #34495e; color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .badge {
            padding: 0.3rem 0.8rem; border-radius: 20px;
            font-size: 0.85rem; font-weight: bold;
        }
        .badge-admin { background: #e74c3c; color: white; }
        .badge-customer { background: #3498db; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>👥 Customer Management</h1>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../products/manage.php">Products</a>
            <a href="../orders/manage.php">Orders</a>
            <a href="manage.php">Customers</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <div class="header">
            <h2>All Users</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $row['role']; ?>">
                            <?php echo ucfirst($row['role']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>