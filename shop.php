<?php
session_start();
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get category filter
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM products WHERE is_active = 1";
if ($category) {
    $query .= " AND category = :category";
}
if ($search) {
    $query .= " AND (name LIKE :search OR description LIKE :search)";
}
$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
if ($category) {
    $stmt->bindParam(':category', $category);
}
if ($search) {
    $search_term = "%$search%";
    $stmt->bindParam(':search', $search_term);
}
$stmt->execute();

// Check if user is logged in
$is_logged_in = isset($_SESSION['customer_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Kowsalya Cake Shop</title>
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
            position: relative;
        }
        .navbar nav a:hover { background: rgba(255,255,255,0.1); }
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #3498db;
            background: white;
            color: #3498db;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .filter-btn.active, .filter-btn:hover {
            background: #3498db;
            color: white;
        }
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        .search-box input {
            width: 100%;
            padding: 0.6rem;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-info {
            padding: 1rem;
        }
        .product-info h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .product-info p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #27ae60;
            margin: 0.5rem 0;
        }
        .add-to-cart {
            width: 100%;
            padding: 0.8rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .add-to-cart:hover {
            background: #2980b9;
        }
        .stock-info {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 0.5rem;
        }
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 2rem;
            background: #2ecc71;
            color: white;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
        }
        .message.show {
            display: block;
            animation: slideIn 0.3s;
        }
        @keyframes slideIn {
            from { transform: translateX(400px); }
            to { transform: translateX(0); }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🎂 Kowsalya Cake Shop</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <?php if ($is_logged_in): ?>
                <a href="customer/cart.php">🛒 Cart <span class="cart-badge" id="cartCount">0</span></a>
                <a href="customer/dashboard.php">My Account</a>
                <a href="customer/logout.php">Logout</a>
            <?php else: ?>
                <a href="customer/login.php">Login</a>
            <?php endif; ?>
        </nav>
    </div>

    <div class="container">
        <div class="filters">
            <a href="shop.php" class="filter-btn <?php echo !$category ? 'active' : ''; ?>">All</a>
            <a href="shop.php?category=Celebration Cakes" class="filter-btn <?php echo $category == 'Celebration Cakes' ? 'active' : ''; ?>">Celebration</a>
            <a href="shop.php?category=Birthday Cakes" class="filter-btn <?php echo $category == 'Birthday Cakes' ? 'active' : ''; ?>">Birthday</a>
            <a href="shop.php?category=Wedding Cakes" class="filter-btn <?php echo $category == 'Wedding Cakes' ? 'active' : ''; ?>">Wedding</a>
            <a href="shop.php?category=Custom Cakes" class="filter-btn <?php echo $category == 'Custom Cakes' ? 'active' : ''; ?>">Custom</a>
            
            <div class="search-box">
                <form method="GET" action="shop.php">
                    <input type="text" name="search" placeholder="Search cakes..." value="<?php echo htmlspecialchars($search); ?>">
                </form>
            </div>
        </div>

        <div class="products-grid">
            <?php while ($product = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="product-card">
                <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     onerror="this.src='https://via.placeholder.com/280x200?text=Cake'">
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <p><strong>Weight:</strong> <?php echo htmlspecialchars($product['weight']); ?></p>
                    <div class="price">₹<?php echo number_format($product['price'], 2); ?></div>
                    <div class="stock-info">⭐ <?php echo $product['rating']; ?>/5 | Stock: <?php echo $product['stock_quantity']; ?></div>
                    
                    <?php if ($is_logged_in): ?>
                        <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                            Add to Cart
                        </button>
                    <?php else: ?>
                        <a href="customer/login.php" class="add-to-cart" style="display: block; text-align: center; text-decoration: none;">
                            Login to Buy
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="message" id="message"></div>

    <script>
        // Load cart count
        <?php if ($is_logged_in): ?>
        fetch('api/cart/read.php?user_id=<?php echo $_SESSION['customer_id']; ?>')
            .then(res => res.json())
            .then(data => {
                document.getElementById('cartCount').textContent = data.count || 0;
            });
        <?php endif; ?>

        function addToCart(productId, productName) {
            fetch('api/cart/add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: <?php echo $_SESSION['customer_id'] ?? 0; ?>,
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(res => res.json())
            .then(data => {
                showMessage(productName + ' added to cart!');
                // Update cart count
                fetch('api/cart/read.php?user_id=<?php echo $_SESSION['customer_id']; ?>')
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('cartCount').textContent = data.count || 0;
                    });
            })
            .catch(err => {
                showMessage('Failed to add to cart', true);
            });
        }

        function showMessage(text, isError = false) {
            const msg = document.getElementById('message');
            msg.textContent = text;
            msg.style.background = isError ? '#e74c3c' : '#2ecc71';
            msg.classList.add('show');
            setTimeout(() => {
                msg.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>