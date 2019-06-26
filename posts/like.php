<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
 
// get database connection
include_once '../config/database.php';
 
// instantiate post object
include_once '../objects/post.php';
include_once '../objects/user.php';
include_once '../objects/like.php';
 
$database = new Database();
$db = $database->getConnection();
 
$post = new Post($db);

$like = new Like($db);
$like->username = isset($_GET['username']) ? $_GET['username'] : echo_err_and_die(400, "Missing username.");
$like->post_id = isset($_GET['id']) ? $_GET['id'] : echo_err_and_die(400, "Missing post id.");

// check if user exists
$user = new User($db);
if (!($user->is_exists($like->username))) {
    echo_err_and_die(500, "User not exists.");
}
// check if post exists
if (!$post->is_exists($like->post_id)) {
    echo_err_and_die(500, "Post not found.");
}

if ($like->is_like()) {
    // do unlike
    if ($like->unlike()) {
        // post unliked
        // set response code - 200 ok
        http_response_code(200);

        // tell the user
        echo json_encode(array("message" => "Post was unliked."));

    } else{
        echo_err_and_die(500, "Unable to unlike this post.");
    }
} else {
    // do like
    if ($like->like()) {
        // post unliked
        // set response code - 200 ok
        http_response_code(200);

        // tell the user
        echo json_encode(array("message" => "Post was liked."));

    } else{
        echo_err_and_die(500, "Unable to like this post.");
    }
}
 
function echo_err_and_die($error_code, $err_message) {
    http_response_code($error_code);
    echo json_encode(array("message" => $err_message));
    die();
}
?>