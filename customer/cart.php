<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit();
}

include_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get cart items
$query = "SELECT c.id as cart_id, c.quantity, 
          p.id as product_id, p.name, p.price, p.image, p.weight, p.stock_quantity
          FROM cart c
          INNER JOIN products p ON c.product_id = p.id
          WHERE c.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['customer_id']);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Kowsalya Cake Shop</title>
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
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .cart-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .cart-section h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }
        .cart-item {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid #ecf0f1;
            align-items: center;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .item-details {
            flex: 1;
        }
        .item-details h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .item-details p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .item-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #27ae60;
        }
        .quantity-control {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .qty-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: #3498db;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .qty-btn:hover {
            background: #2980b9;
        }
        .qty-display {
            width: 50px;
            text-align: center;
            font-weight: bold;
        }
        .remove-btn {
            padding: 0.5rem 1rem;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .remove-btn:hover {
            background: #c0392b;
        }
        .cart-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .summary-row.total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            border-top: 2px solid #ddd;
            padding-top: 1rem;
        }
        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
        }
        .checkout-btn:hover {
            background: #27ae60;
        }
        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
        }
        .empty-cart a {
            display: inline-block;
            margin-top: 1rem;
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
        <h1>🛒 My Shopping Cart</h1>
        <nav>
            <a href="../shop.php">Continue Shopping</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <div class="cart-section">
            <h2>Shopping Cart</h2>
            <div id="cartItems">
                <?php if ($stmt->rowCount() > 0): ?>
                    <?php 
                    $total = 0;
                    while ($item = $stmt->fetch(PDO::FETCH_ASSOC)): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                    <div class="cart-item" id="item-<?php echo $item['cart_id']; ?>">
                        <img src="../assets/images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" onerror="this.src='https://via.placeholder.com/100'">
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>Weight: <?php echo htmlspecialchars($item['weight']); ?></p>
                            <p class="item-price">₹<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <div class="quantity-control">
                            <button class="qty-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                            <span class="qty-display"><?php echo $item['quantity']; ?></span>
                            <button class="qty-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                        </div>
                        <div style="text-align: right;">
                            <div class="item-price">₹<?php echo number_format($subtotal, 2); ?></div>
                            <button class="remove-btn" onclick="removeItem(<?php echo $item['cart_id']; ?>)">Remove</button>
                        </div>
                    </div>
                    <?php endwhile; ?>

                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="total">₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <button class="checkout-btn" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
                    </div>
                <?php else: ?>
                    <div class="empty-cart">
                        <h3>Your cart is empty</h3>
                        <p>Add some delicious cakes to your cart!</p>
                        <a href="../shop.php">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function updateQuantity(cartId, newQty) {
            if (newQty < 1) return;
            
            fetch('../api/cart/update.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cart_id: cartId,
                    quantity: newQty
                })
            })
            .then(res => res.json())
            .then(data => {
                location.reload();
            });
        }

        function removeItem(cartId) {
            if (!confirm('Remove this item from cart?')) return;
            
            fetch('../api/cart/delete.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_id: cartId })
            })
            .then(res => res.json())
            .then(data => {
                location.reload();
            });
        }
    </script>
</body>
</html>