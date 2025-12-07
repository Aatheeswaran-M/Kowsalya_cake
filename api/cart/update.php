<?php
// api/cart/update.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->cart_id) && !empty($data->quantity)){
    $query = "UPDATE cart SET quantity = :quantity WHERE id = :cart_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":quantity", $data->quantity);
    $stmt->bindParam(":cart_id", $data->cart_id);
    
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode(array("message" => "Cart updated successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update cart."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update cart. Data is incomplete."));
}
?>