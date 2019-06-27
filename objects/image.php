<?php
class Image{
 
    // database connection and table name
    private $conn;
    private $table_name = "images";
 
    // object properties
    public $post_id;
    public $file_name;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
    
    // save uploaded image name
    function post_image() {
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    post_id=:id, file_name=:file";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->post_id=htmlspecialchars(strip_tags($this->post_id));
        $this->username=htmlspecialchars(strip_tags($this->file_name));
    
        // bind values
        $stmt->bindParam(":id", $this->post_id);
        $stmt->bindParam(":file", $this->file_name);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    function is_exists() {
        // select query
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
                    file_name = :file";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // bind values
        //$stmt->bindParam(":id", $this->post_id);
        $stmt->bindParam(":file", $this->file_name);
    
        // execute query
        $stmt->execute();
    
        return ($stmt->rowCount() > 0);
    }
}