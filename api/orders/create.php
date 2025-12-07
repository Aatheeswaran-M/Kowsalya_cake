<?php
// api/orders/create.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->user_id) &&
    !empty($data->shipping_address) &&
    !empty($data->phone) &&
    !empty($data->items)
){
    try {
        $db->beginTransaction();
        
        // Calculate total
        $total_amount = 0;
        foreach($data->items as $item){
            $total_amount += $item->price * $item->quantity;
        }
        
        // Create order
        $query = "INSERT INTO orders (user_id, total_amount, shipping_address, phone, payment_method, notes) 
                  VALUES (:user_id, :total_amount, :shipping_address, :phone, :payment_method, :notes)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $data->user_id);
        $stmt->bindParam(":total_amount", $total_amount);
        $stmt->bindParam(":shipping_address", $data->shipping_address);
        $stmt->bindParam(":phone", $data->phone);
        $payment_method = isset($data->payment_method) ? $data->payment_method : 'Cash on Delivery';
        $stmt->bindParam(":payment_method", $payment_method);
        $notes = isset($data->notes) ? $data->notes : '';
        $stmt->bindParam(":notes", $notes);
        
        $stmt->execute();
        $order_id = $db->lastInsertId();
        
        // Insert order items
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                       VALUES (:order_id, :product_id, :quantity, :price, :subtotal)";
        
        $item_stmt = $db->prepare($item_query);
        
        foreach($data->items as $item){
            $subtotal = $item->price * $item->quantity;
            $item_stmt->bindParam(":order_id", $order_id);
            $item_stmt->bindParam(":product_id", $item->product_id);
            $item_stmt->bindParam(":quantity", $item->quantity);
            $item_stmt->bindParam(":price", $item->price);
            $item_stmt->bindParam(":subtotal", $subtotal);
            $item_stmt->execute();
        }
        
        // Clear cart
        $clear_cart = "DELETE FROM cart WHERE user_id = :user_id";
        $clear_stmt = $db->prepare($clear_cart);
        $clear_stmt->bindParam(":user_id", $data->user_id);
        $clear_stmt->execute();
        
        $db->commit();
        
        http_response_code(201);
        echo json_encode(array(
            "message" => "Order placed successfully.",
            "order_id" => $order_id,
            "total_amount" => $total_amount
        ));
        
    } catch(Exception $e){
        $db->rollBack();
        http_response_code(503);
        echo json_encode(array("message" => "Unable to place order. " . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to place order. Data is incomplete."));
}
?>