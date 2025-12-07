<?php
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$email = 'admin@kowsalyacake.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

// Check if admin exists
$check = $db->prepare("SELECT id FROM users WHERE email = :email");
$check->bindParam(':email', $email);
$check->execute();

if ($check->rowCount() > 0) {
    // Update existing admin
    $query = "UPDATE users SET password = :password WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':password', $hash);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    echo "Admin password reset successfully!<br>";
} else {
    // Create new admin
    $query = "INSERT INTO users (name, email, password, role) VALUES ('Admin', :email, :password, 'admin')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hash);
    $stmt->execute();
    echo "Admin user created successfully!<br>";
}

echo "Email: admin@kowsalyacake.com<br>";
echo "Password: admin123<br>";
echo "New hash: " . $hash;
?>