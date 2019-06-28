<?php
// required headers
/* header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); */
 
// get database connection
include_once '../config/database.php';
 
// instantiate comment object
include_once '../objects/comment.php';
include_once '../objects/user.php';
include_once '../objects/post.php';

if($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    // Tell the Client we support invocations from * and 
    // that this preflight holds good for only 1 hour

    header("HTTP/1.1 200 OK");
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, origin, accept, X-Requested-With');
    header('Access-Control-Max-Age: 3600');
    header("Content-Type: application/json");
    exit(0);
  
} elseif($_SERVER['REQUEST_METHOD'] == "POST") {
    // Handle POST by first getting the json POST body, 
    // and then doing something to it, and then sending results to the client
   
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    $database = new Database();
    $db = $database->getConnection();
     
    $comment = new Comment($db);
     
    // get posted data
    $data = json_decode(file_get_contents("php://input"));
     
    // make sure data is not empty
    if(
        !empty($data->username) &&
        !empty($data->post_id) &&
        !empty($data->content)
    ){
     
        // check if user exists
        $user = new User($db);
        if (!($user->is_exists($data->username))) {
            echo_err_and_die(500, "User not exists.");
        }
        // check if post exists
        $post = new Post($db);
        if (!($post->is_exists($data->post_id))) {
            echo_err_and_die(500, "Post not exists.");
        }
    
        // set comment property values
        $comment->username = $data->username;
        $comment->post_id = $data->post_id;
        $comment->content = $data->content;
     
        // create the comment
        if($comment->create()){
            $comment->get_by_id();
            $res = array();
            $res['message'] = "Comment was created.";
            $res['comment'] = $comment;
     
            // set response code - 201 created
            http_response_code(201);
     
            // tell the user
            //echo json_encode(array("message" => "Comment was created."));
            echo json_encode($res);
        }
     
        // if unable to create the comment, tell the user
        else{
     
            // set response code - 503 service unavailable
            http_response_code(503);
     
            // tell the user
            echo json_encode(array("message" => "Unable to create comment."));
        }
    }
     
    // tell the user data is incomplete
    else{
     
        // set response code - 400 bad request
        http_response_code(400);
     
        // tell the user
        echo json_encode(array("message" => "Unable to create comment. Data is incomplete."));
    }
}
 

function echo_err_and_die($error_code, $err_message) {
    http_response_code($error_code);
    echo json_encode(array("message" => $err_message));
    exit(0);
    //die();
}
?>