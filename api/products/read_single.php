<?php
// api/products/read_single.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : die();

$query = "SELECT id, name, description, category, price, weight, image, stock_quantity, rating 
          FROM products WHERE id = :id AND is_active = 1";

$stmt = $db->prepare($query);
$stmt->bindParam(":id", $id);
$stmt->execute();

if($stmt->rowCount() > 0){
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $product = array(
        "id" => $row['id'],
        "name" => $row['name'],
        "description" => $row['description'],
        "category" => $row['category'],
        "price" => $row['price'],
        "weight" => $row['weight'],
        "image" => $row['image'],
        "stock_quantity" => $row['stock_quantity'],
        "rating" => $row['rating']
    );
    
    http_response_code(200);
    echo json_encode($product);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Product not found."));
}
?>