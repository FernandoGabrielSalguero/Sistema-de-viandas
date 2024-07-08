<?php
class Database {
    private $host = '127.0.0.1:3306';
    private $dbname = 'u437094107_viandas_sch00l';
    private $username = 'u437094107_adm111n';
    private $password = '9t:RuQ7^nr+/';

    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }

        return $this->conn;
    }
}