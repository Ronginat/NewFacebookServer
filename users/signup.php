<?php
include_once '../config/database.php';
include_once '../objects/user.php';

//error_log(print_r('inside signup1, '.$_SERVER['REQUEST_METHOD'], TRUE));

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
    $data = json_decode(file_get_contents("php://input"));
    
    // make sure data is not empty
    if(
        !empty($data->username) &&
        !empty($data->password)
        ){
        // prepare user object
        $user = new User($db);
        $user->username = $data->username;
        $user->password = calculate_password($data->password);

        if (!$user->is_exists($user->username)) {
            if ($user->create()) {
                // set response code - 201 created
                http_response_code(201);
        
                // tell the user
                echo json_encode(array("message" => "User signed up."));
            } else {
                // set response code - 503 service unavailable
                http_response_code(503);
        
                // tell the user
                echo json_encode(array("message" => "Unable to create user."));
            }
        } else {
            echo_error_and_die(500, "Username is taken.");
        }
    } else {
        echo_error_and_die(400, "Unable to signup user. Data is incomplete.");
    }
}

/* Returns the encrypted password of the given string. */
function calculate_password($pass) {
    $pass=$pass[0].$pass.$pass[0]; // 12345-->123455
    $pass=md5($pass);
    return $pass;
}

function echo_error_and_die($code, $message) {
    http_response_code($code);
    echo json_encode(array("message" => $message));
    exit(0);
    //die();
}
?>