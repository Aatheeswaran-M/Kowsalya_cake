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

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.image 
               FROM cart c 
               INNER JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = :user_id";
$stmt = $db->prepare($cart_query);
$stmt->bindParam(':user_id', $_SESSION['customer_id']);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = $_POST['shipping_address'];
    $phone = $_POST['phone'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'] ?? '';
    
    try {
        $db->beginTransaction();
        
        // Create order
        $order_query = "INSERT INTO orders (user_id, total_amount, shipping_address, phone, payment_method, notes) 
                       VALUES (:user_id, :total_amount, :shipping_address, :phone, :payment_method, :notes)";
        $stmt = $db->prepare($order_query);
        $stmt->bindParam(':user_id', $_SESSION['customer_id']);
        $stmt->bindParam(':total_amount', $total);
        $stmt->bindParam(':shipping_address', $shipping_address);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':notes', $notes);
        $stmt->execute();
        
        $order_id = $db->lastInsertId();
        
        // Insert order items
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                      VALUES (:order_id, :product_id, :quantity, :price, :subtotal)";
        $item_stmt = $db->prepare($item_query);
        
        foreach ($cart_items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $item_stmt->bindParam(':order_id', $order_id);
            $item_stmt->bindParam(':product_id', $item['product_id']);
            $item_stmt->bindParam(':quantity', $item['quantity']);
            $item_stmt->bindParam(':price', $item['price']);
            $item_stmt->bindParam(':subtotal', $subtotal);
            $item_stmt->execute();
        }
        
        // Clear cart
        $clear_cart = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $db->prepare($clear_cart);
        $stmt->bindParam(':user_id', $_SESSION['customer_id']);
        $stmt->execute();
        
        $db->commit();
        
        header('Location: order-success.php?order_id=' . $order_id);
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Order placement failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kowsalya Cake Shop</title>
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
        }
        .navbar h1 { font-size: 1.5rem; }
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }
        .section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
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
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        .order-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #ecf0f1;
        }
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .item-info {
            flex: 1;
        }
        .item-info h4 {
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }
        .item-info p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }
        .summary-row.total {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2c3e50;
            border-top: 2px solid #ddd;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        .place-order-btn {
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
        .place-order-btn:hover {
            background: #27ae60;
        }
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🛒 Checkout</h1>
    </div>

    <div class="container">
        <div class="section">
            <h2>Shipping Information</h2>
            <?php if (isset($error)): ?>
                <div style="background: #fee; color: #c33; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($customer['name']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Shipping Address *</label>
                    <textarea name="shipping_address" required><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Payment Method *</label>
                    <select name="payment_method" required>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="Online Payment">Online Payment</option>
                        <option value="Card on Delivery">Card on Delivery</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Order Notes (Optional)</label>
                    <textarea name="notes" placeholder="Special instructions for delivery..."></textarea>
                </div>

                <button type="submit" class="place-order-btn">Place Order</button>
            </form>
        </div>

        <div class="section">
            <h2>Order Summary</h2>
            <?php foreach ($cart_items as $item): ?>
            <div class="order-item">
                <img src="../assets/images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" onerror="this.src='https://via.placeholder.com/60'">
                <div class="item-info">
                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    <p>Qty: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></p>
                </div>
                <div style="font-weight: bold; color: #27ae60;">
                    ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="summary-row">
                <span>Subtotal:</span>
                <span>₹<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery:</span>
                <span>FREE</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>₹<?php echo number_format($total, 2); ?></span>
            </div>
        </div>
    </div>
</body>
</html>