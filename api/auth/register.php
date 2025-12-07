<?php
// api/auth/register.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->name) &&
    !empty($data->email) &&
    !empty($data->password)
){
    // Check if email already exists
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(array("message" => "Email already exists."));
    } else {
        $query = "INSERT INTO users (name, email, password, phone, address) 
                  VALUES (:name, :email, :password, :phone, :address)";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":name", $data->name);
        $stmt->bindParam(":email", $data->email);
        $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":phone", $data->phone);
        $stmt->bindParam(":address", $data->address);
        
        if($stmt->execute()){
            http_response_code(201);
            echo json_encode(array(
                "message" => "User registered successfully.",
                "user_id" => $db->lastInsertId()
            ));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to register user."));
        }
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to register. Data is incomplete."));
}
?>