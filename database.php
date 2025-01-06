<?php
class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "12345chadli"; 
    private $dbname = "taskflow_db";
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Erreur de connexion : " . $this->conn->connect_error);
        }
    }

    public function insertUser(User $user)
    {
        $stmt = $this->conn->prepare("INSERT INTO users (username, email) VALUES (?, ?)");
        if ($stmt === false) {
            return $this->conn->error;
        }

        $username = $user->getUsername();
        $email = $user->getEmail();
        $stmt->bind_param("ss", $username, $email);

        if ($stmt->execute()) {
            return true;
        } else {
            return $stmt->error;
        }
    }

    public function closeConnection()
    {
        $this->conn->close();
    }
}

?>
