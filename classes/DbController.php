<?php

class DbController {
    private $host;
    private $username;
    private $password;
    private $db;
    public $connect;

    public function __construct() {
        $this->connect();
    }

    public function connect() {
        // $this->host = "awseb-e-uppn8nyswy-stack-awsebrdsdatabase-rxuf5oalh8pc.cov4c4okst6r.us-east-1.rds.amazonaws.com:3306";
        // $this->username = "root";
        // $this->password = "09183110721";
        // $this->db = "ecommerce";
        $this->host = "127.0.0.1";
        $this->username = "root";
        $this->password = "09183110721";
        $this->db = "ecommerce";

        $this->connect = new mysqli($this->host, $this->username, $this->password, $this->db);
        
        return $this->connect;
    }
}