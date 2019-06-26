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
include_once '../objects/friend.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// prepare friend object
$friend = new Friend($db);
 
// set ID property of record to read
$friend->user_req = isset($_GET['reqUser']) ? $_GET['reqUser'] : echo_err_and_die(400, "Missing username.");
$friend->user_res = isset($_GET['resUser']) ? $_GET['resUser'] : echo_err_and_die(400, "Missing username.");
 
// check if both users exists
$user = new User($db);
if (!$user->is_exists($friend->user_req) || !$user->is_exists($friend->user_res)) {
    echo_err_and_die(500, "User not exists.");
}

// if users are friends, unfriend them, else make them friends
if ($friend->are_friends()) {
    // delete friend record
    if($friend->delete_friend_request()){
 
        // set response code - 201 created
        http_response_code(200);
 
        // tell the user
        echo json_encode(array("message" => "Friend request was deleted."));
    }
 
    // if unable to create the post, tell the user
    else{
        // set response code - 503 service unavailable
        http_response_code(503);
 
        // tell the user
        echo json_encode(array("message" => "Unable to delete friend request."));
    }
} else {
    // create the friend record
    if($friend->post_friend_request()){
 
        // set response code - 201 created
        http_response_code(200);
 
        // tell the user
        echo json_encode(array("message" => "Friend request was created."));
    }
 
    // if unable to create the post, tell the user
    else{
        // set response code - 503 service unavailable
        http_response_code(503);
 
        // tell the user
        echo json_encode(array("message" => "Unable to create friend request."));
    }
}

function echo_err_and_die($error_code, $err_message) {
    http_response_code($error_code);
    echo json_encode(array("message" => $err_message));
    die();
}
?>