<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE orders SET status = :status WHERE id = :order_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $order_id);
    
    if ($stmt->execute()) {
        header('Location: manage.php?msg=updated');
        exit();
    }
}

// Fetch all orders
$query = "SELECT o.*, u.name as customer_name, u.email as customer_email
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id
          ORDER BY o.created_at DESC";
$stmt = $db->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
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
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .header { margin-bottom: 2rem; }
        .header h2 { color: #2c3e50; }
        .message {
            padding: 1rem; border-radius: 5px; margin-bottom: 1rem;
            background: #d4edda; color: #155724;
        }
        table {
            width: 100%; background: white; border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-collapse: collapse;
        }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #34495e; color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .status {
            padding: 0.3rem 0.8rem; border-radius: 20px;
            font-size: 0.85rem; font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cfe2ff; color: #084298; }
        .status-shipped { background: #cff4fc; color: #055160; }
        .status-delivered { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #842029; }
        select {
            padding: 0.4rem; border: 1px solid #ddd;
            border-radius: 5px; font-size: 0.9rem;
        }
        .btn-small {
            padding: 0.4rem 0.8rem; background: #3498db;
            color: white; border: none; border-radius: 5px;
            cursor: pointer; font-size: 0.9rem;
        }
        .btn-small:hover { background: #2980b9; }
        .actions-form {
            display: flex; gap: 0.5rem; align-items: center;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🎂 Order Management</h1>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../products/manage.php">Products</a>
            <a href="manage.php">Orders</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <div class="header">
            <h2>All Customer Orders</h2>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="message">✓ Order status updated successfully!</div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><strong>#<?php echo $row['id']; ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($row['customer_name']); ?><br>
                        <small style="color: #7f8c8d;"><?php echo htmlspecialchars($row['customer_email']); ?></small>
                    </td>
                    <td><strong>₹<?php echo number_format($row['total_amount'], 2); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                    <td>
                        <span class="status status-<?php echo $row['status']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                    <td>
                        <form method="POST" class="actions-form">
                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                            <select name="status">
                                <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $row['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $row['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $row['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $row['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn-small">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>