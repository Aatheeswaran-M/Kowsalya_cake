<?php
// api/orders/read.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : die();

$query = "SELECT o.id, o.total_amount, o.status, o.payment_method, o.payment_status,
          o.shipping_address, o.phone, o.notes, o.created_at,
          GROUP_CONCAT(p.name SEPARATOR ', ') as products
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          WHERE o.user_id = :user_id
          GROUP BY o.id
          ORDER BY o.created_at DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

$orders = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $orders[] = $row;
}

http_response_code(200);
echo json_encode(array(
    "count" => count($orders),
    "orders" => $orders
));
?>