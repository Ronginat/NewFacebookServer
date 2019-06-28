<?php
// required headers
/* header("HTTP/1.1 200 OK");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); */
 
// get database connection
include_once '../config/database.php';
 
// instantiate post object
include_once '../objects/post.php';
include_once '../objects/user.php';

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
     
    $post = new Post($db);
     
    // get posted data
    $data = json_decode(file_get_contents("php://input"));
    
    // make sure data is not empty
    if(
        !empty($data->username) &&
        isset($data->private) && is_bool($data->private) &&
        !empty($data->content)
    ){
    
        // check if user exists
        $user = new User($db);
        if (!($user->is_exists($data->username))) {
            echo_err_and_die(500, "User not exists.");
        }
     
        // set post property values
        $post->username = $data->username;
        $post->private = $data->private ? 1 : 0;
        $post->content = $data->content;
        //$post->created = date('Y-m-d H:i:s');
        
        // create the post
        if($post->create()){
     
            // set response code - 201 created
            http_response_code(201);
            
            $res = array();
            $res['message'] = "Post was created.";
            $data = $post->get_post_by_id()->fetch(PDO::FETCH_ASSOC);
            extract($data);
            $res['post'] = array(
                "id" => $id,
                "author" => $username,
                "private" => $private > 0,
                "date" => $date,
                "content" => html_entity_decode($content),
                "likes" => (int) $likes,
                "meLike" => $meLike > 0,
                "image" => $images,
                "comments" => $comments
            );
            echo json_encode($res);
     
            // tell the user
            //echo json_encode(array("message" => "Post was created."));
        }
     
        // if unable to create the post, tell the user
        else{
     
            // set response code - 503 service unavailable
            http_response_code(503);
     
            // tell the user
            echo json_encode(array("message" => "Unable to create post."));
        }
    }
     
    // tell the user data is incomplete
    else{
     
        // set response code - 400 bad request
        http_response_code(400);
     
        // tell the user
        echo json_encode(array("message" => "Unable to create post. Data is incomplete."));
    }
}
 

function echo_err_and_die($error_code, $err_message) {
    http_response_code($error_code);
    echo json_encode(array("message" => $err_message));
    exit(0);
    //die();
}
?>