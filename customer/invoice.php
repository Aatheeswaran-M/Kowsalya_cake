<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit();
}

include_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: orders.php');
    exit();
}

// Get order details
$query = "SELECT o.*, u.name as customer_name, u.email, u.phone as customer_phone, u.address as customer_address
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id
          WHERE o.id = :order_id AND o.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':order_id', $order_id);
$stmt->bindParam(':user_id', $_SESSION['customer_id']);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$items_query = "SELECT oi.*, p.name as product_name, p.image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id";
$stmt = $db->prepare($items_query);
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order_id; ?> - Kowsalya Cake Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            padding: 2rem;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 3px solid #FF6B9D;
        }

        .company-info h1 {
            color: #FF6B9D;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .company-info p {
            color: #7f8c8d;
            line-height: 1.6;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-meta h2 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .invoice-meta p {
            color: #7f8c8d;
            margin: 0.3rem 0;
        }

        .invoice-meta .order-id {
            font-size: 1.3rem;
            font-weight: bold;
            color: #FF6B9D;
        }

        .customer-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .detail-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #FF6B9D;
        }

        .detail-box h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .detail-box p {
            color: #7f8c8d;
            margin: 0.5rem 0;
            line-height: 1.6;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .items-table thead {
            background: linear-gradient(135deg, #FF6B9D 0%, #FFA07A 100%);
            color: white;
        }

        .items-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid #ecf0f1;
        }

        .items-table tbody tr:hover {
            background: #f8f9fa;
        }

        .item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 2rem;
        }

        .totals-box {
            width: 350px;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            color: #7f8c8d;
        }

        .total-row.grand-total {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2c3e50;
            padding-top: 0.8rem;
            border-top: 2px solid #ddd;
            margin-top: 0.8rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cfe2ff; color: #084298; }
        .status-shipped { background: #cff4fc; color: #055160; }
        .status-delivered { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #842029; }

        .footer-note {
            text-align: center;
            color: #7f8c8d;
            padding-top: 2rem;
            border-top: 2px solid #ecf0f1;
            margin-top: 2rem;
        }

        .footer-note p {
            margin: 0.5rem 0;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF6B9D 0%, #FFA07A 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 157, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #2c3e50;
            border: 2px solid #2c3e50;
        }

        .btn-secondary:hover {
            background: #2c3e50;
            color: white;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .action-buttons {
                display: none;
            }
            
            .invoice-container {
                box-shadow: none;
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .invoice-header {
                flex-direction: column;
            }
            
            .invoice-meta {
                text-align: left;
                margin-top: 1rem;
            }
            
            .customer-details {
                grid-template-columns: 1fr;
            }
            
            .items-table {
                font-size: 0.85rem;
            }
            
            .totals-box {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Invoice
        </button>
        <button onclick="downloadPDF()" class="btn btn-primary">
            <i class="fas fa-download"></i> Download PDF
        </button>
        <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    <div class="invoice-container" id="invoice">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1>🎂 Kowsalya Cake Shop</h1>
                <p>
                    <i class="fas fa-map-marker-alt"></i> Chennai, Tamil Nadu, India<br>
                    <i class="fas fa-phone"></i> +91 1234567890<br>
                    <i class="fas fa-envelope"></i> info@kowsalyacake.com<br>
                    <i class="fas fa-globe"></i> www.kowsalyacake.com
                </p>
            </div>
            <div class="invoice-meta">
                <h2>INVOICE</h2>
                <p class="order-id">#<?php echo $order_id; ?></p>
                <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </p>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="customer-details">
            <div class="detail-box">
                <h3><i class="fas fa-user"></i> Bill To:</h3>
                <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($order['phone']); ?></p>
            </div>
            <div class="detail-box">
                <h3><i class="fas fa-shipping-fast"></i> Ship To:</h3>
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            </div>
        </div>

        <!-- Order Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <img src="../assets/images/<?php echo $item['image']; ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                             class="item-image"
                             onerror="this.src='https://via.placeholder.com/50'">
                    </td>
                    <td><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><strong>₹<?php echo number_format($item['subtotal'], 2); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Delivery Charges:</span>
                    <span>FREE</span>
                </div>
                <div class="total-row">
                    <span>Tax (0%):</span>
                    <span>₹0.00</span>
                </div>
                <div class="total-row grand-total">
                    <span>Grand Total:</span>
                    <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <?php if ($order['notes']): ?>
        <div class="detail-box" style="margin-bottom: 2rem;">
            <h3><i class="fas fa-sticky-note"></i> Order Notes:</h3>
            <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer-note">
            <p><strong>Thank you for your order!</strong></p>
            <p>For any queries, please contact us at info@kowsalyacake.com or call +91 1234567890</p>
            <p style="margin-top: 1rem; font-size: 0.9rem;">
                This is a computer-generated invoice and does not require a signature.
            </p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.getElementById('invoice');
            const opt = {
                margin: 0.5,
                filename: 'Invoice_<?php echo $order_id; ?>_<?php echo date('Ymd'); ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
            };
            
            // Hide buttons before generating PDF
            document.querySelector('.action-buttons').style.display = 'none';
            
            html2pdf().set(opt).from(element).save().then(() => {
                document.querySelector('.action-buttons').style.display = 'flex';
            });
        }
    </script>
</body>
</html>