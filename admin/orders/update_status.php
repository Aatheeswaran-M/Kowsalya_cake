<?php
// admin/orders/update_status.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->order_id) && !empty($data->status)){
    $query = "UPDATE orders SET status = :status WHERE id = :order_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":status", $data->status);
    $stmt->bindParam(":order_id", $data->order_id);
    
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode(array("message" => "Order status updated successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update order status."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update order. Data is incomplete."));
}
?>