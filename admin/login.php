<?php
// admin/login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$debug = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        include_once '../config/database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        // First check if user exists with any role
        $query = "SELECT id, name, email, password, role FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if user is admin
            if ($row['role'] !== 'admin') {
                $error = 'This account is not an admin account.';
            } else {
                // Verify password
                if (password_verify($password, $row['password'])) {
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_name'] = $row['name'];
                    $_SESSION['admin_email'] = $row['email'];
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid password.';
                    // Auto-fix if password is "admin123" and doesn't verify
                    if ($password === 'admin123') {
                        $new_hash = password_hash('admin123', PASSWORD_BCRYPT);
                        $update = "UPDATE users SET password = :password WHERE id = :id";
                        $update_stmt = $db->prepare($update);
                        $update_stmt->bindParam(':password', $new_hash);
                        $update_stmt->bindParam(':id', $row['id']);
                        if ($update_stmt->execute()) {
                            $error = 'Password has been reset. Please try logging in again.';
                        }
                    }
                }
            }
        } else {
            $error = 'Admin account not found.';
            // Auto-create admin if email is admin@kowsalyacake.com
            if ($email === 'admin@kowsalyacake.com' && $password === 'admin123') {
                $hash = password_hash('admin123', PASSWORD_BCRYPT);
                $insert = "INSERT INTO users (name, email, password, phone, address, role) 
                          VALUES ('Admin', 'admin@kowsalyacake.com', :password, '1234567890', 'Admin Address', 'admin')";
                $insert_stmt = $db->prepare($insert);
                $insert_stmt->bindParam(':password', $hash);
                if ($insert_stmt->execute()) {
                    $error = 'Admin account created! Please try logging in again.';
                }
            }
        }
    } else {
        $error = 'Please enter both email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Kowsalya Cake Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        .login-container p {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 2rem;
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
            background: #efe;
            color: #3c3;
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
        .credentials-box {
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 0.85rem;
            color: #666;
        }
        .credentials-box strong {
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🎂 Admin Login</h2>
        <p>Kowsalya Cake Shop</p>
        
        <?php if ($error): ?>
            <div class="<?php echo strpos($error, 'created') !== false || strpos($error, 'reset') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="admin@kowsalyacake.com" required autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">← Back to Home</a>
        </div>
        
        <div class="credentials-box">
            <strong>Default Admin Credentials:</strong><br>
            Email: admin@kowsalyacake.com<br>
            Password: admin123<br><br>
            <small>If login fails, the system will auto-create/reset the admin account.</small>
        </div>
    </div>
</body>
</html>