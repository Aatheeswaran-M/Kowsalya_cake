<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit();
}

include_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get all orders with items
$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
          FROM orders o
          WHERE o.user_id = :user_id
          ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['customer_id']);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Kowsalya Cake Shop</title>
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
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .page-header {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .page-header h2 {
            color: #2c3e50;
        }
        .order-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #ecf0f1;
        }
        .order-id {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .order-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .order-body {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .order-info {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .order-info label {
            display: block;
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
        }
        .order-info p {
            color: #2c3e50;
            font-weight: 500;
        }
        .status {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cfe2ff; color: #084298; }
        .status-shipped { background: #cff4fc; color: #055160; }
        .status-delivered { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #842029; }
        .view-btn {
            padding: 0.5rem 1.5rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .view-btn:hover {
            background: #2980b9;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .empty-state h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .empty-state p {
            color: #7f8c8d;
            margin-bottom: 2rem;
        }
        .empty-state a {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📦 My Orders</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="../shop.php">Shop</a>
            <a href="cart.php">Cart</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Order History</h2>
        </div>

        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($order = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-id">Order #<?php echo $order['id']; ?></div>
                        <div class="order-date"><?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span class="status status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                        <a href="invoice.php?id=<?php echo $order['id']; ?>" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;" target="_blank">
                            <i class="fas fa-download"></i> Invoice
                        </a>
                    </div>
                </div>
                <div class="order-body">
                    <div class="order-info">
                        <label>Total Amount</label>
                        <p>₹<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                    <div class="order-info">
                        <label>Items</label>
                        <p><?php echo $order['item_count']; ?> item(s)</p>
                    </div>
                    <div class="order-info">
                        <label>Payment</label>
                        <p><?php echo htmlspecialchars($order['payment_method']); ?></p>
                    </div>
                    <div class="order-info">
                        <label>Payment Status</label>
                        <p><?php echo ucfirst($order['payment_status']); ?></p>
                    </div>
                </div>
                <div style="border-top: 1px solid #ecf0f1; padding-top: 1rem;">
                    <strong>Shipping Address:</strong>
                    <p style="color: #7f8c8d; margin-top: 0.5rem;"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    <p style="color: #7f8c8d;">Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
                    <?php if ($order['notes']): ?>
                        <p style="color: #7f8c8d; margin-top: 0.5rem;"><strong>Notes:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start shopping to place your first order!</p>
                <a href="../shop.php">Browse Cakes</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>