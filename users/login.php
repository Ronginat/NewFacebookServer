<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
http_response_code(400);
 
// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// prepare user object
$user = new User($db);
 
// set ID property of record to read
$user->username = isset($_GET['username']) ? $_GET['username'] : echo_error_and_die();
$inputPassword = isset($_GET['password']) ? $_GET['password'] : echo_error_and_die();
 
// read the details of user to be edited
$user->readOne();
 
if($user->password!=null){
    if($user->password==$inputPassword){   
        // create array
        $user_arr = array(
            "username" =>  $user->username,
            "password" => $user->password
        );
    
        // set response code - 200 OK
        http_response_code(200);
    
        // make it json format
        echo json_encode($user_arr);
    } else {
        // password not correct
        // set response code - 400 Bad Request
        http_response_code(500);
    
        // tell the user worng password
        echo json_encode(array("message" => "Wrong password."));    
    }
}
 
else{
    // set response code - 404 Not found
    http_response_code(404);
 
    // tell the user user does not exist
    echo json_encode(array("message" => "User does not exist."));
}

function echo_error_and_die() {
    http_response_code(400);
    echo json_encode(array("message" => "Missing username or password."));
    die();
}
?>