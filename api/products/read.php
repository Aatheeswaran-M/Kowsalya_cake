<?php
// api/products/read.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT id, name, description, category, price, weight, image, stock_quantity, rating 
          FROM products WHERE is_active = 1";

if(!empty($category)){
    $query .= " AND category = :category";
}

if(!empty($search)){
    $query .= " AND (name LIKE :search OR description LIKE :search)";
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);

if(!empty($category)){
    $stmt->bindParam(":category", $category);
}

if(!empty($search)){
    $search_term = "%{$search}%";
    $stmt->bindParam(":search", $search_term);
}

$stmt->execute();

$products = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $products[] = array(
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
}

http_response_code(200);
echo json_encode(array(
    "count" => count($products),
    "products" => $products
));
?>