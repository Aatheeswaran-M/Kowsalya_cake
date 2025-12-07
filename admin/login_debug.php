<?php
// admin/login_debug.php - Temporary debug file
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Debug Admin Login</h2>";

// Check if admin user exists
$query = "SELECT id, name, email, password, role FROM users WHERE email = 'admin@kowsalyacake.com'";
$stmt = $db->prepare($query);
$stmt->execute();

echo "<h3>1. Check if admin exists:</h3>";
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Admin found!<br>";
    echo "ID: " . $row['id'] . "<br>";
    echo "Name: " . $row['name'] . "<br>";
    echo "Email: " . $row['email'] . "<br>";
    echo "Role: " . $row['role'] . "<br>";
    echo "Password Hash: " . substr($row['password'], 0, 20) . "...<br>";
    
    echo "<h3>2. Test password verification:</h3>";
    $test_password = 'admin123';
    if (password_verify($test_password, $row['password'])) {
        echo "✓ Password 'admin123' is CORRECT!<br>";
    } else {
        echo "✗ Password 'admin123' is WRONG!<br>";
        echo "Creating new hash...<br>";
        $new_hash = password_hash($test_password, PASSWORD_BCRYPT);
        echo "New hash: " . $new_hash . "<br>";
        
        // Update the password
        $update = "UPDATE users SET password = :password WHERE email = 'admin@kowsalyacake.com'";
        $update_stmt = $db->prepare($update);
        $update_stmt->bindParam(':password', $new_hash);
        if ($update_stmt->execute()) {
            echo "<strong style='color:green'>✓ Password updated! Try logging in again with admin123</strong><br>";
        }
    }
} else {
    echo "✗ Admin user NOT found!<br>";
    echo "<h3>Creating admin user...</h3>";
    
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    $insert = "INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@kowsalyacake.com', :password, 'admin')";
    $insert_stmt = $db->prepare($insert);
    $insert_stmt->bindParam(':password', $hash);
    
    if ($insert_stmt->execute()) {
        echo "<strong style='color:green'>✓ Admin user created!</strong><br>";
        echo "Email: admin@kowsalyacake.com<br>";
        echo "Password: admin123<br>";
    }
}

echo "<hr>";
echo "<h3>3. All users in database:</h3>";
$all_users = "SELECT id, name, email, role FROM users";
$stmt = $db->query($all_users);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . $user['name'] . "</td>";
    echo "<td>" . $user['email'] . "</td>";
    echo "<td>" . $user['role'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<a href='login.php'>← Go to Login Page</a>";
?>