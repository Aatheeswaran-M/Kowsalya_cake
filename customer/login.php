<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['customer_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        include_once '../config/database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, name, email, password, phone, address FROM users WHERE email = :email AND role = 'customer'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                $_SESSION['customer_id'] = $row['id'];
                $_SESSION['customer_name'] = $row['name'];
                $_SESSION['customer_email'] = $row['email'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'Customer account not found.';
        }
    } else {
        $error = 'Please enter both email and password.';
    }
}

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['reg_name'] ?? '');
    $email = trim($_POST['reg_email'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $phone = trim($_POST['reg_phone'] ?? '');
    $address = trim($_POST['reg_address'] ?? '');
    
    if (!empty($name) && !empty($email) && !empty($password)) {
        include_once '../config/database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if email exists
        $check = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($check);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (name, email, password, phone, address, role) 
                     VALUES (:name, :email, :password, :phone, :address, 'customer')";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hash);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! Please login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Kowsalya Cake Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .container {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 2rem;
        }
        .tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 2px solid #ecf0f1;
        }
        .tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
            color: #7f8c8d;
            transition: all 0.3s;
        }
        .tab.active {
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
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
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 0.8rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 0.8rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }
        .btn {
            width: 100%;
            padding: 0.8rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🎂 Customer Portal</h2>
        <p class="subtitle">Kowsalya Cake Shop</p>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="switchTab('login')">Login</div>
            <div class="tab" onclick="switchTab('register')">Register</div>
        </div>

        <!-- Login Form -->
        <div class="tab-content active" id="login">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn">Login</button>
            </form>
        </div>

        <!-- Registration Form -->
        <div class="tab-content" id="register">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="reg_name">Full Name *</label>
                    <input type="text" id="reg_name" name="reg_name" required>
                </div>

                <div class="form-group">
                    <label for="reg_email">Email *</label>
                    <input type="email" id="reg_email" name="reg_email" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_password">Password *</label>
                    <input type="password" id="reg_password" name="reg_password" required>
                </div>

                <div class="form-group">
                    <label for="reg_phone">Phone</label>
                    <input type="text" id="reg_phone" name="reg_phone">
                </div>

                <div class="form-group">
                    <label for="reg_address">Address</label>
                    <input type="text" id="reg_address" name="reg_address">
                </div>
                
                <button type="submit" name="register" class="btn">Register</button>
            </form>
        </div>

        <div class="back-link">
            <a href="../index.php">← Back to Home</a>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to selected tab
            event.target.classList.add('active');
            document.getElementById(tab).classList.add('active');
        }
    </script>
</body>
</html>