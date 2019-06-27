<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
 
// include database and object files
include_once '../config/database.php';
include_once '../objects/post.php';
include_once '../objects/user.php';
 
// instantiate database and user object
$database = new Database();
$db = $database->getConnection();

// initialize object
$post = new Post($db);
$post->username = isset($_GET['username']) ? $_GET['username'] : echo_err_and_die(400, "Missing username.");

// check if user exists
$user = new User($db);
if (!($user->is_exists($post->username))) {
    echo_err_and_die(500, "User not exists.");
}
 
// query posts
$stmt = $post->get_feed();
//error_log(print_r(json_encode($stmt->fetch(PDO::FETCH_ASSOC)), TRUE));
$num = $stmt->rowCount();
 
// posts array
$posts_arr=array();
$posts_arr["records"]=array();

// check if more than 0 record found
if($num > 0){
 
    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
        $post_item=array(
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
 
        array_push($posts_arr["records"], $post_item);
    }

    // set response code - 200 OK
    http_response_code(200);
 
    // show posts data in json format
    echo json_encode($posts_arr);
} else{
    // no posts found
    // return an empty array
 
    // set response code - 200 OK
    http_response_code(200);
 
    // tell the user no posts found
    echo json_encode($posts_arr);
}

function echo_err_and_die($error_code, $err_message) {
    http_response_code($error_code);
    echo json_encode(array("message" => $err_message));
    die();
}

?>
 
