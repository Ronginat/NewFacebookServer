<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
http_response_code(400);
 
// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 

// get posted data
$data = json_decode(file_get_contents("php://input"));

// make sure data is not empty
if(
    !empty($data->username) &&
    !empty($data->password)
    ){
        // prepare user object
        $user = new User($db);
        $user->username = $data->username;
        $user->password = $data->password;

        if (!$user->is_exists($user->username)) {
        if ($user->create()) {
            // set response code - 201 created
           http_response_code(201);
   
           // tell the user
           echo json_encode(array("message" => "User signed up."));
        } else {
            // set response code - 503 service unavailable
            http_response_code(503);
    
            // tell the user
            echo json_encode(array("message" => "Unable to create user."));
        }
    } else {
        echo_error_and_die(400, "Username is taken.");
    }
} else {
    echo_error_and_die(400, "Unable to signup user. Data is incomplete.");
}

function echo_error_and_die($code, $message) {
    http_response_code($code);
    echo json_encode(array("message" => $message));
    die();
}
?>