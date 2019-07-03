<?php
class Game{
 
    // database connection and table name
    private $conn;
    private $table_name = "invites";
 
    // object properties
    public $sender;
    public $receiver;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    function did_invite() {
        // select all query
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
                    (sender = :sender AND receiver = :receiver)";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // bind id of user to be updated
        $stmt->bindParam(":sender", $this->sender);
        $stmt->bindParam(":receiver", $this->receiver);
    
        // execute query
        $stmt->execute();
    
        return ($stmt->rowCount() > 0);
    }

    // create friend request
    function post_invite_request() {
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "(sender, receiver) 
                VALUES
                    (:sender, :receiver)";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->sender=htmlspecialchars(strip_tags($this->sender));
        $this->receiver=htmlspecialchars(strip_tags($this->receiver));
    
        // bind values
        $stmt->bindParam(":sender", $this->sender);
        $stmt->bindParam(":receiver", $this->receiver);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    function delete_invite_request() {
        // delete query
        $query = "DELETE 
            FROM " . $this->table_name . " 
            WHERE 
                (sender = :sender AND receiver = :reciever)";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->sender=htmlspecialchars(strip_tags($this->sender));
        $this->receiver=htmlspecialchars(strip_tags($this->receiver));
    
        // bind id of record to delete
        $stmt->bindParam(":sender", $this->sender);
        $stmt->bindParam(":reciever", $this->receiver);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }
}