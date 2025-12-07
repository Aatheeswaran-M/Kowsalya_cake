<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [
    'total_products' => 0,
    'total_orders' => 0,
    'total_customers' => 0,
    'pending_orders' => 0,
    'total_revenue' => 0,
    'today_orders' => 0
];

$query = "SELECT COUNT(*) as count FROM products WHERE is_active = 1";
$stmt = $db->query($query);
$stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT COUNT(*) as count FROM orders";
$stmt = $db->query($query);
$stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
$stmt = $db->query($query);
$stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
$stmt = $db->query($query);
$stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE status = 'delivered'";
$stmt = $db->query($query);
$stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'];

$query = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()";
$stmt = $db->query($query);
$stats['today_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent orders
$recent_orders_query = "SELECT o.*, u.name as customer_name, u.email as customer_email
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC LIMIT 8";
$recent_orders = $db->query($recent_orders_query);

// Get low stock products
$low_stock_query = "SELECT * FROM products WHERE stock_quantity < 10 AND is_active = 1 ORDER BY stock_quantity ASC LIMIT 5";
$low_stock = $db->query($low_stock_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kowsalya Cake Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1f2937;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: white;
            padding: 1rem 5%;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .nav-links a:hover {
            background: var(--gradient);
            color: white;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 5%;
        }

        /* Welcome Section */
        .welcome-banner {
            background: var(--gradient);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-content h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .welcome-icon {
            font-size: 4rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: all 0.3s;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(1)::before { background: var(--info); }
        
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(2)::before { background: var(--success); }
        
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(3)::before { background: var(--warning); }
        
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(4)::before { background: var(--danger); }
        
        .stat-card:nth-child(5) { animation-delay: 0.5s; }
        .stat-card:nth-child(5)::before { background: var(--primary); }
        
        .stat-card:nth-child(6) { animation-delay: 0.6s; }
        .stat-card:nth-child(6)::before { background: var(--secondary); }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
        }

        .stat-icon.products { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .stat-icon.orders { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .stat-icon.customers { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .stat-icon.pending { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .stat-icon.revenue { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        .stat-icon.today { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); }

        .stat-details h3 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }

        .stat-details p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Quick Actions */
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease 0.7s;
            animation-fill-mode: both;
        }

        .quick-actions h2 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            padding: 1.2rem;
            border-radius: 12px;
            text-decoration: none;
            color: white;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .action-btn.add { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .action-btn.manage { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .action-btn.orders { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .action-btn.customers { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

        /* Two Column Layout */
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease 0.8s;
            animation-fill-mode: both;
        }

        .section h2 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Orders Table */
        .order-item {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }

        .order-item:hover {
            background: #f9fafb;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-info h4 {
            color: var(--dark);
            margin-bottom: 0.3rem;
        }

        .order-info p {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .status {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e3a8a; }
        .status-shipped { background: #cffafe; color: #164e63; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        /* Low Stock Alert */
        .alert {
            padding: 1rem;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            margin-bottom: 1rem;
            color: #92400e;
        }

        .stock-item {
            padding: 0.8rem;
            background: #fef3c7;
            border-radius: 8px;
            margin-bottom: 0.8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stock-item h4 {
            color: var(--dark);
            font-size: 0.9rem;
        }

        .stock-badge {
            background: #ef4444;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .two-column {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-birthday-cake"></i> Admin Panel
        </div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="products/manage.php"><i class="fas fa-box"></i> Products</a>
            <a href="orders/manage.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="users/manage.php"><i class="fas fa-users"></i> Customers</a>
            <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>! 👋</h2>
                <p>Here's what's happening with your cake shop today</p>
            </div>
            <div class="welcome-icon">📊</div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon products">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_products']; ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_orders']; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon customers">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_customers']; ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['pending_orders']; ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-details">
                    <h3>₹<?php echo number_format($stats['total_revenue'], 0); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon today">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['today_orders']; ?></h3>
                    <p>Today's Orders</p>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="action-grid">
                <a href="products/create.php" class="action-btn add">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
                <a href="products/manage.php" class="action-btn manage">
                    <i class="fas fa-box"></i> Manage Products
                </a>
                <a href="orders/manage.php" class="action-btn orders">
                    <i class="fas fa-shopping-cart"></i> View Orders
                </a>
                <a href="users/manage.php" class="action-btn customers">
                    <i class="fas fa-users"></i> View Customers
                </a>
            </div>
        </div>

        <div class="two-column">
            <div class="section">
                <h2><i class="fas fa-box-open"></i> Recent Orders</h2>
                <?php while ($order = $recent_orders->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="order-item">
                    <div class="order-info">
                        <h4>#<?php echo $order['id']; ?> - <?php echo htmlspecialchars($order['customer_name']); ?></h4>
                        <p>₹<?php echo number_format($order['total_amount'], 2); ?> • <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                    </div>
                    <span class="status status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="section">
                <h2><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h2>
                <?php if ($low_stock->rowCount() > 0): ?>
                    <div class="alert">
                        <strong>Warning!</strong> The following products are running low on stock
                    </div>
                    <?php while ($product = $low_stock->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="stock-item">
                        <div>
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p style="color: #6b7280; font-size: 0.85rem;"><?php echo htmlspecialchars($product['category']); ?></p>
                        </div>
                        <span class="stock-badge"><?php echo $product['stock_quantity']; ?> left</span>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #6b7280; text-align: center; padding: 2rem;">
                        <i class="fas fa-check-circle" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;"></i><br>
                        All products have sufficient stock!
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>