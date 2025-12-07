<?php
// api/cart/add.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->user_id) && !empty($data->product_id)){
    // Check if item already exists in cart
    $check_query = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":user_id", $data->user_id);
    $check_stmt->bindParam(":product_id", $data->product_id);
    $check_stmt->execute();
    
    if($check_stmt->rowCount() > 0){
        // Update quantity
        $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
        $new_quantity = $row['quantity'] + (isset($data->quantity) ? $data->quantity : 1);
        
        $update_query = "UPDATE cart SET quantity = :quantity WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(":quantity", $new_quantity);
        $update_stmt->bindParam(":id", $row['id']);
        
        if($update_stmt->execute()){
            http_response_code(200);
            echo json_encode(array("message" => "Cart updated successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update cart."));
        }
    } else {
        // Insert new item
        $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $data->user_id);
        $stmt->bindParam(":product_id", $data->product_id);
        $quantity = isset($data->quantity) ? $data->quantity : 1;
        $stmt->bindParam(":quantity", $quantity);
        
        if($stmt->execute()){
            http_response_code(201);
            echo json_encode(array("message" => "Item added to cart successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to add item to cart."));
        }
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to add to cart. Data is incomplete."));
}
?>