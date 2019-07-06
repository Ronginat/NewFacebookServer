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
include_once '../objects/game.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// prepare game object
$invitation = new Game($db);
 
// set ID property of record to read
$invitation->sender = isset($_GET['reqUser']) ? $_GET['reqUser'] : echo_err_and_die(400, "Missing username.");
$invitation->receiver = isset($_GET['resUser']) ? $_GET['resUser'] : echo_err_and_die(400, "Missing username.");
 
// check if both users exists
$user = new User($db);
if (!$user->is_exists($invitation->sender) || !$user->is_exists($invitation->receiver)) {
    echo_err_and_die(500, "User not exists.");
}

$friend = new Friend($db);
$friend->user_req = $_GET['reqUser'];
$friend->user_res = $_GET['resUser'];

if (!$friend->are_friends()) {
    echo_err_and_die(500, "You can invite only friends, please add this user to your friends list");
}

// if users are friends, unfriend them, else make them friends
if ($invitation->did_invite()) {
    // delete friend record
    if($invitation->delete_invite_request()) {
 
        // set response code - 201 created
        http_response_code(200);
 
        // tell the user
        echo json_encode(array("message" => "Invitation was deleted."));
    }
 
    // if unable to create the game invitation, tell the user
    else {
        // set response code - 503 service unavailable
        http_response_code(503);
 
        // tell the user
        echo json_encode(array("message" => "Unable to delete invitation."));
    }
} else {
    // create the game record
    if($invitation->post_invite_request()) {
 
        // set response code - 201 created
        http_response_code(200);
 
        // tell the user
        echo json_encode(array("message" => "Invited " . $invitation->receiver . " to play."));
    }
 
    // if unable to create the game invitation, tell the user
    else {
        // set response code - 503 service unavailable
        http_response_code(503);
 
        // tell the user
        echo json_encode(array("message" => "Unable to invite " . $invitation->receiver . "."));
    }
}

function echo_err_and_die($error_code, $err_message) {
    http_response_code($error_code);
    echo json_encode(array("message" => $err_message));
    exit(0);
}
?>