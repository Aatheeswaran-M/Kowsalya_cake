<?php
session_start();
if (!isset($_SESSION['customer_id']) || !isset($_GET['order_id'])) {
    header('Location: dashboard.php');
    exit();
}

$order_id = $_GET['order_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed Successfully</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            background: white;
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
        }
        .success-icon {
            font-size: 5rem;
            color: #2ecc71;
            margin-bottom: 1rem;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        p {
            color: #7f8c8d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .order-id {
            font-size: 1.5rem;
            font-weight: bold;
            color: #3498db;
            margin: 1rem 0;
        }
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0.5rem;
            font-weight: bold;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-success {
            background: #2ecc71;
        }
        .btn-success:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h1>Order Placed Successfully!</h1>
        <p>Thank you for your order. We'll start preparing your delicious cake right away.</p>
        <div class="order-id">Order #<?php echo $order_id; ?></div>
        <p>You'll receive an email confirmation shortly.</p>
        <div>
            <a href="orders.php" class="btn btn-success">View My Orders</a>
            <a href="../shop.php" class="btn">Continue Shopping</a>
        </div>
    </div>
</body>
</html>