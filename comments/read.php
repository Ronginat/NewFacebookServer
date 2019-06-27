<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

http_response_code(400);
//echo json_encode(array("message" => "Missing post_id."));
 
// include database and object files
include_once '../config/database.php';
include_once '../objects/comment.php';
 
// instantiate database and comment object
$database = new Database();
$db = $database->getConnection();
 
// initialize object
$comment = new Comment($db);

// set ID property of record to read
$comment->post_id = isset($_GET['post_id']) ? $_GET['post_id'] : echo_err_and_die();
 
// query comments
$stmt = $comment->read();
$num = $stmt->rowCount();
 
// comments array
$comments_arr=array();
$comments_arr["records"]=array();

// check if more than 0 record found
if($num > 0) {
  
    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
 
        $comment_item=array(
            "id" => $id,
            "post_id" => $post_id,
            "author" => $username,
            "date" => $date,
            "content" => $content,
            //"description" => html_entity_decode($description),
        );
 
        array_push($comments_arr["records"], $comment_item);
    }
 
    // set response code - 200 OK
    http_response_code(200);
 
    // show comments data in json format
    echo json_encode($comments_arr);
} else{
    // no comments found
 
    // set response code - 200 OK
    http_response_code(200);
 
    // tell the comment no products found
    echo json_encode($comments_arr);
}

function echo_err_and_die() {
    http_response_code(500);
    echo json_encode(array("message" => "Missing post_id."));
    die();
}
?>
 
