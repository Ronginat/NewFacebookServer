<?php
class Comment{
 
    // database connection and table name
    private $conn;
    private $table_name = "comments";
 
    // object properties
    public $id;
    public $post_id;
    public $username;
    public $date;
    public $content;
    public $author;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read posts
    function read(){
        
        // select all query
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE 
                    post_id = ?
                ORDER BY
                    date ASC";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // bind id of post to be updated
        $stmt->bindParam(1, $this->post_id);

        // execute query
        $stmt->execute();
    
        return $stmt;
    }

    // create post
    function create(){
    
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    username=:username, post_id=:post_id, content=:content";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->post_id=htmlspecialchars(strip_tags($this->post_id));
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->content=htmlspecialchars(strip_tags($this->content));
    
        // bind values
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":content", $this->content);
    
        // execute query
        if(!$stmt->execute()){
            return false;
        }
    
        $this->id = $this->conn->lastInsertId();
        return true;
    }

    function get_by_id() {
        // query to read single record
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "                    
                WHERE
                    id = ?";
 
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // bind id of user to be updated
        $stmt->bindParam(1, $this->id);
    
        // execute query
        $stmt->execute();

        if (!$stmt->rowCount() > 0) {
            // false means that post not exists
            return false;
        }
    
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

         // set values to object properties
        $this->id = $row['id'];
        $this->username = null;
        $this->author = $row['username'];
        $this->date = $row['date'];
        $this->content = $row['content'];
        return true;
    }
}