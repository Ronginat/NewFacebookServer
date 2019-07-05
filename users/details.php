<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');


// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';
include_once '../objects/friend.php';
include_once '../objects/game.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// prepare friend object
$friend = new Friend($db);
 
// set ID property of record to read
$friend->user_req = isset($_GET['reqUser']) ? $_GET['reqUser'] : echo_err_and_die(400, "Missing username.");
$friend->user_res = isset($_GET['resUser']) ? $_GET['resUser'] : echo_err_and_die(400, "Missing username.");

$invitation = new Game($db);

$invitation->receiver = $_GET['reqUser']; // current user
$invitation->sender = $_GET['resUser']; // 
 
// check if both users exists
$user = new User($db);
if (!$user->is_exists($friend->user_req) || !$user->is_exists($friend->user_res)) {
    echo_err_and_die(500, "User not exists.");
}
   
http_response_code(200);

$res = array();

$res['friends'] = $friend->are_friends();
$res['invited'] = $invitation->did_invite();
$invitation->sender = $_GET['reqUser']; // current user
$invitation->receiver = $_GET['resUser']; // 
$res['inviting'] = $invitation->did_invite();

// tell the user
echo json_encode($res);


function echo_err_and_die($error_code, $err_message) {
    http_response_code($error_code);
    echo json_encode(array("message" => $err_message));
    exit(0);
}
?>