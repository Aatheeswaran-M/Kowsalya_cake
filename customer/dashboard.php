<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit();
}

include_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get customer details
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['customer_id']);
$stmt->execute();
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Get order statistics
$orders_query = "SELECT COUNT(*) as total_orders, 
                 COALESCE(SUM(total_amount), 0) as total_spent,
                 SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                 SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
                 FROM orders WHERE user_id = :user_id";
$stmt = $db->prepare($orders_query);
$stmt->bindParam(':user_id', $_SESSION['customer_id']);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent orders
$recent_orders_query = "SELECT * FROM orders 
                        WHERE user_id = :user_id 
                        ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($recent_orders_query);
$stmt->bindParam(':user_id', $_SESSION['customer_id']);
$stmt->execute();
$recent_orders = $stmt;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Kowsalya Cake Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #FF6B9D;
            --secondary: #FFA07A;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gradient: linear-gradient(135deg, #FF6B9D 0%, #FFA07A 100%);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, rgba(255, 107, 157, 0.05) 0%, rgba(255, 160, 122, 0.05) 100%);
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
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient);
            transition: width 0.3s;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 5%;
        }

        /* Welcome Section */
        .welcome-section {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .welcome-content p {
            color: #7f8c8d;
            font-size: 1.1rem;
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 107, 157, 0.2);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .stat-icon.orders { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.spent { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.pending { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-icon.delivered { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

        .stat-details h3 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }

        .stat-details p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease 0.5s;
            animation-fill-mode: both;
        }

        .profile-card h2 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .profile-item {
            padding: 1.2rem;
            background: linear-gradient(135deg, rgba(255, 107, 157, 0.05) 0%, rgba(255, 160, 122, 0.05) 100%);
            border-radius: 15px;
            border-left: 4px solid var(--primary);
            transition: transform 0.3s;
        }

        .profile-item:hover {
            transform: translateX(5px);
        }

        .profile-item label {
            display: block;
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .profile-item p {
            color: var(--dark);
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Orders Section */
        .orders-section {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.6s ease 0.6s;
            animation-fill-mode: both;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            color: var(--dark);
            font-size: 1.5rem;
        }

        .view-all-btn {
            padding: 0.6rem 1.5rem;
            background: var(--gradient);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .view-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 157, 0.4);
        }

        .order-card {
            padding: 1.5rem;
            border: 2px solid #ecf0f1;
            border-radius: 15px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .order-card:hover {
            border-color: var(--primary);
            box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-id {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--dark);
        }

        .status {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cfe2ff; color: #084298; }
        .status-shipped { background: #cff4fc; color: #055160; }
        .status-delivered { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #842029; }

        .order-details {
            display: flex;
            justify-content: space-between;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
        }

        .no-orders i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .shop-btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.8rem 2rem;
            background: var(--gradient);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .shop-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 157, 0.4);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-section {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-birthday-cake"></i> Kowsalya Cake Shop
        </div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="orders.php"><i class="fas fa-box"></i> My Orders</a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
            <a href="../shop.php"><i class="fas fa-store"></i> Shop</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <div class="welcome-content">
                <h2>Welcome back, <?php echo htmlspecialchars($customer['name']); ?>! 👋</h2>
                <p>Manage your orders and profile information</p>
            </div>
            <div class="welcome-icon">🎂</div>
        </div>

        <div class="stats-grid">
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
                <div class="stat-icon spent">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-details">
                    <h3>₹<?php echo number_format($stats['total_spent'], 0); ?></h3>
                    <p>Total Spent</p>
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
                <div class="stat-icon delivered">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['delivered_orders']; ?></h3>
                    <p>Delivered</p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <h2><i class="fas fa-user-circle"></i> My Profile</h2>
            <div class="profile-grid">
                <div class="profile-item">
                    <label><i class="fas fa-user"></i> Name</label>
                    <p><?php echo htmlspecialchars($customer['name']); ?></p>
                </div>
                <div class="profile-item">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <p><?php echo htmlspecialchars($customer['email']); ?></p>
                </div>
                <div class="profile-item">
                    <label><i class="fas fa-phone"></i> Phone</label>
                    <p><?php echo htmlspecialchars($customer['phone'] ?? 'Not provided'); ?></p>
                </div>
                <div class="profile-item">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <p><?php echo htmlspecialchars($customer['address'] ?? 'Not provided'); ?></p>
                </div>
            </div>
        </div>

        <div class="orders-section">
            <div class="section-header">
                <h2><i class="fas fa-box-open"></i> Recent Orders</h2>
                <a href="orders.php" class="view-all-btn">View All Orders →</a>
            </div>
            
            <?php if ($recent_orders->rowCount() > 0): ?>
                <?php while ($order = $recent_orders->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">#<?php echo $order['id']; ?></div>
                        <span class="status status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <div class="order-details">
                        <span><i class="fas fa-rupee-sign"></i> ₹<?php echo number_format($order['total_amount'], 2); ?></span>
                        <span><i class="fas fa-credit-card"></i> <?php echo htmlspecialchars($order['payment_method']); ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-basket"></i>
                    <h3>No Orders Yet</h3>
                    <p>Start shopping to place your first order!</p>
                    <a href="../shop.php" class="shop-btn">Browse Cakes</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>