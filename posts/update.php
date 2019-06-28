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
 
$database = new Database();
$db = $database->getConnection();
 
$post = new Post($db);
$post->id = isset($_GET['id']) ? $_GET['id'] : echo_err_and_die(400, "Missing post id.");
$username = isset($_GET['username']) ? $_GET['username'] : echo_err_and_die(400, "Missing username.");

// check if user exists
$user = new User($db);
if (!($user->is_exists($username))) {
    echo_err_and_die(500, "User not exists.");
}

if ($post->populate_object_with_post_by_id()) {
    // compare given username with post username
    if($username === $post->username) {
        // can edit post private attribute
        if ($post->update_privacy()) {
            // post updated
            $res = array();
            $res['message'] = "Post was updated.";
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

            // set response code - 200 ok
            http_response_code(200);
    
            // tell the user
            echo json_encode($res);
            //echo json_encode(array("message" => "Post was updated."));

        } else{
            echo_err_and_die(500, "Unable to update this post.");
        }
    } else {
        echo_err_and_die(403, "Can't edit other user's posts.");
    }
} else {
    echo_err_and_die(404, "Post not found.");
}
 
function echo_err_and_die($error_code, $err_message) {
    http_response_code($error_code);
    echo json_encode(array("message" => $err_message));
    die();
}
?>