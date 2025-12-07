<?php
// api/cart/read.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : die();

$query = "SELECT c.id as cart_id, c.quantity, 
          p.id as product_id, p.name, p.price, p.image, p.weight
          FROM cart c
          INNER JOIN products p ON c.product_id = p.id
          WHERE c.user_id = :user_id";

$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

$cart_items = array();
$total = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $subtotal = $row['price'] * $row['quantity'];
    $total += $subtotal;
    
    $cart_items[] = array(
        "cart_id" => $row['cart_id'],
        "product_id" => $row['product_id'],
        "name" => $row['name'],
        "price" => $row['price'],
        "image" => $row['image'],
        "weight" => $row['weight'],
        "quantity" => $row['quantity'],
        "subtotal" => $subtotal
    );
}

http_response_code(200);
echo json_encode(array(
    "count" => count($cart_items),
    "total" => $total,
    "items" => $cart_items
));
?>