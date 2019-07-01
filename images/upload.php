<?php
// required headers
/* header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
http_response_code(400); */
 
// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';
include_once '../objects/post.php';
include_once '../objects/image.php';

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
 
    // get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // prepare friend object
    $image = new Image($db);
    
    // set ID property of record to read
    $fileToUpload = isset($_FILES['image']) ? $_FILES['image'] : echo_err_and_die(400, "Missing file content.");
    $originFileName = $fileToUpload['name'];
    $image->post_id = isset($_GET['id']) ? $_GET['id'] : echo_err_and_die(400, "Missing post id.");
    //$username = isset($_GET['username']) ? $_GET['username'] : echo_err_and_die(400, "Missing username.");
    
    // check if user exists
    /* $user = new User($db);
    if (!$user->is_exists($username)) {
        echo_err_and_die(500, "User not exists.");
    } */

    // check if post exists
    $post = new Post($db);
    if (!$post->is_exists($image->post_id)) {
        echo_err_and_die(500, "Post not exists.");
    }

    $timestamp = round(microtime(true) * 1000);//$_SERVER['REQUEST_TIME'];
    $extension = explode('.', $originFileName)[1];
    $image->file_name = $timestamp . '.' . $extension;

    
    $target_file = "uploads\\" . $image->file_name;
    if (move_uploaded_file($fileToUpload['tmp_name'], $target_file)) {
        $res = array();
        $res['message'] = "The file ". basename( $originFileName). " has been uploaded.";
        $res['url'] = "images\\" . $target_file;

        if ($image->post_image()) {
            //$res['message'] = "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
    
            // set response code - 200 OK
            http_response_code(200);
        
            // tell the user
            echo json_encode($res);
    
        } else {
            // set response code - 500 Internal Server Error
            http_response_code(500);
        
            // tell the user
            echo json_encode(array("message" => "Unable to save image name to db. " . $originFileName));
        }

    } else {
        // set response code - 500 Internal Server Error
        http_response_code(500);
        
        // tell the user
        echo json_encode(array("message" => "Sorry, there was an error uploading your file."));
    }
}


function echo_err_and_die($error_code, $err_message) {
    http_response_code($error_code);
    echo json_encode(array("message" => $err_message));
    exit(0);
    //die();
}
?>