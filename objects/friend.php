<?php
class Friend{
 
    // database connection and table name
    private $conn;
    private $table_name = "friends";
 
    // object properties
    public $user_req;
    public $user_res;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read friends of a user
    function friends_of_user($username){
    
        // select all query
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
                    user_req = ?";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // bind id of user to be updated
        $stmt->bindParam(1, $username);
    
        // execute query
        $stmt->execute();
    
        return $stmt;
    }

    function are_friends() {
        // select all query
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
                    (user_req = :user1 AND user_res = :user2)";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // bind id of user to be updated
        $stmt->bindParam(":user1", $this->user_req);
        $stmt->bindParam(":user2", $this->user_res);
    
        // execute query
        $stmt->execute();
    
        return ($stmt->rowCount() > 0);
    }

    // create friend request
    function post_friend_request() {
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "(user_req, user_res) 
                VALUES
                    (:user1, :user2),
                    (:user2, :user1)";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->user_req=htmlspecialchars(strip_tags($this->user_req));
        $this->user_res=htmlspecialchars(strip_tags($this->user_res));
    
        // bind values
        $stmt->bindParam(":user1", $this->user_req);
        $stmt->bindParam(":user2", $this->user_res);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    function delete_friend_request() {
        // delete query
        $query = "DELETE 
            FROM " . $this->table_name . " 
            WHERE 
                (user_req = :user1 AND user_res = :user2)
                OR
                (user_req = :user2 AND user_res = :user1)";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->user_req=htmlspecialchars(strip_tags($this->user_req));
        $this->user_res=htmlspecialchars(strip_tags($this->user_res));
    
        // bind id of record to delete
        $stmt->bindParam(":user1", $this->user_req);
        $stmt->bindParam(":user2", $this->user_res);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }
}