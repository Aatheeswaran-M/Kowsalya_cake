<?php
// api/newsletter/subscribe.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email)){
    $query = "INSERT INTO newsletter_subscribers (email) VALUES (:email)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    
    try {
        if($stmt->execute()){
            http_response_code(201);
            echo json_encode(array("message" => "Subscribed successfully."));
        }
    } catch(PDOException $e) {
        if($e->getCode() == 23000){
            http_response_code(400);
            echo json_encode(array("message" => "Email already subscribed."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to subscribe."));
        }
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Email is required."));
}
?>