<?php
class Like{
 
    // database connection and table name
    private $conn;
    private $table_name = "likes";
 
    // object properties
    public $username;
    public $post_id;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
    
    // do like
    function like(){
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    username=:username, post_id=:post_id";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->post_id=htmlspecialchars(strip_tags($this->post_id));
        $this->username=htmlspecialchars(strip_tags($this->username));
    
        // bind values
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->bindParam(":username", $this->username);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    // do unlike
    function unlike(){    
        // query to delete record
        $query = "DELETE
                FROM " . $this->table_name . " 
                WHERE
                    username=:username AND post_id=:post_id";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":post_id", $this->post_id);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    function is_like() {
        // select query
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
                    username = :username AND post_id = :post_id";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // bind id of user to be updated
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":post_id", $this->post_id);
    
        // execute query
        $stmt->execute();
    
        return ($stmt->rowCount() > 0);
    }
}