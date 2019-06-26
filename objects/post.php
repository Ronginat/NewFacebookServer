<?php
class Post{
 
    // database connection and table name
    private $conn;
    private $table_name = "posts";
 
    // object properties
    public $id;
    public $username;
    public $private;
    public $date;
    public $content;
    public $likes;
    public $meLike;
    public $comments;
    //public $images;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    /**
     * Not usued method
     */
    // read posts
    function read_all(){
        
        // select all query
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                ORDER BY
                    date DESC";
                //SELECT id, username, date, content, IF(private, 'true', 'false') private
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // execute query
        $stmt->execute();
    
        return $stmt;
    }

    function get_feed_not_working() {
        $query = "SELECT p.id, p.username, p.private, p.date, p.content, COUNT(l.post_id) AS likes, 
                (SELECT 
                    JSON_ARRAY(
                        JSON_OBJECT(
                            'id', c.id,
                            'username' c.username,
                            'date', c.date,
                            'content', c.content
                        )
                    )
                    FROM posts p2 LEFT JOIN comments c ON p2.id = c.post_id GROUP BY p.id
                ) AS comments,
                (SELECT COUNT(l2.username)
                    FROM likes l2 WHERE l2.post_id = p.id AND l2.username = :username) as meLike
                FROM " . $this->table_name . " p
                LEFT JOIN likes l
                ON p.id = l.post_id
                WHERE
                    (p.username IN(SELECT f.user_res FROM friends f WHERE f.user_req = :username) AND p.private = 0)
                    OR
                    p.username = :username
                GROUP BY
                    p.id
                ORDER BY
                    p.date DESC";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // bind values
        $stmt->bindParam(":username", $this->username);

        // execute query
        $stmt->execute();
    
        return $stmt;
    }

    function get_feed() {
        $query = "SELECT p.id, p.username, p.private, p.date, p.content, COUNT(l.post_id) AS likes, 
                (SELECT COUNT(l2.username)
                    FROM likes l2 WHERE l2.post_id = p.id AND l2.username = :username) as meLike
                FROM " . $this->table_name . " p
                LEFT JOIN likes l
                ON p.id = l.post_id
                WHERE
                    (p.username IN(SELECT f.user_res FROM friends f WHERE f.user_req = :username) AND p.private = 0)
                    OR
                    p.username = :username
                GROUP BY
                    p.id
                ORDER BY
                    p.date DESC";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // bind values
        $stmt->bindParam(":username", $this->username);

        // execute query
        $stmt->execute();
    
        return $stmt;
    }

    // read posts
    function get_user_posts($my_profile, $reqUser){
        
        // "SELECT p.id, p.username, p.date, p.content, IF(private, 'true', 'false') private"
        // select all query
        $query_private = "SELECT p.id, p.username, p.private, p.date, p.content, COUNT(l.post_id) AS likes, 
                (SELECT COUNT(l2.username)
                    FROM likes l2 WHERE l2.post_id = p.id AND l2.username = :username) as meLike
                FROM " . $this->table_name . " p
                LEFT JOIN likes l
                ON p.id = l.post_id
                WHERE
                    p.username = :username
                GROUP BY
                    p.id
                ORDER BY
                    p.date DESC";

        $query_public = "SELECT p.id, p.username, p.private, p.date, p.content, COUNT(l.post_id) AS likes, 
                (SELECT COUNT(l2.username)
                    FROM likes l2 WHERE l2.post_id = p.id AND l2.username = :reqUser) as meLike
                FROM " . $this->table_name . " p
                LEFT JOIN likes l
                ON p.id = l.post_id
                WHERE
                    p.username = :username AND p.private = 0
                GROUP BY
                    p.id
                ORDER BY
                    p.date DESC";
    
        // prepare query statement
        $stmt = $this->conn->prepare($my_profile ? $query_private : $query_public);

        // bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":reqUser", $reqUser);
    
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
                    username=:username, private=:private, content=:content";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->private=htmlspecialchars(strip_tags($this->private));
        $this->content=htmlspecialchars(strip_tags($this->content));
    
        // bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":private", $this->private);
        $stmt->bindParam(":content", $this->content);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    // update post private flag
    function update_privacy(){
    
        // query to insert record
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                    private = :private
                WHERE
                    id = :id";
    
        // prepare query
        $stmt = $this->conn->prepare($query);
    
        // new privacy attribute
        $privacy = 1 - $this->private;
        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":private", $privacy);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    function get_post_by_id() {
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
        $this->username = $row['username'];
        $this->date = $row['date'];
        $this->private = $row['private'] > 0;
        $this->content = $row['content'];
        return true;
    }

    function is_exists($id) {
        // query to read single record
        $query = "SELECT
                   id
                FROM
                    " . $this->table_name . "                    
                WHERE
                    id = ?
                LIMIT
                    0,1";
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // bind id of user to be updated
        $stmt->bindParam(1, $id);
    
        // execute query
        $stmt->execute();
    
        // get retrieved row
        return ($stmt->rowCount() > 0);
    }
}