<?php
class Database {
    private $host = "127.0.0.1:3306";
    private $db_name = "u437094107_viandas_sch00l";
    private $username = "u437094107_adm111n";
    private $password = "9t:RuQ7^nr+/";

    public function getConnection() {
        $conn = null;
        try {
            $conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        } catch (mysqli_sql_exception $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $conn;
    }
}
