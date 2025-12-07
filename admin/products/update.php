<?php
// admin/products/update.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)){
    $query = "UPDATE products SET 
              name = :name,
              description = :description,
              category = :category,
              price = :price,
              weight = :weight,
              image = :image,
              stock_quantity = :stock_quantity
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":id", $data->id);
    $stmt->bindParam(":name", $data->name);
    $stmt->bindParam(":description", $data->description);
    $stmt->bindParam(":category", $data->category);
    $stmt->bindParam(":price", $data->price);
    $stmt->bindParam(":weight", $data->weight);
    $stmt->bindParam(":image", $data->image);
    $stmt->bindParam(":stock_quantity", $data->stock_quantity);
    
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode(array("message" => "Product updated successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update product."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update product. Data is incomplete."));
}
?>