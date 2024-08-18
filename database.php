<?php
require_once(LIB_PATH . DS . "config.php");

class Database {
    private $conn;
    public $last_query;
    
    function __construct() {
        $this->open_connection();
        $this->real_escape_string_exists = function_exists("mysqli_real_escape_string");
    }
    
    public function open_connection() {
        $this->conn = mysqli_connect(server, user, pass);
        if (!$this->conn) {
            die("Problem in database connection! Contact administrator!");
        }

        $db_select = mysqli_select_db($this->conn, database_name);
        if (!$db_select) {
            die("Problem in selecting database! Contact administrator!");
        }
    }
    
    public function setQuery($sql = '') {
        $this->last_query = $sql;
    }
    
    public function executeQuery() {
        $result = mysqli_query($this->conn, $this->last_query);
        $this->confirm_query($result);
        return $result;
    }
    
    private function confirm_query($result) {
        if (!$result) {
            $this->error_no = mysqli_errno($this->conn);
            $this->error_msg = mysqli_error($this->conn);
            return false;
        }
        return $result;
    }
    
    public function loadResultList($key = '') {
        $cur = $this->executeQuery();
        
        $array = [];
        while ($row = mysqli_fetch_object($cur)) {
            if ($key) {
                $array[$row->$key] = $row;
            } else {
                $array[] = $row;
            }
        }
        mysqli_free_result($cur);
        return $array;
    }
    
    public function loadSingleResult() {
        $cur = $this->executeQuery();
        $data = null;
        if ($row = mysqli_fetch_object($cur)) {
            $data = $row;
        }
        mysqli_free_result($cur);
        return $data;
    }
    
    public function getFieldsOnOneTable($tbl_name) {
        $this->setQuery("DESC " . $tbl_name);
        $rows = $this->loadResultList();
        
        $fields = [];
        foreach ($rows as $row) {
            $fields[] = $row->Field;
        }
        
        return $fields;
    }
    
    public function fetch_array($result) {
        return mysqli_fetch_array($result);
    }
    
    public function num_rows($result_set) {
        return mysqli_num_rows($result_set);
    }
    
    public function insert_id() {
        return mysqli_insert_id($this->conn);
    }
    
    public function affected_rows() {
        return mysqli_affected_rows($this->conn);
    }
    
    public function escape_value($value) {
        if ($this->real_escape_string_exists) {
            // mysqli_real_escape_string will handle escaping
            $value = mysqli_real_escape_string($this->conn, $value);
        } else {
            // Older versions of PHP (not recommended)
            $value = addslashes($value);
        }
        return $value;
    }
    
    public function close_connection() {
        if (isset($this->conn)) {
            mysqli_close($this->conn);
            unset($this->conn);
        }
    }
}

$mydb = new Database();
?>
